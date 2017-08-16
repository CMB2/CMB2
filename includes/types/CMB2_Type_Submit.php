<?php
/**
 * CMB submit field type, used by options pages.
 *
 * Called by field types: 'submit', 'reset', 'submit_and_reset'
 *
 * @since  2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Submit extends CMB2_Type_Base {
	
	public function render( $args = array(), $action = 'submit' ) {
		
		$args = empty( $args ) ? $this->args : $args;
		
		$a = $this->parse_args( $this->type, array(
			'id'          => '',
			'name'        => $action . '-cmb',
			'text'        => $action == 'submit' ? __( 'Save', 'cmb2' ) : __( 'Reset', 'cmb2' ),
			'wrap'        => false,
			'button_type' => $action == 'submit' ? 'primary' : 'secondary',
			'attributes'  => array(),
		), $args );
		
		if ( ! empty( $a['id'] ) ) {
			$a['attributes']['id'] = $a['id'];
		}
		
		return get_submit_button( esc_attr( $a['text'] ), $a['button_type'], $a['name'], $a['wrap'], $a['attributes'] );
	}
}