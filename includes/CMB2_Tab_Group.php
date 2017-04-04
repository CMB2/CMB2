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
class CMB2_Tab_Group extends CMB2_Base {

	/**
	 * The object properties name.
	 *
	 * @var   string
	 * @since 2.2.4
	 */
	protected $properties_name = 'args';

	/**
	 * The array of tabs in this tab group.
	 *
	 * @var   array
	 * @since 2.2.4
	 */
	protected $tabs = array();

	/**
	 * Get started
	 *
	 * @since 2.2.4
	 * @param array $args Object properties array
	 */
	public function __construct( $args, $cmb_id ) {

		$this->args = $args;
		$this->cmb_id = $cmb_id;
	}

	public function add_tab( $args ) {

		$args['parent_id'] = $this->prop( 'id' );

		return $this->tabs[ $args['id'] ] = new CMB2_Tab( $args, $this->cmb_id );
	}

	public function get_tabs() {

		return $this->tabs;
	}

}
