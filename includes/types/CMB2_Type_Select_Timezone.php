<?php
/**
 * CMB select_timezone field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Select_Timezone extends CMB2_Type_Select {

	public function render() {

		$this->field->args['default'] = $this->field->get_default()
			? $this->field->get_default()
			: cmb2_utils()->timezone_string();

		$this->args = wp_parse_args( $this->args, array(
			'class'   => 'cmb2_select cmb2-select-timezone',
			'options' => wp_timezone_choice( $this->field->escaped_value() ),
			'desc'    => $this->_desc(),
		) );

		return parent::render();
	}
}
