<?php

/**
 * CMB submit field type, used by options pages.
 *
 * Called by field types: 'submit', 'reset', 'submit_and_reset'.
 *
 * Argument handling is a bit wonky as wp_parse_args cannot handle recursive arguments.
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Submit extends CMB2_Type_Base {
	
	public function render() {
		
		$a   = $this->type_submit_parse_args( $this->args );
		$ret = '';
		
		foreach ( $a as $butt ) {
			
			if ( $butt[ 'text' ] ) {
				
				if ( ! empty( $butt[ 'id' ] ) ) {
					$butt[ 'attributes' ]['id'] = $butt[ 'id' ];
				}
				
				$ret .= get_submit_button(
					esc_attr( __( $butt[ 'text' ] ) ),
					$butt[ 'button' ],
					$butt[ 'name' ],
					$butt[ 'wrap' ],
					$butt[ 'attributes' ]
				);
			}
		}
		
		return $ret;
	}
	
	/**
	 * wp_parse_args is not recursive; need to merge the defaults before passing to base class parser
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function type_submit_parse_args( $args ) {
		
		foreach( $this->type_submit_defaults() as $button => $bargs ) {
			
			if ( isset( $args[ $button ] ) && is_array( $args[ $button ] ) ) {
				
				// make sure attributes which must be set have a value on the incoming options
				$args[ $button ] = $this->type_submit_property_not_empty( $bargs, $args[ $button ] );
				$args[ $button ] = array_merge( $bargs, $args[ $button ] );
			}
		}
		
		// expose arguments to allowing filtering in base method
		return $this->parse_args( 'submit', $this->type_submit_defaults(), $args );
	}
	
	/**
	 * Makes sure that empty values were not passed via field
	 *
	 * @param array $checks
	 * @param array $button_defaults
	 * @param array $button_args
	 *
	 * @return array
	 */
	public function type_submit_property_not_empty( $button_defaults, $button_args, $checks = array() ) {
		
		$checks = empty( $checks ) ? array( 'name', 'button', 'wrap' ) : $checks;
		
		foreach( $checks as $check ) {
			if ( empty( $button_args[ $check ] ) ) {
				$button_args[ $check ] = $button_defaults[ $check ];
			}
		}
		
		return $button_args;
	}
	
	/**
	 * Set the defaults.
	 *
	 * @param array $defaults
	 *
	 * @return array
	 */
	public function type_submit_defaults( $defaults = array() ) {
		
		return ! empty( $defaults ) ? $defaults :
			array(
			'reset' => array(
				'id' => '',
				'name' => 'reset-cmb',
				'text' => '',
				'button' => 'secondary',
				'wrap' => false,
				'attributes' => array( 'style' => 'margin-right: 10px;' ),
			),
			'submit' => array(
				'id' => '',
				'name' => 'submit-cmb',
				'text' => 'Save',
				'button' => 'primary',
				'wrap' => false,
				'attributes' => array(),
			),
		);
	}
}