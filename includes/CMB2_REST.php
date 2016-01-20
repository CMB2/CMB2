<?php
/**
 * Handles hooking CMB2 objects/fields into the WordPres REST API
 * which can allow fields to be read and/or updated.
 *
 * @since  2.2.0
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
	 * @since 2.2.0
	 */
	const VERSION = '1';

	/**
	 * The CMB2 REST base namespace (v should always be followed by $version)
	 * @var string
	 * @since 2.2.0
	 */
	const BASE = 'cmb2/v1';

	/**
	 * @var   CMB2 object
	 * @since 2.2.0
	 */
	public $cmb;

	/**
	 * @var   CMB2_REST[] objects
	 * @since 2.2.0
	 */
	public static $boxes;

	/**
	 * Array of readable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.2.0
	 */
	protected $read_fields = array();

	/**
	 * Array of writeable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.2.0
	 */
	protected $write_fields = array();

	/**
	 * whether CMB2 object is readable via the rest api
	 * @var boolean
	 */
	protected $rest_read = false;

	/**
	 * whether CMB2 object is readable via the rest api
	 * @var boolean
	 */
	protected $rest_write = false;

	/**
	 * Constructor
	 * @since 2.2.0
	 * @param CMB2 $cmb The CMB2 object to be registered for the API.
	 */
	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
		self::$boxes[ $cmb->cmb_id ] = $this;

		$show_value = $this->cmb->prop( 'show_in_rest' );
		$this->rest_read  = 'write_only' !== $show_value;
		$this->rest_write = in_array( $show_value, array( 'read_and_write', 'write_only' ), true );
	}

	public function universal_hooks() {
		// hook up the CMB rest endpoint classes
		$this->once( 'rest_api_init', array( $this, 'init_routes' ), 0 );
		$this->once( 'rest_api_init', array( __CLASS__, 'register_appended_fields' ), 50 );

		$this->declare_read_write_fields();

		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );
	}

	public function init_routes() {
		global $wp_rest_server;

		$boxes_controller = new CMB2_REST_Controller_Boxes( $wp_rest_server );
		$boxes_controller->register_routes();

		$fields_controller = new CMB2_REST_Controller_Fields( $wp_rest_server );
		$fields_controller->register_routes();
	}

	public static function register_appended_fields() {

		$types = array();
		foreach ( self::$boxes as $cmb_id => $cmb_rest ) {
			$types = array_merge( $types, $cmb_rest->cmb->prop( 'object_types' ) );
		}
		$types = array_unique( $types );

		register_api_field(
			$types,
			'cmb2',
			array(
				'get_callback' => array( __CLASS__, 'get_restable_field_values' ),
				'update_callback' => array( __CLASS__, 'update_restable_field_values' ),
				'schema' => null,
			)
		);
	}

	protected function declare_read_write_fields() {
		foreach ( $this->cmb->prop( 'fields' ) as $field ) {
			$show_in_rest = isset( $field['show_in_rest'] ) ? $field['show_in_rest'] : null;

			if ( false === $show_in_rest ) {
				continue;
			}

			if ( $this->can_read( $show_in_rest ) ) {
				$this->read_fields[] = $field['id'];
			}

			if ( $this->can_write( $show_in_rest ) ) {
				$this->write_fields[] = $field['id'];
			}

		}
	}

	protected function can_read( $show_in_rest ) {
		return $this->rest_read
			? 'write_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'read_only' ), true );
	}

	protected function can_write( $show_in_rest ) {
		return $this->rest_write
			? 'read_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true );
	}

	/**
	 * Handler for getting custom field data.
	 * @since  2.2.0
	 * @param  array           $object   The object from the response
	 * @param  string          $field_id Name of field
	 * @param  WP_REST_Request $request  Current request
	 * @return mixed
	 */
	public static function get_restable_field_values( $object, $field_id, $request ) {
		$values = array();
		if ( ! isset( $object['id'] ) ) {
			return;
		}

		foreach ( self::$boxes as $cmb_id => $rest_box ) {
			foreach ( $rest_box->read_fields as $field_id ) {
				$field = $rest_box->cmb->get_field( $field_id );
				$field->object_id = $object['id'];

				// TODO: test other object types (users, comments, etc)
				if ( isset( $object->type ) ) {
					$field->object_type = $object->type;
				}

				$values[ $cmb_id ][ $field->id( true ) ] = $field->get_data();
			}
		}

		return $values;
	}

	/**
	 * Handler for updating custom field data.
	 * @since  2.2.0
	 * @param  mixed    $value    The value of the field
	 * @param  object   $object   The object from the response
	 * @param  string   $field_id Name of field
	 * @return bool|int
	 */
	public static function update_restable_field_values( $values, $object, $field_id ) {
		if ( empty( $values ) || ! is_array( $values ) || 'cmb2' !== $field_id ) {
			return;
		}

		$data = self::get_object_data( $object );
		if ( ! $data ) {
			return;
		}

		$updated = array();

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
	 * @since  2.2.o
	 * @param  array   $values Array of values being provided.
	 * @return array           Array of updated/sanitized values.
	 */
	public function sanitize_box_values( array $values ) {
		$updated = array();

		$this->cmb->pre_process();

		foreach ( $this->write_fields as $field_id ) {
			$updated[ $field_id ] = $this->sanitize_field_value( $values, $field_id );
		}

		$this->cmb->after_save();

		return $updated;
	}

	/**
	 * Handles returning a sanitized field value.
	 * @since  2.2.0
	 * @param  array   $values   Array of values being provided.
	 * @param  string  $field_id The id of the field to update.
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

		$field->object_id   = $this->cmb->object_id();
		$field->object_type = $this->cmb->object_type();

		if ( 'group' == $field->type() ) {
			return $this->sanitize_group_value( $values, $field );
		}

		return $field->save_field( $values[ $this->cmb->cmb_id ][ $field_id ] );
	}

	/**
	 * Handles returning a sanitized group field value.
	 * @since  2.2.0
	 * @param  array       $values Array of values being provided.
	 * @param  CMB2_Field  $field  CMB2_Field object.
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
	 * @since 2.2.0
	 * @param bool   $protected Whether the key is protected. Default false.
	 * @param string $meta_key  Meta key.
	 * @param string $meta_type Meta type.
	 */
	public function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( $this->field_can_write( $meta_key ) ) {
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

	public function field_can_write( $field_id, $return_object = false ) {
		return $this->field_can( 'write_fields', $field_id, $return_object );
	}

	protected function field_can( $type = 'read_fields', $field_id, $return_object = false ) {
		if ( ! in_array( $field_id, $this->{$type}, true ) ) {
			return false;
		}

		return $return_object ? $this->cmb->get_field( $field_id ) : true;
	}

	/**
	 * Get an instance of this class by a CMB2 id
	 *
	 * @since  2.2.0
	 *
	 * @param  string  $cmb_id CMB2 config id
	 *
	 * @return CMB2_REST|false The CMB2_REST object or false.
	 */
	public static function get_rest_box( $cmb_id ) {
		return isset( self::$boxes[ $cmb_id ] ) ? self::$boxes[ $cmb_id ] : false;
	}

	/**
	 * Magic getter for our object.
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'read_fields':
			case 'write_fields':
			case 'rest_read':
			case 'rest_write':
				return $this->{$field};
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}
