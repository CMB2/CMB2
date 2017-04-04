<?php
/**
 * CMB colorpicker field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Colorpicker extends CMB2_Type_Text {

	/**
	 * The optional value for the colorpicker field
	 *
	 * @var string
	 */
	public $value = '';

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param CMB2_Types $types
	 * @param array      $args
	 */
	public function __construct( CMB2_Types $types, $args = array(), $value = '' ) {
		parent::__construct( $types, $args );
		$this->value = $value ? $value : $this->value;
	}

	public function render() {
		$meta_value = $this->value ? $this->value : $this->field->escaped_value();

		$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
		if ( preg_match( '/^' . $hex_color . '/i', $meta_value ) ) {
			// Value is just 123abc, so prepend #
			$meta_value = '#' . $meta_value;
		} elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta_value ) ) {
			// Value doesn't match #123abc, so sanitize to just #
			$meta_value = '#';
		}

		wp_enqueue_style( 'wp-color-picker' );

		$args = wp_parse_args( $this->args, array(
			'class'           => 'cmb2-colorpicker cmb2-text-small',
			'value'           => $meta_value,
			'js_dependencies' => 'wp-color-picker',
		) );

		return parent::render( $args );
	}

}
