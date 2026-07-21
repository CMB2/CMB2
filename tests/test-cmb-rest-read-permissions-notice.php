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
	}

	public function tear_down() {
		delete_option( CMB2_Rest_Read_Permissions_Notice::DISMISSED_OPTION );

		remove_all_filters( 'cmb2_rest_enforce_options_page_read_permissions' );
		remove_all_filters( 'cmb2_rest_read_permissions_guide_url' );

		foreach ( CMB2_Boxes::get_all() as $box ) {
			CMB2_Boxes::remove( $box->cmb_id );
		}

		parent::tear_down();
	}

	/**
	 * Registers a REST-readable options-page box (an affected box).
	 *
	 * @return CMB2
	 */
	protected function register_options_page_box() {
		return new CMB2( array(
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
		) );
	}

	/**
	 * Registers a REST-readable post-object box (not an affected box).
	 *
	 * @return CMB2
	 */
	protected function register_post_box() {
		return new CMB2( array(
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
		) );
	}

	public function test_eligible_when_affected_box_registered() {
		$this->register_options_page_box();

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::should_show() );
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

	public function test_not_eligible_after_dismissal_persists() {
		$this->register_options_page_box();

		CMB2_Rest_Read_Permissions_Notice::dismiss();

		$this->assertTrue( CMB2_Rest_Read_Permissions_Notice::is_dismissed() );
		$this->assertFalse( CMB2_Rest_Read_Permissions_Notice::should_show() );
	}

	public function test_guide_url_filterable() {
		$this->assertSame(
			CMB2_Rest_Read_Permissions_Notice::GUIDE_URL,
			CMB2_Rest_Read_Permissions_Notice::guide_url()
		);

		add_filter( 'cmb2_rest_read_permissions_guide_url', function () {
			return 'https://example.com/custom-guide';
		} );

		$this->assertSame(
			'https://example.com/custom-guide',
			CMB2_Rest_Read_Permissions_Notice::guide_url()
		);
	}

	public function test_render_outputs_copy_when_eligible() {
		$this->register_options_page_box();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		CMB2_Rest_Read_Permissions_Notice::render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Upcoming change to REST API read permissions', $output );
		$this->assertStringContainsString( CMB2_Rest_Read_Permissions_Notice::guide_url(), $output );
	}

	public function test_render_outputs_nothing_when_not_eligible() {
		ob_start();
		CMB2_Rest_Read_Permissions_Notice::render();
		$output = ob_get_clean();

		$this->assertSame( '', trim( $output ) );
	}
}
