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

		$field        = $this->cmb->get_field( $field_id );
		$show_in_rest = $field ? $field->args( 'show_in_rest' ) : 'no';

		if ( in_array( $show_in_rest, array( 'read_and_write', 'write_only' ), true ) ) {
			return $field->save_field( $value );
		}
	}

}
