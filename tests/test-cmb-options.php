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
class Test_CMB2_Options extends CMB2TestCase {

	// Options-specific test properties
	protected $option_metabox_array;
	protected $options_cmb;
	protected $opt_set;

	/**
	 * Set up the test fixture
	 */
	public function set_up() {
		parent::set_up();

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
		$this->assertIsArray( $opts->get_options() );
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

	/**
	 * A field's value must be removable from an options page even when it is the
	 * only key in the option, i.e. when no other field triggers a save.
	 *
	 * Regression test for https://github.com/CMB2/CMB2/issues/1509.
	 *
	 * @since 2.13.2
	 */
	public function test_cmb2_remove_option_persists_to_db() {
		$option_key = 'cmb_remove_persist_option';
		cmb2_options( $option_key )->delete_option();

		// Seed one value, as if the user had saved the field once.
		cmb2_options( $option_key )->set( array( 'first' => 'foo' ) );

		$this->assertSame( array( 'first' => 'foo' ), get_option( $option_key ) );

		$opts = cmb2_options( $option_key );
		$opts->remove( 'first', true );

		/*
		 * remove() must have written the change, not just altered its in-memory
		 * copy. The stored option is now an empty array rather than false: what
		 * matters for #1509 is that the 'first' key is gone from the database.
		 * Reading the option without going through this object proves the write.
		 */
		$this->assertArrayNotHasKey( 'first', (array) get_option( $option_key ) );
		$this->assertSame( array(), cmb2_options( $option_key )->get_options() );
	}

	/**
	 * Without the second argument, remove() is expected to stay in memory only:
	 * the option on disk is untouched. Guards the documented default.
	 *
	 * @since 2.13.2
	 */
	public function test_cmb2_remove_option_without_resave_stays_in_memory() {
		$option_key = 'cmb_remove_noresave_option';
		cmb2_options( $option_key )->delete_option();

		$opts = cmb2_options( $option_key );
		$opts->set( array( 'first' => 'foo', 'second' => 'bar' ) );

		$opts->remove( 'first' );

		// In-memory copy dropped the key...
		$this->assertSame( array( 'second' => 'bar' ), $opts->get_options() );

		// ...but the stored option still holds it.
		$this->assertSame( array( 'first' => 'foo', 'second' => 'bar' ), get_option( $option_key ) );
	}

}
