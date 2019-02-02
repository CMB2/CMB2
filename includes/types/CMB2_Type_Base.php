<?php
/**
 * CMB base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
abstract class CMB2_Type_Base {

	/**
	 * The CMB2_Types object
	 *
	 * @var CMB2_Types
	 */
	public $types;

	/**
	 * Arguments for use in the render method
	 *
	 * @var array
	 */
	public $args;

	/**
	 * Rendered output (if 'rendered' argument is set to false)
	 *
	 * @var string
	 */
	protected $rendered = '';

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 * @param CMB2_Types $types Object for the field type.
	 * @param array      $args  Array of arguments for the type.
	 */
	public function __construct( CMB2_Types $types, $args = array() ) {
		$this->types      = $types;
		$args['rendered'] = isset( $args['rendered'] ) ? (bool) $args['rendered'] : true;
		$this->args       = $args;
	}

	/**
	 * Handles rendering this field type.
	 *
	 * @since  2.2.2
	 * @return string  Rendered field type.
	 */
	abstract public function render();

	/**
	 * Stores the rendered field output.
	 *
	 * @since  2.2.2
	 * @param  string|CMB2_Type_Base $rendered Rendered output.
	 * @return string|CMB2_Type_Base           Rendered output or this object.
	 */
	public function rendered( $rendered ) {
		$this->field->register_js_data();

		if ( $this->args['rendered'] ) {
			return is_a( $rendered, __CLASS__ ) ? $rendered->rendered : $rendered;
		}

		$this->rendered = is_a( $rendered, __CLASS__ ) ? $rendered->rendered : $rendered;

		return $this;
	}

	/**
	 * Returns the stored rendered field output.
	 *
	 * @since  2.2.2
	 * @return string Stored rendered output (if 'rendered' argument is set to false).
	 */
	public function get_rendered() {
		return $this->rendered;
	}

	/**
	 * Handles parsing and filtering attributes while preserving any passed in via field config.
	 *
	 * @since  1.1.0
	 * @param  string $element        Element for filter.
	 * @param  array  $type_defaults  Type default arguments.
	 * @param  array  $type_overrides Type override arguments.
	 * @return array                  Parsed and filtered arguments.
	 */
	public function parse_args( $element, $type_defaults, $type_overrides = array() ) {
		$args = $this->parse_args_from_overrides( $type_overrides );

		/**
		 * Filter attributes for a field type.
		 * The dynamic portion of the hook name, $element, refers to the field type.
		 *
		 * @since 1.1.0
		 * @param array  $args              The array of attribute arguments.
		 * @param array  $type_defaults     The array of default values.
		 * @param array  $field             The `CMB2_Field` object.
		 * @param object $field_type_object This `CMB2_Types` object.
		 */
		$args = apply_filters( "cmb2_{$element}_attributes", $args, $type_defaults, $this->field, $this->types );

		$args = wp_parse_args( $args, $type_defaults );

		if ( ! empty( $args['js_dependencies'] ) ) {
			$this->field->add_js_dependencies( $args['js_dependencies'] );
		}

		return $args;
	}

	/**
	 * Handles parsing and filtering attributes while preserving any passed in via field config.
	 *
	 * @since  2.2.4
	 * @param  array $type_overrides Type override arguments.
	 * @return array                 Parsed arguments
	 */
	protected function parse_args_from_overrides( $type_overrides = array() ) {
		$type_overrides = empty( $type_overrides ) ? $this->args : $type_overrides;

		if ( true !== $this->field->args( 'disable_hash_data_attribute' ) ) {
			$type_overrides['data-hash'] = $this->field->hash_id();
		}

		$field_overrides = $this->field->args( 'attributes' );

		return ! empty( $field_overrides )
			? wp_parse_args( $field_overrides, $type_overrides )
			: $type_overrides;
	}

	/**
	 * Fall back to CMB2_Types methods
	 *
	 * @param  string $method    Method name being invoked.
	 * @param  array  $arguments Arguments passed for the method.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __call( $method, $arguments ) {
		switch ( $method ) {
			case '_id':
			case '_name':
			case '_desc':
			case '_text':
			case 'concat_attrs':
				return call_user_func_array( array( $this->types, $method ), $arguments );
			default:
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s method: %2$s', 'cmb2' ), __CLASS__, $method ) );
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field Property being requested.
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
