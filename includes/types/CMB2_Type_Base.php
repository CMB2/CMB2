<?php
/**
 * CMB base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_Type_Base {

	/**
	 * The CMB2_Types object
	 * @var CMB2_Types
	 */
	public $types;

	/**
	 * Arguments for use in the render method
	 * @var array
	 */
	public $args;

	/**
	 * Rendered output (if 'rendered' argument is set to false)
	 * @var string
	 */
	protected $rendered = '';

	/**
	 * Constructor
	 * @since 2.2.2
	 * @param CMB2_Types $types
	 * @param array      $args
	 */
	public function __construct( CMB2_Types $types, $args = array() ) {
		$this->types = $types;
		$args['rendered'] = isset( $args['rendered'] ) ? (bool) $args['rendered'] : true;
		$this->args = $args;
	}

	/**
	 * Handles rendering this field type.
	 * @since  2.2.2
	 * @return string  Rendered field type.
	 */
	abstract public function render();

	/**
	 * Stores the rendered field output.
	 * @since  2.2.2
	 * @param  string|CMB2_Type_Base $rendered Rendered output.
	 * @return string|CMB2_Type_Base           Rendered output or this object.
	 */
	public function rendered( $rendered ) {
		if ( $this->args['rendered'] ) {
			return is_a( $rendered, __CLASS__ ) ? $rendered->rendered : $rendered;
		}

		$this->rendered = is_a( $rendered, __CLASS__ ) ? $rendered->rendered : $rendered;

		return $this;
	}

	/**
	 * Returns the stored rendered field output.
	 * @since  2.2.2
	 * @return string Stored rendered output (if 'rendered' argument is set to false).
	 */
	public function get_rendered() {
		return $this->rendered;
	}

	/**
	 * Handles parsing and filtering attributes while preserving any passed in via field config.
	 * @since  1.1.0
	 * @param  string $element       Element for filter
	 * @param  array  $type_defaults Type default arguments
	 * @param  array  $type_args     Type override arguments
	 * @return array                 Parsed and filtered arguments
	 */
	public function parse_args( $element, $type_defaults, $type_args = array() ) {
		$type_args = empty( $type_args ) ? $this->args : $type_args;

		$field_overrides = $this->field->args( 'attributes' );

		$args = ! empty( $field_overrides )
			? wp_parse_args( $field_overrides, $type_args )
			: $type_args;

		/**
		 * Filter attributes for a field type.
		 * The dynamic portion of the hook name, $element, refers to the field type.
		 * @since 1.1.0
		 * @param array  $args              The array of attribute arguments.
		 * @param array  $type_defaults          The array of default values.
		 * @param array  $field             The `CMB2_Field` object.
		 * @param object $field_type_object This `CMB2_Types` object.
		 */
		$args = apply_filters( "cmb2_{$element}_attributes", $args, $type_defaults, $this->field, $this->types );

		return wp_parse_args( $args, $type_defaults );
	}

	/**
	 * Fall back to CMB2_Types methods
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case '_id':
			case '_name':
			case '_desc':
			case '_text':
			case 'concat_attrs':
				return call_user_func_array( array( $this->types, $name ), $arguments );
			default:
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s method: %2$s', 'cmb2' ), __CLASS__, $name ) );
		}
	}

	/**
	 * Magic getter for our object.
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'field':
				return $this->types->field;
			default:
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s property: %2$s', 'cmb2' ), __CLASS__, $field ) );
		}
	}

}
