<?php
/**
 * CMB2 Tab Group
 *
 * @category WordPress_Plugin
 * @package  CMB2
 * @author   CMB2 team
 * @license  GPL-2.0+
 * @link     https://cmb2.io
 */
class CMB2_Tab extends CMB2_Base {

	/**
	 * The object properties name.
	 *
	 * @var   string
	 * @since 2.2.4
	 */
	protected $properties_name = 'args';





	// https://github.com/dThemeStudio/cmb2-tabs
	/**
	 * The array of field ids in this tab.
	 *
	 * @var   array
	 * @since 2.2.4
	 */
	protected $field_ids = array();

	/**
	 * Get started
	 *
	 * @since 2.2.4
	 * @param array $args Object properties array
	 */
	public function __construct( $args, $cmb_id ) {

		$this->args = $args;
		$this->cmb_id = $this->prop( 'cmb_id' );
	}

	public function add_field( $field ) {

		if ( $field instanceof CMB2_Field ) {
			$this->field_ids[ $field->id() ] = 1;
		} elseif ( is_array( $field ) ) {
			$this->field_ids[ $field['id'] ] = 1;
		} else {
			$this->field_ids[ $field ] = 1;
		}
	}

	public function get_field_ids() {

		return $this->field_ids;
	}

}
