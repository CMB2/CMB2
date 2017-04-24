<?php
/**
 * CMB multicheck field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Multicheck extends CMB2_Type_Radio {

	/**
	 * The type of radio field
	 *
	 * @var string
	 */
	public $type = 'checkbox';

	public function render() {
		$classes = false === $this->field->args( 'select_all_button' )
			? 'cmb2-checkbox-list no-select-all cmb2-list'
			: 'cmb2-checkbox-list cmb2-list';

		$args = $this->parse_args( $this->type, array(
			'class'   => $classes,
			'options' => $this->concat_items( array(
				'name'   => $this->_name() . '[]',
				'method' => 'list_input_checkbox',
			) ),
			'desc' => $this->_desc( true ),
		) );

		return $this->rendered( $this->ul( $args ) );
	}

}
