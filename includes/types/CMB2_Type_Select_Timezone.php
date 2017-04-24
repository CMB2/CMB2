<?php
/**
 * CMB select_timezone field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Select_Timezone extends CMB2_Type_Select {

	public function render() {

		$this->field->args['default'] = $this->field->get_default()
			? $this->field->get_default()
			: CMB2_Utils::timezone_string();

		$this->args = wp_parse_args( $this->args, array(
			'class'   => 'cmb2_select cmb2-select-timezone',
			'options' => wp_timezone_choice( $this->field->escaped_value() ),
			'desc'    => $this->_desc(),
		) );

		return parent::render();
	}
}
