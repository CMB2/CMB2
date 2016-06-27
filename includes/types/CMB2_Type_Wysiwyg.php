<?php
/**
 * CMB wysiwyg field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Wysiwyg extends CMB2_Type_Base {

	/**
	 * Handles outputting a 'wysiwyg' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form wysiwyg element
	 */
	public function render() {
		$a = $this->parse_args( 'wysiwyg', array(
			'id'      => $this->_id(),
			'value'   => $this->field->escaped_value( 'stripslashes' ),
			'desc'    => $this->_desc( true ),
			'options' => $this->field->options(),
		) );

		ob_start();
		wp_editor( $a['value'], $a['id'], $a['options'] );
		echo $a['desc'];

		return $this->rendered( ob_get_clean() );
	}
}
