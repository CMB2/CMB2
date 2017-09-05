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
			
			if ( $butt['text'] ) {
				
				if ( ! empty( $butt['id'] ) ) {
					$butt['attributes']['id'] = $butt['id'];
				}
				
				$ret .= get_submit_button(
					esc_attr( __( $butt['text'] ) ),
					$butt['button'],
					$butt['name'],
					$butt['wrap'],
					$butt['attributes']
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
		
		$args = $this->type_submit_minimum_config( $args );
		
		if ( empty( $args ) ) {
			return array();
		}
		
		$defaults = $this->type_submit_defaults();
		
		foreach ( $defaults as $button => $bargs ) {
			
			if ( isset( $args[ $button ] ) && is_array( $args[ $button ] ) ) {
				
				// make sure attributes which must be set have a value on the incoming options
				$args[ $button ] = $this->type_submit_property_not_empty( $bargs, $args[ $button ] );
				$args[ $button ] = array_merge( $bargs, $args[ $button ] );
				
			} else {
				
				unset( $defaults[ $button ] );
			}
		}
		
		// expose arguments to allowing filtering in base method
		return $this->parse_args( 'submit', $defaults, $args );
	}
	
	/**
	 * Will create a button:
	 *   $args[$key] = true            uses defaults
	 *   $args[$key] = 'Whatever'      sets key using 'Whatever' as button text
	 *   $args[$key] = array( params ) subs parameters for defaults
	 *
	 * Will not create a button:
	 *   $args[$key] = ''
	 *   $args[$key] = false
	 *   $args[$key] = array()
	 *   $args[$key] = 1
	 *
	 * @since 2.XXX
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function type_submit_minimum_config( $args ) {
		
		if ( ! is_array( $args ) ) {
			return array();
		}
		
		$keys = array_keys( $this->type_submit_defaults() );
		
		foreach ( $keys as $key ) {
			
			if ( ! isset( $args[ $key ] ) ) {
				
				continue;
				
			} else if ( is_bool( $args[ $key ] ) && $args[ $key ] === TRUE ) {
				
				// true will set the button, using defaults
				$args[ $key ] = array();
				
			} else if ( is_string( $args[ $key ] ) && $args[ $key ] ) {
				
				// a string will set the value
				$args[ $key ] = array(
					'text' => $args[ $key ],
				);
				
			} else if ( ! is_array( $args[ $key ] ) || empty( $args[ $key ] ) ) {
				
				// other values, including empty arrays, should remove the button
				unset( $args[ $key ] );
			}
		}
		
		return $args;
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
		
		foreach ( $checks as $check ) {
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
				'reset'  => array(
					'id'         => '',
					'name'       => 'reset-cmb',
					'text'       => 'Reset',
					'button'     => '',
					'wrap'       => FALSE,
					'attributes' => array( 'style' => 'margin-right: 10px;' ),
				),
				'submit' => array(
					'id'         => '',
					'name'       => 'submit-cmb',
					'text'       => 'Save',
					'button'     => 'primary',
					'wrap'       => FALSE,
					'attributes' => array(),
				),
			);
	}
}