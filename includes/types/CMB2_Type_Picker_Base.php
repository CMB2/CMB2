<?php
/**
 * CMB Picker base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_Type_Picker_Base extends CMB2_Type_Text {

	/**
	 * Parse the picker attributes.
	 * @since  2.2.0
	 * @param  string  $arg  'date' or 'time'
	 * @param  array   $args Optional arguments to modify (else use $this->field->args['attributes'])
	 * @return array         Array of field attributes
	 */
	public function parse_picker_options( $arg = 'date', $args = array() ) {
		$att    = 'data-' . $arg . 'picker';
		$update = empty( $args );
		$atts   = array();
		$format = $this->field->args( $arg . '_format' );

		if ( $js_format = cmb2_utils()->php_to_js_dateformat( $format ) ) {

			if ( $update ) {
				$atts = $this->field->args( 'attributes' );
			} else {
				$atts = isset( $args['attributes'] )
					? $args['attributes']
					: $atts;
			}

			// Don't override user-provided datepicker values
			$data = isset( $atts[ $att ] )
				? json_decode( $atts[ $att ], true )
				: array();

			$data[ $arg . 'Format' ] = $js_format;
			$atts[ $att ] = function_exists( 'wp_json_encode' )
				? wp_json_encode( $data )
				: json_encode( $data );
		}

		if ( $update ) {
			$this->field->args['attributes'] = $atts;
		}

		return array_merge( $args, $atts );
	}
}
