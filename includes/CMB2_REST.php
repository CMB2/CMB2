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
class CMB2_REST {

	/**
	 * @var   CMB2 object
	 * @since 2.1.3
	 */
	protected $cmb;

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
	protected $read_fields = array();

	/**
	 * Array of writeable field objects.
	 * @var   CMB2_Field[]
	 * @since 2.1.3
	 */
	protected $write_fields = array();

	/**
	 * Whether metabox fields can be written via REST API
	 * @var   bool
	 * @since 2.1.3
	 */
	protected $can_write = false;

	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;

		$show_value      = $this->cmb->prop( 'show_in_rest' );
		$this->can_read  = 'write_only' !== $show_value;
		$this->can_write = in_array( $show_value, array( 'read_and_write', 'write_only' ), true );
	}

	public function register_fields() {
		$this->read_write_fields();

		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );

		register_api_field(
			$this->cmb->prop( 'object_types' ),
			'cmb2:'. $this->cmb->cmb_id,
			array(
				'get_callback' => $this->can_read
					? array( $this, 'get_restable_field_values' )
					: null,
				'update_callback' => $this->can_write
					? array( $this, 'update_restable_field_value' )
					: null,
				'schema' => $this->cmb->prop( 'rest_schema' ),
			)
		);
	}

	public function read_write_fields() {
		foreach ( $this->cmb->prop( 'fields' ) as $field ) {
			$show_in_rest = isset( $field['show_in_rest'] ) ? $field['show_in_rest'] : null;

			if ( false === $show_in_rest ) {
				continue;
			}

			$this->maybe_add_read_field( $field['id'], $show_in_rest );
			// $this->maybe_add_write_field( $field['id'], $show_in_rest );
		}
	}

	public function maybe_add_read_field( $field_id, $show_in_rest ) {
		$can_read = $this->can_read
			? 'write_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'read_only' ), true );

		if ( $can_read ) {
			$this->read_fields[] = $field_id;
		}
	}

	public function maybe_add_write_field( $field_id, $show_in_rest ) {
		$can_update = $this->can_write
			? 'read_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true );

		if ( $can_update ) {
			$this->write_fields[] = $field_id;
		}
	}

	/**
	 * Handler for getting custom field data.
	 * @since  2.1.3
	 * @param  array           $object     The object from the response
	 * @param  string          $field_name Name of field
	 * @param  WP_REST_Request $request    Current request
	 * @return mixed
	 */
	public function get_restable_field_values( $object, $field_name, $request ) {

		$values = array();
		foreach ( $this->read_fields as $field_id ) {
			$field = $this->cmb->get_field( $field_id );
			$values[ $field->id() ] = $field->escaped_value();
		}

		return $values;
		foreach ( $this->cmb->prop( 'fields' ) as $field ) {
			$field = $this->cmb->get_field( $field['id'] );

			$show_value = $field->args( 'show_in_rest' );

			if ( false !== $show_value && 'write_only' !== $show_value ) {
				$values[ $field->id() ] = $field->escaped_value();
			}
		}

		return $values;
	}

	/**
	 * Handler for updating custom field data.
	 * @since  2.1.3
	 * @param  mixed    $value      The value of the field
	 * @param  object   $object     The object from the response
	 * @param  string   $field_name Name of field
	 * @return bool|int
	 */
	public function update_restable_field_value( $value, $object, $field_id ) {
		/**
		 * @todo Allow empty values (to erase)?
		 * @todo Better validation for value-saving than is_string
		 */
		if ( ! $value || ! is_string( $value ) ) {
			return;
		}

		if ( $field = $this->field_can_update( $field_id ) ) {
			return $field->save_field( $value );
		}
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
		$can_update   = $this->can_write
			? 'read_only' !== $show_in_rest
			: in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true );

		return $can_update ? $field : false;
	}

}
