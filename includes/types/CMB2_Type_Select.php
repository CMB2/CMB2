<?php
/**
 * CMB select field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Select extends CMB2_Type_Multi_Base {

	public function render() {
		$a = $this->parse_args( 'select', array(
			'class'   => 'cmb2_select',
			'name'    => $this->_name(),
			'id'      => $this->_id(),
			'options' => $this->concat_items(),
		) );

		$attrs = $this->concat_attrs( $a, array( 'options' ) );

		return $this->rendered(
			sprintf( '<select%s>%s</select>', $attrs, $a['options'] )
		);
	}
}
