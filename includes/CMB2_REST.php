<?php
/**
 * Handles hooking CMB2 objects/fields into the WordPres REST API
 * which can allow fields to be read and/or updated.
 *
 * @since  2.2.4
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_REST extends CMB2_Hookup_Base {

	/**
	 * The current CMB2 REST endpoint version
	 * @var string
	 * @since 2.2.4
	 */
	const VERSION = '1';

	/**
	 * The CMB2 REST base namespace (v should always be followed by $version)
	 * @var string
	 * @since 2.2.4
	 */
	const NAME_SPACE = 'cmb2/v1';

	/**
	 * @var   CMB2 object
	 * @since 2.2.4
	 */
	public $cmb;

	/**
	 * @var   CMB2_REST[] objects
	 * @since 2.2.4
	 */
	public static $boxes;

	/**
	 * Array of readable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.2.4
	 */
	protected $read_fields = array();

	/**
	 * Array of editable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.2.4
	 */
	protected $edit_fields = array();

	/**
	 * whether CMB2 object is readable via the rest api.
	 * @var boolean
	 */
	protected $rest_read = false;

	/**
	 * whether CMB2 object is editable via the rest api.
	 * @var boolean
	 */
	protected $rest_edit = false;

	/**
	 * Constructor
	 *
	 * @since 2.2.4
	 *
	 * @param CMB2 $cmb The CMB2 object to be registered for the API.
	 */
	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
		self::$boxes[ $cmb->cmb_id ] = $this;

		$show_value = $this->cmb->prop( 'show_in_rest' );

		$this->rest_read = self::is_readable( $show_value );
		$this->rest_edit = self::is_editable( $show_value );
	}

	/**
	 * Hooks to register on frontend and backend.
	 *
	 * @since  2.2.4
	 *
	 * @return void
	 */
	public function universal_hooks() {
		// hook up the CMB rest endpoint classes
		$this->once( 'rest_api_init', array( $this, 'init_routes' ), 0 );

		if ( function_exists( 'register_rest_field' ) ) {
			$this->once( 'rest_api_init', array( __CLASS__, 'register_cmb2_fields' ), 50 );
		}

		$this->declare_read_edit_fields();

		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );
	}

	/**
	 * Initiate the CMB2 Boxes and Fields routes
	 *
	 * @since  2.2.4
	 *
	 * @return void
	 */
	public function init_routes() {
		$wp_rest_server = rest_get_server();

		$boxes_controller = new CMB2_REST_Controller_Boxes( $wp_rest_server );
		$boxes_controller->register_routes();

		$fields_controller = new CMB2_REST_Controller_Fields( $wp_rest_server );
		$fields_controller->register_routes();
	}

	/**
	 * Loop through REST boxes and call register_rest_field for each object type.
	 *
	 * @since  2.2.4
	 *
	 * @return void
	 */
	public static function register_cmb2_fields() {
		$alltypes = $taxonomies = array();
		$has_user = $has_comment = $has_taxonomies = false;

		foreach ( self::$boxes as $cmb_id => $rest_box ) {
			$types = array_flip( $rest_box->cmb->box_types() );

			if ( isset( $types['user'] ) ) {
				unset( $types['user'] );
				$has_user = true;
			}

			if ( isset( $types['comment'] ) ) {
				unset( $types['comment'] );
				$has_comment = true;
			}

			if ( isset( $types['term'] ) ) {
				unset( $types['term'] );

				$taxonomies = array_merge(
					$taxonomies,
					CMB2_Utils::ensure_array( $rest_box->cmb->prop( 'taxonomies' ) )
				);

				$has_taxonomies = true;
			}

			if ( ! empty( $types ) ) {
				$alltypes = array_merge( $alltypes, array_flip( $types ) );
			}
		}

		$alltypes = array_unique( $alltypes );

		if ( ! empty( $alltypes ) ) {
			self::register_rest_field( $alltypes, 'post' );
		}

		if ( $has_user ) {
			self::register_rest_field( 'user', 'user' );
		}

		if ( $has_comment ) {
			self::register_rest_field( 'comment', 'comment' );
		}

		if ( $has_taxonomies ) {
			self::register_rest_field( $taxonomies, 'term' );
		}
	}

	/**
	 * Wrapper for register_rest_field.
	 *
	 * @since  2.2.4
	 *
	 * @param string|array $object_types Object(s) the field is being registered
	 *                                   to, "post"|"term"|"comment" etc.
	 * @param string $object_types       Canonical object type for callbacks.
	 *
	 * @return void
	 */
	protected static function register_rest_field( $object_types, $object_type ) {
		register_rest_field( $object_types, 'cmb2', array(
			'get_callback'    => array( __CLASS__, "get_{$object_type}_rest_values" ),
			'update_callback' => array( __CLASS__, "update_{$object_type}_rest_values" ),
			'schema'          => null, // @todo add schema
		) );
	}

	/**
	 * Setup readable and editable fields.
	 *
	 * @since  2.2.4
	 *
	 * @return void
	 */
	protected function declare_read_edit_fields() {
		foreach ( $this->cmb->prop( 'fields' ) as $field ) {
			$show_in_rest = isset( $field['show_in_rest'] ) ? $field['show_in_rest'] : null;

			if ( false === $show_in_rest ) {
				continue;
			}

			if ( $this->can_read( $show_in_rest ) ) {
				$this->read_fields[] = $field['id'];
			}

			if ( $this->can_edit( $show_in_rest ) ) {
				$this->edit_fields[] = $field['id'];
			}

		}
	}

	/**
	 * Determines if a field is readable based on it's show_in_rest value
	 * and the box's show_in_rest value.
	 *
	 * @since  2.2.4
	 *
	 * @param  bool $show_in_rest Field's show_in_rest value. Default null.
	 *
	 * @return bool               Whether field is readable.
	 */
	protected function can_read( $show_in_rest ) {
		// if 'null', then use default box value.
		if ( null === $show_in_rest ) {
			return $this->rest_read;
		}

		// Else check if the value represents readable.
		return self::is_readable( $show_in_rest );
	}

	/**
	 * Determines if a field is editable based on it's show_in_rest value
	 * and the box's show_in_rest value.
	 *
	 * @since  2.2.4
	 *
	 * @param  bool $show_in_rest Field's show_in_rest value. Default null.
	 *
	 * @return bool               Whether field is editable.
	 */
	protected function can_edit( $show_in_rest ) {
		// if 'null', then use default box value.
		if ( null === $show_in_rest ) {
			return $this->rest_edit;
		}

		// Else check if the value represents editable.
		return self::is_editable( $show_in_rest );
	}

	/**
	 * Handler for getting post custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  array           $object      The object data from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return mixed
	 */
	public static function get_post_rest_values( $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::get_rest_values( $object, $request, $object_type, 'post' );
		}
	}

	/**
	 * Handler for getting user custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  array           $object      The object data from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return mixed
	 */
	public static function get_user_rest_values( $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::get_rest_values( $object, $request, $object_type, 'user' );
		}
	}

	/**
	 * Handler for getting comment custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  array           $object      The object data from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return mixed
	 */
	public static function get_comment_rest_values( $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::get_rest_values( $object, $request, $object_type, 'comment' );
		}
	}

	/**
	 * Handler for getting term custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  array           $object      The object data from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return mixed
	 */
	public static function get_term_rest_values( $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::get_rest_values( $object, $request, $object_type, 'term' );
		}
	}

	/**
	 * Handler for getting custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  array           $object           The object data from the response
	 * @param  WP_REST_Request $request          Current request
	 * @param  string          $object_type      The request object type
	 * @param  string          $main_object_type The cmb main object type
	 *
	 * @return mixed
	 */
	protected static function get_rest_values( $object, $request, $object_type, $main_object_type = 'post' ) {
		if ( ! isset( $object['id'] ) ) {
			return;
		}

		$values = array();

		foreach ( self::$boxes as $cmb_id => $rest_box ) {
			$check = 'term' === $main_object_type ? 'term' : $object_type;

			// This is not the box you're looking for...
			if ( ! in_array( $check, $rest_box->cmb->box_types() ) ) {
				continue;
			}

			foreach ( $rest_box->read_fields as $field_id ) {
				$rest_box->cmb->object_id( $object['id'] );
				$rest_box->cmb->object_type( $main_object_type );

				$field = $rest_box->cmb->get_field( $field_id );

				$field->object_id( $object['id'] );
				$field->object_type( $main_object_type );

				$values[ $cmb_id ][ $field->id( true ) ] = $field->get_data();
			}
		}

		return $values;
	}

	/**
	 * Handler for updating post custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed           $value       The value of the field
	 * @param  object          $object      The object from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return bool|int
	 */
	public static function update_post_rest_values( $values, $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::update_rest_values( $values, $object, $request, $object_type, 'post' );
		}
	}

	/**
	 * Handler for updating user custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed           $value       The value of the field
	 * @param  object          $object      The object from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return bool|int
	 */
	public static function update_user_rest_values( $values, $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::update_rest_values( $values, $object, $request, $object_type, 'user' );
		}
	}

	/**
	 * Handler for updating comment custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed           $value       The value of the field
	 * @param  object          $object      The object from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return bool|int
	 */
	public static function update_comment_rest_values( $values, $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::update_rest_values( $values, $object, $request, $object_type, 'comment' );
		}
	}

	/**
	 * Handler for updating term custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed           $value       The value of the field
	 * @param  object          $object      The object from the response
	 * @param  string          $field_name  Name of field
	 * @param  WP_REST_Request $request     Current request
	 * @param  string          $object_type The request object type
	 *
	 * @return bool|int
	 */
	public static function update_term_rest_values( $values, $object, $field_name, $request, $object_type ) {
		if ( 'cmb2' === $field_name ) {
			return self::update_rest_values( $values, $object, $request, $object_type, 'term' );
		}
	}

	/**
	 * Handler for updating custom field data.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed           $value            The value of the field
	 * @param  object          $object           The object from the response
	 * @param  WP_REST_Request $request          Current request
	 * @param  string          $object_type      The request object type
	 * @param  string          $main_object_type The cmb main object type
	 *
	 * @return bool|int
	 */
	protected static function update_rest_values( $values, $object, $request, $object_type, $main_object_type = 'post' ) {
		if ( empty( $values ) || ! is_array( $values ) ) {
			return;
		}

		// @todo verify that $object_type matches this output.
		$data = self::get_object_data( $object );
		if ( ! $data ) {
			return;
		}

		$updated = array();

		// @todo Security hardening... check for object type, check for show_in_rest values.
		foreach ( self::$boxes as $cmb_id => $rest_box ) {
			if ( ! array_key_exists( $cmb_id, $values ) ) {
				continue;
			}

			$rest_box->cmb->object_id( $data['object_id'] );
			$rest_box->cmb->object_type( $data['object_type'] );

			// TODO: Test since refactor.
			$updated[ $cmb_id ] = $rest_box->sanitize_box_values( $values );
		}

		return $updated;
	}

	/**
	 * Loop through box fields and sanitize the values.
	 *
	 * @since  2.2.o
	 *
	 * @param  array   $values Array of values being provided.
	 * @return array           Array of updated/sanitized values.
	 */
	public function sanitize_box_values( array $values ) {
		$updated = array();

		$this->cmb->pre_process();

		foreach ( $this->edit_fields as $field_id ) {
			$updated[ $field_id ] = $this->sanitize_field_value( $values, $field_id );
		}

		$this->cmb->after_save();

		return $updated;
	}

	/**
	 * Handles returning a sanitized field value.
	 *
	 * @since  2.2.4
	 *
	 * @param  array   $values   Array of values being provided.
	 * @param  string  $field_id The id of the field to update.
	 *
	 * @return mixed             The results of saving/sanitizing a field value.
	 */
	protected function sanitize_field_value( array $values, $field_id ) {
		if ( ! array_key_exists( $field_id, $values[ $this->cmb->cmb_id ] ) ) {
			return;
		}

		$field = $this->cmb->get_field( $field_id );

		if ( 'title' == $field->type() ) {
			return;
		}

		$field->object_id( $this->cmb->object_id() );
		$field->object_type( $this->cmb->object_type() );

		if ( 'group' == $field->type() ) {
			return $this->sanitize_group_value( $values, $field );
		}

		return $field->save_field( $values[ $this->cmb->cmb_id ][ $field_id ] );
	}

	/**
	 * Handles returning a sanitized group field value.
	 *
	 * @since  2.2.4
	 *
	 * @param  array       $values Array of values being provided.
	 * @param  CMB2_Field  $field  CMB2_Field object.
	 *
	 * @return mixed               The results of saving/sanitizing the group field value.
	 */
	protected function sanitize_group_value( array $values, CMB2_Field $field ) {
		$fields = $field->fields();
		if ( empty( $fields ) ) {
			return;
		}

		$this->cmb->data_to_save[ $field->_id() ] = $values[ $this->cmb->cmb_id ][ $field->_id() ];

		return $this->cmb->save_group_field( $field );
	}

	/**
	 * Filter whether a meta key is protected.
	 *
	 * @since 2.2.4
	 *
	 * @param bool   $protected Whether the key is protected. Default false.
	 * @param string $meta_key  Meta key.
	 * @param string $meta_type Meta type.
	 */
	public function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( $this->field_can_edit( $meta_key ) ) {
			return false;
		}

		return $protected;
	}

	protected static function get_object_data( $object ) {
		$object_id = 0;
		if ( isset( $object->ID ) ) {
			$object_id   = intval( $object->ID );
			$object_type = isset( $object->user_login ) ? 'user' : 'post';
		} elseif ( isset( $object->comment_ID ) ) {
			$object_id   = intval( $object->comment_ID );
			$object_type = 'comment';
		} elseif ( is_array( $object ) && isset( $object['term_id'] ) ) {
			$object_id   = intval( $object['term_id'] );
			$object_type = 'term';
		} elseif ( isset( $object->term_id ) ) {
			$object_id   = intval( $object->term_id );
			$object_type = 'term';
		}

		if ( empty( $object_id ) ) {
			return false;
		}

		return compact( 'object_id', 'object_type' );
	}

	public function field_can_read( $field_id, $return_object = false ) {
		return $this->field_can( 'read_fields', $field_id, $return_object );
	}

	public function field_can_edit( $field_id, $return_object = false ) {
		return $this->field_can( 'edit_fields', $field_id, $return_object );
	}

	protected function field_can( $type = 'read_fields', $field_id, $return_object = false ) {
		if ( ! in_array( $field_id instanceof CMB2_Field ? $field_id->id() : $field_id, $this->{$type}, true ) ) {
			return false;
		}

		return $return_object ? $this->cmb->get_field( $field_id ) : true;
	}

	/**
	 * Get a CMB2_REST instance object from the registry by a CMB2 id.
	 *
	 * @since  2.2.4
	 *
	 * @param  string  $cmb_id CMB2 config id
	 *
	 * @return CMB2_REST|false The CMB2_REST object or false.
	 */
	public static function get_rest_box( $cmb_id ) {
		return isset( self::$boxes[ $cmb_id ] ) ? self::$boxes[ $cmb_id ] : false;
	}

	/**
	 * Remove a CMB2_REST instance object from the registry.
	 *
	 * @since  2.2.4
	 *
	 * @param string $cmb_id A CMB2 instance id.
	 */
	public static function remove( $cmb_id ) {
		if ( array_key_exists( $cmb_id, self::$boxes ) ) {
			unset( self::$boxes[ $cmb_id ] );
		}
	}

	/**
	 * Checks if given value is readable.
	 *
	 * Value is considered readable if it is not empty and if it does not match the editable blacklist.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed  $value Value to check.
	 *
	 * @return boolean       Whether value is considered readable.
	 */
	public static function is_readable( $value ) {
		return ! empty( $value ) && ! in_array( $value, array(
			WP_REST_Server::CREATABLE,
			WP_REST_Server::EDITABLE,
			WP_REST_Server::DELETABLE,
		), true );
	}

	/**
	 * Checks if given value is editable.
	 *
	 * Value is considered editable if matches the editable whitelist.
	 *
	 * @since  2.2.4
	 *
	 * @param  mixed  $value Value to check.
	 *
	 * @return boolean       Whether value is considered editable.
	 */
	public static function is_editable( $value ) {
		return in_array( $value, array(
			WP_REST_Server::EDITABLE,
			WP_REST_Server::ALLMETHODS,
		), true );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'read_fields':
			case 'edit_fields':
			case 'rest_read':
			case 'rest_edit':
				return $this->{$field};
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}
