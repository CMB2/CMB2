<?php
/**
 * Handles hooking CMB2 objects/fields into the WordPres REST API
 * which can allow fields to be read and/or updated.
 *
 * @since  2.2.3
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read read_fields Array of readable field objects.
 * @property-read edit_fields Array of editable field objects.
 * @property-read rest_read   Whether CMB2 object is readable via the rest api.
 * @property-read rest_edit   Whether CMB2 object is editable via the rest api.
 */
class CMB2_REST extends CMB2_Hookup_Base {

	/**
	 * The current CMB2 REST endpoint version
	 *
	 * @var string
	 * @since 2.2.3
	 */
	const VERSION = '1';

	/**
	 * The CMB2 REST base namespace (v should always be followed by $version)
	 *
	 * @var string
	 * @since 2.2.3
	 */
	const NAME_SPACE = 'cmb2/v1';

	/**
	 * @var   CMB2 object
	 * @since 2.2.3
	 */
	public $cmb;

	/**
	 * @var   CMB2_REST[] objects
	 * @since 2.2.3
	 */
	protected static $boxes = array();

	/**
	 * @var   array Array of cmb ids for each type.
	 * @since 2.2.3
	 */
	protected static $type_boxes = array(
		'post' => array(),
		'user' => array(),
		'comment' => array(),
		'term' => array(),
	);

	/**
	 * Array of readable field objects.
	 *
	 * @var   CMB2_Field[]
	 * @since 2.2.3
	 */
	protected $read_fields = array();

	/**
	 * Array of editable field objects.
	 *
	 * @var   CMB2_Field[]
	 * @since 2.2.3
	 */
	protected $edit_fields = array();

	/**
	 * Whether CMB2 object is readable via the rest api.
	 *
	 * @var boolean
	 */
	protected $rest_read = false;

	/**
	 * Whether CMB2 object is editable via the rest api.
	 *
	 * @var boolean
	 */
	protected $rest_edit = false;

	/**
	 * A functionalized constructor, used for the hookup action callbacks.
	 *
	 * @since  2.2.6
	 *
	 * @param  CMB2 $cmb The CMB2 object to hookup
	 *
	 * @return CMB2_Hookup_Base $hookup The hookup object.
	 */
	public static function maybe_init_and_hookup( CMB2 $cmb ) {
		if ( $cmb->prop( 'show_in_rest' ) && function_exists( 'rest_get_server' ) ) {

			$hookup = new self( $cmb );

			return $hookup->universal_hooks();
		}

		return false;
	}

	/**
	 * Constructor
	 *
	 * @since 2.2.3
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
	 * @since  2.2.3
	 *
	 * @return void
	 */
	public function universal_hooks() {
		// hook up the CMB rest endpoint classes
		$this->once( 'rest_api_init', array( __CLASS__, 'init_routes' ), 0 );

		if ( function_exists( 'register_rest_field' ) ) {
			$this->once( 'rest_api_init', array( __CLASS__, 'register_cmb2_fields' ), 50 );
		}

		$this->declare_read_edit_fields();

		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );

		return $this;
	}

	/**
	 * Initiate the CMB2 Boxes and Fields routes
	 *
	 * @since  2.2.3
	 *
	 * @return void
	 */
	public static function init_routes() {
		$wp_rest_server = rest_get_server();

		$boxes_controller = new CMB2_REST_Controller_Boxes( $wp_rest_server );
		$boxes_controller->register_routes();

		$fields_controller = new CMB2_REST_Controller_Fields( $wp_rest_server );
		$fields_controller->register_routes();
	}

	/**
	 * Loop through REST boxes and call register_rest_field for each object type.
	 *
	 * @since  2.2.3
	 *
	 * @return void
	 */
	public static function register_cmb2_fields() {
		$alltypes = $taxonomies = array();

		foreach ( self::$boxes as $cmb_id => $rest_box ) {

			// Hook box specific filter callbacks.
			$callback = $rest_box->cmb->prop( 'register_rest_field_cb' );
			if ( is_callable( $callback ) ) {
				call_user_func( $callback, $rest_box );
				continue;
			}

			$types = array_flip( $rest_box->cmb->box_types( array( 'post' ) ) );

			if ( isset( $types['user'] ) ) {
				unset( $types['user'] );
				self::$type_boxes['user'][ $cmb_id ] = $cmb_id;
			}

			if ( isset( $types['comment'] ) ) {
				unset( $types['comment'] );
				self::$type_boxes['comment'][ $cmb_id ] = $cmb_id;
			}

			if ( isset( $types['term'] ) ) {
				unset( $types['term'] );

				$taxonomies = array_merge(
					$taxonomies,
					CMB2_Utils::ensure_array( $rest_box->cmb->prop( 'taxonomies' ) )
				);

				self::$type_boxes['term'][ $cmb_id ] = $cmb_id;
			}

			if ( ! empty( $types ) ) {
				$alltypes = array_merge( $alltypes, array_flip( $types ) );
				self::$type_boxes['post'][ $cmb_id ] = $cmb_id;
			}
		}

		$alltypes = array_unique( $alltypes );

		if ( ! empty( $alltypes ) ) {
			self::register_rest_field( $alltypes, 'post' );
		}

		if ( ! empty( self::$type_boxes['user'] ) ) {
			self::register_rest_field( 'user', 'user' );
		}

		if ( ! empty( self::$type_boxes['comment'] ) ) {
			self::register_rest_field( 'comment', 'comment' );
		}

		if ( ! empty( self::$type_boxes['term'] ) ) {
			self::register_rest_field( $taxonomies, 'term' );
		}
	}

	/**
	 * Wrapper for register_rest_field.
	 *
	 * @since  2.2.3
	 *
	 * @param string|array $object_types Object(s) the field is being registered
	 *                                   to, "post"|"term"|"comment" etc.
	 * @param string       $object_types       Canonical object type for callbacks.
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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
	 * @since  2.2.3
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

		if ( ! empty( self::$type_boxes[ $main_object_type ] ) ) {
			foreach ( self::$type_boxes[ $main_object_type ] as $cmb_id ) {
				$rest_box = self::$boxes[ $cmb_id ];

				if ( ! $rest_box->cmb->is_box_type( $object_type ) ) {
					continue;
				}

				$result = self::get_box_rest_values( $rest_box, $object['id'], $main_object_type );
				if ( ! empty( $result ) ) {
					if ( empty( $values[ $cmb_id ] ) ) {
						$values[ $cmb_id ] = $result;
					} else {
						$values[ $cmb_id ] = array_merge( $values[ $cmb_id ], $result );
					}
				}
			}
		}

		return $values;
	}

	/**
	 * Get box rest values.
	 *
	 * @since  2.7.0
	 *
	 * @param  CMB2_REST $rest_box         The CMB2_REST object.
	 * @param  integer   $object_id        The object ID.
	 * @param  string    $main_object_type The object type (post, user, term, etc)
	 *
	 * @return array                       Array of box rest values.
	 */
	public static function get_box_rest_values( $rest_box, $object_id = 0, $main_object_type = 'post' ) {

		$rest_box->cmb->object_id( $object_id );
		$rest_box->cmb->object_type( $main_object_type );

		$values = array();

		foreach ( $rest_box->read_fields as $field_id ) {
			$field = $rest_box->cmb->get_field( $field_id );
			$field->object_id( $object_id );
			$field->object_type( $main_object_type );

			$values[ $field->id( true ) ] = $field->get_rest_value();

			if ( $field->args( 'has_supporting_data' ) ) {
				$field = $field->get_supporting_field();
				$values[ $field->id( true ) ] = $field->get_rest_value();
			}
		}

		return $values;
	}

	/**
	 * Handler for updating post custom field data.
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed           $values      The value of the field
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
	 * @since  2.2.3
	 *
	 * @param  mixed           $values      The value of the field
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
	 * @since  2.2.3
	 *
	 * @param  mixed           $values      The value of the field
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
	 * @since  2.2.3
	 *
	 * @param  mixed           $values      The value of the field
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
	 * @since  2.2.3
	 *
	 * @param  mixed           $values           The value of the field
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

		$object_id = self::get_object_id( $object, $main_object_type );

		if ( ! $object_id ) {
			return;
		}

		$updated = array();

		if ( ! empty( self::$type_boxes[ $main_object_type ] ) ) {
			foreach ( self::$type_boxes[ $main_object_type ] as $cmb_id ) {
				$result = self::santize_box_rest_values( $values, self::$boxes[ $cmb_id ], $object_id, $main_object_type );
				if ( ! empty( $result ) ) {
					$updated[ $cmb_id ] = $result;
				}
			}
		}

		return $updated;
	}

	/**
	 * Updates box rest values.
	 *
	 * @since  2.7.0
	 *
	 * @param  array     $values           Array of values.
	 * @param  CMB2_REST $rest_box         The CMB2_REST object.
	 * @param  integer   $object_id        The object ID.
	 * @param  string    $main_object_type The object type (post, user, term, etc)
	 *
	 * @return mixed|bool                  Array of updated statuses if successful.
	 */
	public static function santize_box_rest_values( $values, $rest_box, $object_id = 0, $main_object_type = 'post' ) {

		if ( ! array_key_exists( $rest_box->cmb->cmb_id, $values ) ) {
			return false;
		}

		$rest_box->cmb->object_id( $object_id );
		$rest_box->cmb->object_type( $main_object_type );

		return $rest_box->sanitize_box_values( $values );
	}

	/**
	 * Loop through box fields and sanitize the values.
	 *
	 * @since  2.2.o
	 *
	 * @param  array $values Array of values being provided.
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
	 * @since  2.2.3
	 *
	 * @param  array  $values   Array of values being provided.
	 * @param  string $field_id The id of the field to update.
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
	 * @since  2.2.3
	 *
	 * @param  array      $values Array of values being provided.
	 * @param  CMB2_Field $field  CMB2_Field object.
	 *
	 * @return mixed               The results of saving/sanitizing the group field value.
	 */
	protected function sanitize_group_value( array $values, CMB2_Field $field ) {
		$fields = $field->fields();
		if ( empty( $fields ) ) {
			return;
		}

		$this->cmb->data_to_save[ $field->_id( '', false ) ] = $values[ $this->cmb->cmb_id ][ $field->_id( '', false ) ];

		return $this->cmb->save_group_field( $field );
	}

	/**
	 * Filter whether a meta key is protected.
	 *
	 * @since 2.2.3
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

	/**
	 * Get the object ID for the given object/type.
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed  $object      The object to get the ID for.
	 * @param  string $object_type The object type we are looking for.
	 *
	 * @return int                 The object ID if found.
	 */
	public static function get_object_id( $object, $object_type = 'post' ) {
		switch ( $object_type ) {
			case 'user':
			case 'post':
				if ( isset( $object->ID ) ) {
					return intval( $object->ID );
				}
			case 'comment':
				if ( isset( $object->comment_ID ) ) {
					return intval( $object->comment_ID );
				}
			case 'term':
				if ( is_array( $object ) && isset( $object['term_id'] ) ) {
					return intval( $object['term_id'] );
				} elseif ( isset( $object->term_id ) ) {
					return intval( $object->term_id );
				}
		}

		return 0;
	}

	/**
	 * Checks if a given field can be read.
	 *
	 * @since  2.2.3
	 *
	 * @param  string|CMB2_Field $field_id      Field ID or CMB2_Field object.
	 * @param  boolean           $return_object Whether to return the Field object.
	 *
	 * @return mixed                            False if field can't be read or true|CMB2_Field object.
	 */
	public function field_can_read( $field_id, $return_object = false ) {
		return $this->field_can( 'read_fields', $field_id, $return_object );
	}

	/**
	 * Checks if a given field can be edited.
	 *
	 * @since  2.2.3
	 *
	 * @param  string|CMB2_Field $field_id      Field ID or CMB2_Field object.
	 * @param  boolean           $return_object Whether to return the Field object.
	 *
	 * @return mixed                            False if field can't be edited or true|CMB2_Field object.
	 */
	public function field_can_edit( $field_id, $return_object = false ) {
		return $this->field_can( 'edit_fields', $field_id, $return_object );
	}

	/**
	 * Checks if a given field can be read or edited.
	 *
	 * @since  2.2.3
	 *
	 * @param  string            $type          Whether we are checking for read or edit fields.
	 * @param  string|CMB2_Field $field_id      Field ID or CMB2_Field object.
	 * @param  boolean           $return_object Whether to return the Field object.
	 *
	 * @return mixed                            False if field can't be read or edited or true|CMB2_Field object.
	 */
	protected function field_can( $type = 'read_fields', $field_id, $return_object = false ) {
		if ( ! in_array( $field_id instanceof CMB2_Field ? $field_id->id() : $field_id, $this->{$type}, true ) ) {
			return false;
		}

		return $return_object ? $this->cmb->get_field( $field_id ) : true;
	}

	/**
	 * Get a CMB2_REST instance object from the registry by a CMB2 id.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $cmb_id CMB2 config id
	 *
	 * @return CMB2_REST|false The CMB2_REST object or false.
	 */
	public static function get_rest_box( $cmb_id ) {
		return isset( self::$boxes[ $cmb_id ] ) ? self::$boxes[ $cmb_id ] : false;
	}

	/**
	 * Remove a CMB2_REST instance object from the registry.
	 *
	 * @since  2.2.3
	 *
	 * @param string $cmb_id A CMB2 instance id.
	 */
	public static function remove( $cmb_id ) {
		if ( array_key_exists( $cmb_id, self::$boxes ) ) {
			unset( self::$boxes[ $cmb_id ] );
		}
	}

	/**
	 * Retrieve all CMB2_REST instances from the registry.
	 *
	 * @since  2.2.3
	 * @return CMB2[] Array of all registered CMB2_REST instances.
	 */
	public static function get_all() {
		return self::$boxes;
	}

	/**
	 * Checks if given value is readable.
	 *
	 * Value is considered readable if it is not empty and if it does not match the editable blacklist.
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed $value Value to check.
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
	 * @since  2.2.3
	 *
	 * @param  mixed $value Value to check.
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
