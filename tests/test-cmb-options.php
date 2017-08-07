<?php
/**
 * CMB2_Field tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-tests-base.php' );

/**
 * Test the oEmbed functionality
 */
class Test_CMB2_Options extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->option_metabox_array = array(
			'id'            => 'options_page',
			'title'         => 'Theme Options Metabox',
			'show_on'    => array(
				'options-page' => array( 'theme_options' ),
			),
			'fields'        => array(
				'bg_color' => array(
					'name'    => 'Site Background Color',
					'desc'    => 'field description (optional)',
					'id'      => 'bg_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff',
				),
			),
		);

		$this->options_cmb = new CMB2( $this->option_metabox_array );

		$this->opt_set = array(
			'bg_color' => '#ffffff',
			'my_name' => 'Justin',
		);
		add_option( $this->options_cmb->cmb_id, $this->opt_set );
	}

	public function test_cmb2_options_function() {
		$opts = cmb2_options( $this->options_cmb->cmb_id );
		$this->assertSame( $opts->get_options(), $this->opt_set );
	}

	public function test_cmb2_get_option() {
		$get = get_option( $this->options_cmb->cmb_id );
		$val = cmb2_get_option( $this->options_cmb->cmb_id, 'my_name' );

		$this->assertSame( $this->opt_set['my_name'], $get['my_name'] );
		$this->assertSame( $val, $get['my_name'] );
		$this->assertSame( $val, $this->opt_set['my_name'] );
	}

	public function test_cmb2_get_option_bad_value() {
		$opts = cmb2_options( $this->options_cmb->cmb_id );

		$opts->set( '1' );

		$get = get_option( $this->options_cmb->cmb_id );
		$val = $opts->get_options();

		$this->assertSame( '1', $get );
		$this->assertSame( array( '1' ), $val );

		$opts->delete_option();
		$get = get_option( $this->options_cmb->cmb_id );
		$val = $opts->get_options();

		$this->assertSame( false, $get );
		$this->assertSame( array(), $val );

		// Reset the option for future tests.
		$opts->set( $this->opt_set );
	}

	public function test_cmb2_remove_option_bad_value() {
		$opts = cmb2_options( $this->options_cmb->cmb_id );
		$opts->delete_option();

		$val = $opts->remove( 'my_name' );

		$this->assertSame( array(), $val );

		$opts->set( $this->opt_set );
	}

	public function test_cmb2_update_option() {
		$new_value = 'James';

		cmb2_update_option( $this->options_cmb->cmb_id, 'my_name', $new_value );

		$get = get_option( $this->options_cmb->cmb_id );
		$val = cmb2_get_option( $this->options_cmb->cmb_id, 'my_name' );

		$this->assertSame( $new_value, $get['my_name'] );
		$this->assertSame( $val, $get['my_name'] );
		$this->assertSame( $val, $new_value );
	}

	public function test_cmb2_with_empty_options() {
		$opts = cmb2_options( 'cmb_empty_option' );
		$this->assertInternalType( 'array', $opts->get_options() );
		$this->assertSame( array(), $opts->get_options() );
	}

	public function test_cmb2_get_option_with_empty_options() {
		$opts = cmb2_options( 'cmb_empty_option' );
		$this->assertFalse( $opts->get( 'nothing' ) );
	}

	public function test_cmb2_update_option_with_empty_options() {
		$new_value = 'Van Anh';

		cmb2_update_option( 'cmb_empty_option', 'my_name', $new_value );

		$get = get_option( 'cmb_empty_option' );
		$val = cmb2_get_option( 'cmb_empty_option', 'my_name' );

		$this->assertSame( $new_value, $get['my_name'] );
		$this->assertSame( $val, $get['my_name'] );
		$this->assertSame( $val, $new_value );
	}

}
