<?php
/**
 * CMB2 tests for the REST read-permissions admin notice.
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-tests-base.php' );

/**
 * Tests the eligibility logic for the admin notice that informs site owners about
 * the upcoming change to CMB2's REST API read permissions for settings data.
 *
 * @group cmb2-rest-api
 */
class Test_CMB2_Rest_Read_Permissions_Notice extends CMB2TestCase {

	public function set_up() {
		parent::set_up();
		delete_option( CMB2_Rest_Read_Permissions_Notice::DISMISSED_OPTION );
		CMB2_Rest_Read_Permissions_Notice::reset();

		// The notice now only tracks boxes on the admin side.
		set_current_screen( 'dashboard' );
	}

	public function tear_down() {
		delete_option( CMB2_Rest_Read_Permissions_Notice::DISMISSED_OPTION );
		CMB2_Rest_Read_Permissions_Notice::reset();

		remove_all_filters( 'cmb2_rest_enforce_options_page_read_permissions' );

		foreach ( CMB2_Boxes::get_all() as $box ) {
			CMB2_Boxes::remove( $box->cmb_id );
		}

		parent::tear_down();
	}

	/**
	 * Fires the per-box hookup action so the box is fed into the notice exactly
	 * as the CMB2 constructor wires it (via `cmb2_init_hookup_{$cmb_id}`).
	 *
	 * @param CMB2 $cmb The box to hook up.
	 *
	 * @return CMB2
	 */
	protected function hookup_box( CMB2 $cmb ) {
		do_action( "cmb2_init_hookup_{$cmb->cmb_id}", $cmb );

		return $cmb;
	}

	/**
	 * Registers a REST-readable options-page box (an affected box).
	 *
	 * @param array $args Optional box-registration overrides/additions.
	 *
	 * @return CMB2
	 */
	protected function register_options_page_box( $args = array() ) {
		return $this->hookup_box( new CMB2( wp_parse_args( $args, array(
			'id'           => 'notice_opts_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cmb2_notice_options_test',
			'capability'   => 'manage_options',
			'fields'       => array(
				'opts_field' => array(
					'name' => 'Opts Field',
					'id'   => 'opts_field',
					'type' => 'text',
				),
			),
		) ) ) );
	}

	/**
	 * Registers a REST-readable post-object box (not an affected box).
	 *
	 * @return CMB2
	 */
	protected function register_post_box() {
		return $this->hookup_box( new CMB2( array(
			'id'           => 'notice_post_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'post' ),
			'fields'       => array(
				'post_field' => array(
					'name' => 'Post Field',
					'id'   => 'post_field',
					'type' => 'text',
				),
			),
		) ) );
	}

	public function test_eligible_when_affected_box_registered() {
		$this->register_options_page_box();

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_constructor_wiring_feeds_options_page_box_into_notice() {
		// A box that has not yet been hooked up should not be tracked.
		$cmb = new CMB2( array(
			'id'           => 'notice_wiring_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cmb2_notice_wiring_test',
			'capability'   => 'manage_options',
			'fields'       => array(
				'wiring_field' => array(
					'name' => 'Wiring Field',
					'id'   => 'wiring_field',
					'type' => 'text',
				),
			),
		) );

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );

		// Firing the action the CMB2 constructor registered feeds the box in.
		do_action( "cmb2_init_hookup_{$cmb->cmb_id}", $cmb );

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_not_tracked_outside_admin() {
		set_current_screen( 'front' );
		$this->register_options_page_box();

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_not_eligible_with_no_boxes() {
		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_not_eligible_with_only_post_boxes() {
		$this->register_post_box();

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_not_eligible_when_gate_already_enabled() {
		$this->register_options_page_box();
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	/**
	 * A box that explicitly declares its REST reads public (`rest_read_capability`
	 * => false) has stated its intent, so its owner needs no nag.
	 */
	public function test_not_eligible_when_box_declares_public_reads() {
		$this->register_options_page_box( array(
			'rest_read_capability' => false,
		) );

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	/**
	 * A box that has already opted its reads into the capability gate needs no nag.
	 */
	public function test_not_eligible_when_box_opts_into_capability_gate() {
		$this->register_options_page_box( array(
			'rest_read_capability' => true,
		) );

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	/**
	 * Same for a box naming an explicit capability.
	 */
	public function test_not_eligible_when_box_names_a_capability() {
		$this->register_options_page_box( array(
			'rest_read_capability' => 'manage_options',
		) );

		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	/**
	 * A degenerate empty-string prop is not a declaration — the gate treats it as
	 * unset, so the notice must too.
	 */
	public function test_still_eligible_when_prop_is_empty_string() {
		$this->register_options_page_box( array(
			'rest_read_capability' => '',
		) );

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	/**
	 * A box with the prop left unset is still eligible, even alongside a box that
	 * has declared its intent.
	 */
	public function test_still_eligible_when_another_box_leaves_prop_unset() {
		$this->register_options_page_box( array(
			'rest_read_capability' => false,
		) );
		$this->hookup_box( new CMB2( array(
			'id'           => 'notice_opts_box_unset',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cmb2_notice_options_unset_test',
			'capability'   => 'manage_options',
			'fields'       => array(
				'opts_field' => array(
					'name' => 'Opts Field',
					'id'   => 'opts_field',
					'type' => 'text',
				),
			),
		) ) );

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_not_eligible_after_dismissal_persists() {
		$this->register_options_page_box();

		CMB2_Rest_Read_Permissions_Notice::dismiss();

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::is_dismissed() );
		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_render_outputs_copy_when_eligible() {
		$this->register_options_page_box();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		CMB2_Rest_Read_Permissions_Notice::render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Upcoming change to REST API read permissions', $output );
		$this->assertStringContainsString( CMB2_Rest_Read_Permissions_Notice::GUIDE_URL, $output );
	}

	public function test_render_outputs_nothing_when_not_eligible() {
		ob_start();
		CMB2_Rest_Read_Permissions_Notice::render();
		$output = ob_get_clean();

		$this->assertSame( '', trim( $output ) );
	}
}
