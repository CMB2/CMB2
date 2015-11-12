<?php
/**
 * Handles hooking CMB2 objects/fields into the WordPres REST API
 * which can allow fields to be read and/or updated.
 *
 * @since  2.1.3
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_REST_Access extends CMB2_Hookup_Base {

	/**
	 * @var   CMB2[] objects
	 * @since 2.1.3
	 */
	protected static $boxes;

	/**
	 * Whether metabox fields can be read via REST API
	 * @var   bool
	 * @since 2.1.3
	 */
	protected $can_read = true;

	/**
	 * Array of readable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.1.3
	 */
	protected static $read_fields = array();

	/**
	 * Array of writeable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.1.3
	 */
	protected static $write_fields = array();

	/**
	 * Whether metabox fields can be written via REST API
	 * @var   bool
	 * @since 2.1.3
	 */
	protected $can_write = false;

	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
		self::$boxes[ $cmb->cmb_id ] = $cmb;

		$show_value = $this->cmb->prop( 'show_in_rest' );
		$this->cmb->rest_read  = 'write_only' !== $show_value;
		$this->cmb->rest_write = in_array( $show_value, array( 'read_and_write', 'write_only' ), true );
	}

	public function universal_hooks() {
		$this->once( 'rest_api_init', array( __CLASS__, 'register_fields' ), 50 );

		// hook up the CMB rest endpoint class
		$this->once( 'rest_api_init', array( cmb2_rest_endpoints(), 'register_routes' ), 0 );

		$this->prepare_read_write_fields();

		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );
	}

	public static function register_fields() {

		$types = array();
		foreach ( self::$boxes as $cmb_id => $cmb ) {
			$types = array_merge( $types, $cmb->prop( 'object_types' ) );
		}
		$types = array_unique( $types );

		register_api_field(
			$types,
			'cmb2',
			array(
				'get_callback' => array( __CLASS__, 'get_restable_field_values' ),
				'update_callback' => array( __CLASS__, 'update_restable_field_values' ),
				'schema' => apply_filters( 'cmb2_rest_schema', null, self::$boxes ),
			)
		);
	}

	protected function prepare_read_write_fields() {
		foreach ( $this->cmb->prop( 'fields' ) as $field ) {
			$show_in_rest = isset( $field['show_in_rest'] ) ? $field['show_in_rest'] : null;

			if ( false === $show_in_rest ) {
				continue;
			}

			$this->maybe_add_read_field( $field['id'], $show_in_rest );
			$this->maybe_add_write_field( $field['id'], $show_in_rest );
		}
	}

	protected function maybe_add_read_field( $field_id, $show_in_rest ) {
		$can_read = $this->cmb->rest_read
			? 'write_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'read_only' ), true );

		if ( $can_read ) {
			self::$read_fields[ $this->cmb->cmb_id ][] = $field_id;
		}
	}

	protected function maybe_add_write_field( $field_id, $show_in_rest ) {
		$can_update = $this->cmb->rest_write
			? 'read_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true );

		if ( $can_update ) {
			self::$write_fields[ $this->cmb->cmb_id ][] = $field_id;
		}
	}

	/**
	 * Handler for getting custom field data.
	 * @since  2.1.3
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

		foreach ( self::$read_fields as $cmb_id => $fields ) {
			foreach ( $fields as $field_id ) {
				$field = self::$boxes[ $cmb_id ]->get_field( $field_id );
				$field->object_id = $object['id'];

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
	 * @since  2.1.3
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

		$object_id   = $data['object_id'];
		$object_type = $data['object_type'];
		$updated     = array();

		foreach ( self::$write_fields as $cmb_id => $fields ) {
			if ( ! array_key_exists( $cmb_id, $values ) ) {
				continue;
			}

			$cmb = self::$boxes[ $cmb_id ];

			$cmb->object_type( $object_id );
			$cmb->object_type( $object_type );

			$cmb->pre_process();

			foreach ( $fields as $field_id ) {
				if ( ! array_key_exists( $field_id, $values[ $cmb_id ] ) ) {
					continue;
				}

				$field = $cmb->get_field( $field_id );

				if ( 'title' == $field->type() ) {
					continue;
				}

				$field->object_id   = $object_id;
				$field->object_type = $object_type;

				if ( 'group' == $field->type() ) {
					$fields = $field->fields();
					if ( empty( $fields ) ) {
						continue;
					}

					$cmb->data_to_save[ $field_id ] = $values[ $cmb_id ][ $field_id ];
					$updated[ $cmb_id ][ $field_id ] = $cmb->save_group_field( $field );

				} else {
					$updated[ $cmb_id ][ $field_id ] = $field->save_field( $values[ $cmb_id ][ $field_id ] );
				}

			}

			$cmb->after_save();
		}

		return $updated;
	}

	/**
	 * Filter whether a meta key is protected.
	 * @since 2.1.3
	 * @param bool   $protected Whether the key is protected. Default false.
	 * @param string $meta_key  Meta key.
	 * @param string $meta_type Meta type.
	 */
	public function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( $this->field_can_update( $meta_key ) ) {
			return false;
		}

		return $protected;
	}

	public function field_can_update( $field_id ) {

		$field        = $this->cmb->get_field( $field_id );
		$show_in_rest = $field ? $field->args( 'show_in_rest' ) : 'no';
		$can_update   = $this->cmb->rest_write
			? 'read_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true );

		return $can_update ? $field : false;
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

}
