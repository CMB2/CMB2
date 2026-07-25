<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-rest-tests-base.php' );

/**
 * Test the REST endpoints
 *
 * @todo  More Tests for maybe_hook_registered_callback.
 *
 * @group cmb2-rest-api
 *
 * @link https://pantheon.io/blog/test-coverage-your-wp-rest-api-project
 * @link https://github.com/danielbachhuber/pantheon-rest-api-demo/blob/master/tests/test-rest-api-demo.php
 */
class Test_CMB2_REST_Controllers extends Test_CMB2_Rest_Base {

	/**
	 * Set up the test fixture
	 */
	public function set_up() {
		$this->set_up_and_init( array(
			'id' => 'test',
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'fields' => array(
				'rest_test' => array(
					'name'        => 'Name',
					'id'          => 'rest_test',
					'type'        => 'text',
				),
				'rest_test2' => array(
					'name'        => 'Name',
					'id'          => 'rest_test2',
					'type'        => 'text',
				),
			),
		) );
	}

	public function tear_down() {
		parent::tear_down();
	}

	public function test_get_schema() {
		$this->assertResponseStatuses( '/' . CMB2_REST::NAME_SPACE, array(
			'GET' => 200,
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );
	}

	public function test_read_boxes() {
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );
	}

	public function test_read_box() {
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/' . __FUNCTION__;
		$this->assertResponseStatuses( $url, array(
			'GET' => array(
				403 => 'cmb2_rest_box_not_found_error',
			),
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );

		$rest = new CMB2_REST( new CMB2( array(
			'id' => 'test_read_box_test',
			'object_types' => array( 'post' ),
			'show_in_rest' => WP_REST_Server::EDITABLE,
		) ) );
		$rest->universal_hooks();

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test_read_box_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => array(
				403 => 'cmb2_rest_no_read_error',
			),
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );

		$rest = new CMB2_REST( new CMB2( array(
			'id' => 'test_edit_box_test',
			'object_types' => array( 'post' ),
			'show_in_rest' => WP_REST_Server::READABLE,
		) ) );
		$rest->universal_hooks();

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test_edit_box_test';
		$this->assertResponseStatuses( $url, array(
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );
	}

	/**
	 * Test that reading a box with read permissions callback throws an exception
	 */
	public function test_read_box_with_read_permissions_callback() {
		$rest = new CMB2_REST( new CMB2( array(
			'id' => __FUNCTION__,
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'get_box_permissions_check_cb' => 'wp_die',
		) ) );
		$rest->universal_hooks();

		$this->expectException(WPDieException::class);

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/' . __FUNCTION__;
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
	}

	public function test_read_box_fields() {
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => array(
				404 => 'rest_no_route',
			),
		) );
	}

	public function test_read_box_field() {
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => array(
				self::auth_required_code() => 'rest_forbidden',
			),
			'DELETE' => array(
				400 => 'rest_missing_callback_param',
			),
		) );

		$mb = $this->metabox_array;
		$mb['id'] = 'test2';
		foreach ( $mb['fields'] as &$field ) {
			$field['show_in_rest'] = WP_REST_Server::EDITABLE;
		}
		$rest_box2 = new CMB2_REST( new CMB2( $mb ) );
		$rest_box2->universal_hooks();

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test2/fields/rest_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => array(
				403 => 'cmb2_rest_no_field_by_id_error',
			),
			'POST' => array(
				self::auth_required_code() => 'rest_forbidden',
			),
			'DELETE' => array(
				400 => 'rest_missing_callback_param',
			),
		) );
	}

	public function test_read_box_field_filter() {
		add_filter( 'cmb2_api_get_field_permissions_check', '__return_false' );
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => array(
				self::auth_required_code() => 'rest_forbidden',
			),
			'POST' => array(
				self::auth_required_code() => 'rest_forbidden',
			),
			'DELETE' => array(
				400 => 'rest_missing_callback_param',
			),
		) );

		$request = new WP_REST_Request( 'DELETE', $url );
		$request['object_id'] = $this->post_id;
		$request['object_type'] = 'post';
		$this->assertResponseStatus( self::auth_required_code(), rest_do_request( $request ), 'rest_forbidden' );
	}

	/**
	 * Test that reading a box field with read permissions callback throws an exception
	 */
	public function test_read_box_field_with_read_permissions_callback() {
		$rest = new CMB2_REST( new CMB2( array(
			'id' => __FUNCTION__,
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'get_field_permissions_check_cb' => 'wp_die',
		) ) );
		$rest->universal_hooks();

		$this->expectException(WPDieException::class);

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/' . __FUNCTION__ . '/fields/rest_test';
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
	}

	public function test_read_box_field_with_value() {
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'GET', $url );
		$request['object_id'] = $this->post_id;
		$request['object_type'] = 'post';
		$response = rest_do_request( $request );

		$response_data = $response->get_data();
		$this->assertEquals( get_post_meta( $this->post_id, 'rest_test', 1 ), $response_data['value'] );
	}

	public function test_update_unauthorized_for_subscriber() {
		wp_set_current_user( $this->subscriber );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$response = rest_do_request( new WP_REST_Request( 'POST', $url ) );
		$this->assertResponseStatus( self::auth_required_code(), $response, 'rest_forbidden' );
	}

	public function test_update_bad_request_for_admin() {
		wp_set_current_user( $this->administrator );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'POST', $url );
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 400, $response, 'cmb2_rest_update_field_error' );
		$this->assertResponseData( array(
			'code'    => 'cmb2_rest_update_field_error',
			'message' => __( 'CMB2 Field value cannot be updated without the value parameter specified.', 'cmb2' ),
			'data'    => array(
				'status' => 400,
			),
		), $response );

		$request['value'] = 'new value';
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 400, $response, 'cmb2_rest_modify_field_value_error' );
		$this->assertResponseData( array(
			'code'    => 'cmb2_rest_modify_field_value_error',
			'message' => __( 'CMB2 Field value cannot be modified without the object_id and object_type parameters specified.', 'cmb2' ),
			'data'    => array(
				'status' => 400,
			),
		), $response );

		$request['object_id'] = $this->post_id;
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 400, $response, 'cmb2_rest_modify_field_value_error' );
		$this->assertResponseData( array(
			'code'    => 'cmb2_rest_modify_field_value_error',
			'message' => __( 'CMB2 Field value cannot be modified without the object_id and object_type parameters specified.', 'cmb2' ),
			'data'    => array(
				'status' => 400,
		    ),
		), $response );
	}

	public function test_update_authorized_for_admin() {
		wp_set_current_user( $this->administrator );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'POST', $url );
		$request['value'] = 'new value';
		$request['object_id'] = $this->post_id;
		$request['object_type'] = 'post';
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 200, $response );

		$response = rest_do_request( $request );

		$response_data = $response->get_data();
		$this->assertEquals( 'new value', get_post_meta( $this->post_id, 'rest_test', 1 ) );
		$this->assertEquals( 'new value', $response_data['value'] );
	}

	public function test_delete_unauthorized_for_subscriber() {
		wp_set_current_user( $this->subscriber );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'DELETE', $url );
		$request['object_id'] = $this->post_id;
		$request['object_type'] = 'post';
		$response = rest_do_request( $request );

		$this->assertResponseStatus( 403, $response, 'rest_forbidden' );
	}

	public function test_delete_bad_request_for_admin() {
		wp_set_current_user( $this->administrator );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'DELETE', $url );
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 400, $response, 'rest_missing_callback_param' );
		$this->assertResponseData( array(
			'code'    => 'rest_missing_callback_param',
			'message' => 'Missing parameter(s): object_id, object_type',
			'data'    => array(
				'status' => 400,
				'params' => array(
					'object_id',
					'object_type',
				),
			),
		), $response );

		$request['object_id'] = $this->post_id;
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 400, $response, 'rest_missing_callback_param' );
	}

	public function test_delete_authorized_for_admin() {
		wp_set_current_user( $this->administrator );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$request = new WP_REST_Request( 'DELETE', $url );
		$request['object_id'] = $this->post_id;
		$request['object_type'] = 'post';
		$response = rest_do_request( $request );
		$this->assertResponseStatus( 200, $response );

		$response = rest_do_request( $request );

		$response_data = $response->get_data();
		$this->assertEquals( '', get_post_meta( $this->post_id, 'rest_test', 1 ) );
		$this->assertEquals( '', $response_data['value'] );
	}

	/**
	 * Registers an options-page box (REST-readable) for the read-permission tests.
	 *
	 * @param array $args Optional box-registration overrides/additions.
	 *
	 * @return void
	 */
	protected function register_options_page_box( $args = array() ) {
		$rest = new CMB2_REST( new CMB2( wp_parse_args( $args, array(
			'id'           => 'opts_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cmb2_rest_options_test',
			'capability'   => 'manage_options',
			'fields'       => array(
				'opts_field' => array(
					'name' => 'Opts Field',
					'id'   => 'opts_field',
					'type' => 'text',
				),
			),
		) ) ) );
		$rest->universal_hooks();
	}

	/**
	 * Registers a post-object box (REST-readable) for the read-permission tests.
	 *
	 * @param array $args Optional box-registration overrides/additions.
	 *
	 * @return void
	 */
	protected function register_post_object_box( $args = array() ) {
		$rest = new CMB2_REST( new CMB2( wp_parse_args( $args, array(
			'id'           => 'post_cap_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'post' ),
			'fields'       => array(
				'post_cap_field' => array(
					'name' => 'Post Cap Field',
					'id'   => 'post_cap_field',
					'type' => 'text',
				),
			),
		) ) ) );
		$rest->universal_hooks();
	}

	/**
	 * By default (gate off), an options-page box read stays public, matching
	 * CMB2's historical REST read behavior.
	 */
	public function test_options_page_box_read_public_by_default() {
		$this->register_options_page_box();

		wp_set_current_user( 0 );
		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * When the options-page read gate is enabled, reads of an options-page box are
	 * aligned with WordPress core's settings convention (gated behind the box
	 * capability, defaulting to manage_options).
	 */
	public function test_options_page_box_read_gated_when_enabled() {
		$this->register_options_page_box();
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box';

		wp_set_current_user( 0 );
		$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $this->subscriber );
		$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $this->administrator );
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * The same options-page read gate applies to field reads on the box.
	 */
	public function test_options_page_field_read_gated_when_enabled() {
		$this->register_options_page_box();
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field';

		wp_set_current_user( $this->subscriber );
		$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $this->administrator );
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * Non-options-page (post-object) box reads are unaffected by the options-page
	 * gate, even when it is enabled.
	 */
	public function test_post_box_read_unchanged_when_options_page_gate_enabled() {
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		wp_set_current_user( 0 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/test/fields/rest_test';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * With the options-page read gate enabled, the boxes collection still lists
	 * public (post-object) boxes but omits gated options-page boxes for users
	 * lacking the box capability; users with the capability see them listed.
	 */
	public function test_boxes_collection_omits_gated_options_page_box() {
		$this->register_options_page_box();
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes';

		wp_set_current_user( 0 );
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertEquals( 200, $response->get_status() );
		$box_ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( 'test', $box_ids );
		$this->assertNotContains( 'opts_box', $box_ids );

		wp_set_current_user( $this->administrator );
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertEquals( 200, $response->get_status() );
		$box_ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( 'opts_box', $box_ids );
	}

	/**
	 * A box declaring `'rest_read_capability' => 'exist'` has opted its reads out of
	 * the gate entirely: WordPress grants the `exist` pseudo-capability to every
	 * visitor (see WP_User::has_cap(), "Everyone is allowed to exist"), so reads stay
	 * public — even for a logged-out visitor, and even when the site-wide alignment
	 * filter is enabled.
	 */
	public function test_read_capability_prop_exist_keeps_reads_public() {
		$this->register_options_page_box( array(
			'rest_read_capability' => 'exist',
		) );
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		wp_set_current_user( 0 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * `'rest_read_capability' => false` reads as "no, REST reads of this box are not
	 * permitted" — for everyone, administrators included. It maps to WordPress's
	 * `do_not_allow` pseudo-capability, which WP_User::has_cap() denies
	 * unconditionally.
	 */
	public function test_read_capability_prop_false_disables_reads_for_everyone() {
		$this->register_options_page_box( array(
			'rest_read_capability' => false,
		) );

		$urls = array(
			'/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box',
			'/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field',
		);

		foreach ( $urls as $url ) {
			wp_set_current_user( 0 );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $this->subscriber );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $this->administrator );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );
		}
	}

	/**
	 * A box with reads disabled also drops out of the boxes collection, even for an
	 * administrator.
	 */
	public function test_read_capability_prop_false_omits_box_from_collection() {
		$this->register_options_page_box( array(
			'rest_read_capability' => false,
		) );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes';

		wp_set_current_user( $this->administrator );
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertEquals( 200, $response->get_status() );
		$box_ids = wp_list_pluck( $response->get_data(), 'id' );
		$this->assertContains( 'test', $box_ids );
		$this->assertNotContains( 'opts_box', $box_ids );
	}

	/**
	 * `'rest_read_capability' => true` reads as "yes, everyone may read this box" — an
	 * alias of the `exist` capability WordPress grants every visitor. Because it is an
	 * explicit declaration, the site-wide alignment filter never applies to it.
	 */
	public function test_read_capability_prop_true_declares_reads_public() {
		$this->register_options_page_box( array(
			'rest_read_capability' => true,
		) );
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		wp_set_current_user( 0 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * `'box-capability'` gates reads behind the box's own `capability` property,
	 * without naming (and so duplicating) it. It is an explicit declaration, so it
	 * applies with the site-wide filter left off.
	 */
	public function test_read_capability_prop_box_capability_gates_by_box_capability() {
		$this->register_options_page_box( array(
			'rest_read_capability' => 'box-capability',
		) );

		$urls = array(
			'/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box',
			'/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field',
		);

		foreach ( $urls as $url ) {
			wp_set_current_user( 0 );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $this->subscriber );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $this->administrator );
			$this->assertRequestResponseStatus( 'GET', $url, 200 );
		}
	}

	/**
	 * A capability string on the box gates field reads on that box, too.
	 */
	public function test_read_capability_prop_string_gates_field_reads_without_filter() {
		$this->register_options_page_box( array(
			'rest_read_capability' => 'manage_options',
		) );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box/fields/opts_field';

		wp_set_current_user( $this->subscriber );
		$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $this->administrator );
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * A non-options-page box is gateable too, but only by explicitly declaring the
	 * capability on the box: a capability string gates reads immediately, with the
	 * site-wide filter left off.
	 */
	public function test_read_capability_prop_string_gates_post_object_box_reads() {
		$this->register_post_object_box( array(
			'rest_read_capability' => 'edit_posts',
		) );

		$author = $this->factory->user->create( array(
			'role' => 'author',
		) );

		$urls = array(
			'/' . CMB2_REST::NAME_SPACE . '/boxes/post_cap_box',
			'/' . CMB2_REST::NAME_SPACE . '/boxes/post_cap_box/fields/post_cap_field',
		);

		foreach ( $urls as $url ) {
			wp_set_current_user( 0 );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $this->subscriber );
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );

			wp_set_current_user( $author );
			$this->assertRequestResponseStatus( 'GET', $url, 200 );
		}
	}

	/**
	 * Without the prop, a non-options-page box keeps its public reads even when the
	 * site-wide options-page filter is enabled (pinned by
	 * test_post_box_read_unchanged_when_options_page_gate_enabled), and the
	 * site-wide filter does not gate it either.
	 */
	public function test_read_capability_prop_unset_leaves_post_object_box_public() {
		$this->register_post_object_box();
		add_filter( 'cmb2_rest_enforce_options_page_read_permissions', '__return_true' );

		wp_set_current_user( 0 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/post_cap_box';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/post_cap_box/fields/post_cap_field';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );
	}

	/**
	 * Registers a box whose `gated_field` carries the given `rest_read_capability`
	 * declaration, alongside an `open_field` sibling which declares nothing.
	 *
	 * @param mixed $field_declaration The field-level `rest_read_capability` value.
	 * @param array $box_args          Optional box-registration overrides/additions.
	 *
	 * @return void
	 */
	protected function register_field_cap_box( $field_declaration, $box_args = array() ) {
		$rest = new CMB2_REST( new CMB2( wp_parse_args( $box_args, array(
			'id'           => 'field_cap_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'post' ),
			'fields'       => array(
				'gated_field' => array(
					'name' => 'Gated Field',
					'id'   => 'gated_field',
					'type' => 'text',
					'rest_read_capability' => $field_declaration,
				),
				'open_field' => array(
					'name' => 'Open Field',
					'id'   => 'open_field',
					'type' => 'text',
				),
			),
		) ) ) );
		$rest->universal_hooks();
	}

	/**
	 * A read must not persist its own default answer onto the box as a `*_cb`
	 * permission parameter.
	 *
	 * CMB2::prop() stores a truthy fallback on the box, so handing the current default
	 * to it as a fallback turns one request's default into the stored answer for every
	 * later request on that box — which would let a readable field's "allowed" default
	 * carry over to a field the current user may not read.
	 */
	public function test_read_does_not_persist_permission_callback_param_on_box() {
		$this->register_field_cap_box( 'manage_options' );

		$cmb   = CMB2_REST::get_rest_box( 'field_cap_box' )->cmb;
		$gated = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/gated_field';
		$open  = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/open_field';

		wp_set_current_user( 0 );
		$this->assertRequestResponseStatus( 'GET', $open, 200 );

		$this->assertNull( $cmb->prop( 'get_field_permissions_check_cb' ) );

		// And the gated sibling is still gated after that readable-field request.
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );
	}

	/**
	 * A field naming a capability gates only that field's reads. Its sibling field and
	 * the box read itself (resolved at box level) are untouched.
	 */
	public function test_field_read_capability_prop_string_gates_only_that_field() {
		$this->register_field_cap_box( 'manage_options' );

		$gated = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/gated_field';
		$open  = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/open_field';
		$box   = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box';

		wp_set_current_user( 0 );
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );
		$this->assertRequestResponseStatus( 'GET', $open, 200 );
		$this->assertRequestResponseStatus( 'GET', $box, 200 );

		wp_set_current_user( $this->subscriber );
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $this->administrator );
		$this->assertRequestResponseStatus( 'GET', $gated, 200 );
	}

	/**
	 * The fields collection omits fields the current user cannot read, and lists the
	 * rest.
	 */
	public function test_fields_collection_omits_fields_gated_by_field_prop() {
		$this->register_field_cap_box( 'manage_options' );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields';

		wp_set_current_user( 0 );
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertEquals( 200, $response->get_status() );
		$field_ids = array_keys( $response->get_data() );
		$this->assertContains( 'open_field', $field_ids );
		$this->assertNotContains( 'gated_field', $field_ids );

		wp_set_current_user( $this->administrator );
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertEquals( 200, $response->get_status() );
		$field_ids = array_keys( $response->get_data() );
		$this->assertContains( 'gated_field', $field_ids );
	}

	/**
	 * A field declaring `false` has its reads disabled for everyone, administrators
	 * included, and drops out of the fields collection entirely.
	 */
	public function test_field_read_capability_prop_false_disables_that_field_for_everyone() {
		$this->register_field_cap_box( false );

		$gated = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/gated_field';
		$open  = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/open_field';

		foreach ( array( 0, $this->subscriber, $this->administrator ) as $user_id ) {
			wp_set_current_user( $user_id );
			$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );
			$this->assertRequestResponseStatus( 'GET', $open, 200 );
		}

		wp_set_current_user( $this->administrator );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields' ) );
		$this->assertEquals( 200, $response->get_status() );
		$field_ids = array_keys( $response->get_data() );
		$this->assertContains( 'open_field', $field_ids );
		$this->assertNotContains( 'gated_field', $field_ids );
	}

	/**
	 * A field-level declaration wins over the box's: the box declares its reads public
	 * (`true`), and the field still requires its named capability.
	 */
	public function test_field_read_capability_prop_overrides_public_box_prop() {
		$this->register_field_cap_box( 'manage_options', array(
			'rest_read_capability' => true,
		) );

		$gated = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/gated_field';
		$open  = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/open_field';
		$box   = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box';

		wp_set_current_user( 0 );
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );
		$this->assertRequestResponseStatus( 'GET', $open, 200 );
		$this->assertRequestResponseStatus( 'GET', $box, 200 );

		wp_set_current_user( $this->administrator );
		$this->assertRequestResponseStatus( 'GET', $gated, 200 );
	}

	/**
	 * A field declaring `'box-capability'` is gated by the box's `capability`, even
	 * where the box has declared its own reads public — the field borrows the box's
	 * capability without restating it.
	 */
	public function test_field_read_capability_prop_box_capability_gates_by_box_capability() {
		$this->register_field_cap_box( 'box-capability', array(
			'capability'           => 'edit_posts',
			'rest_read_capability' => true,
		) );

		$gated = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/gated_field';
		$open  = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/open_field';

		$author = $this->factory->user->create( array(
			'role' => 'author',
		) );

		wp_set_current_user( 0 );
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );
		$this->assertRequestResponseStatus( 'GET', $open, 200 );

		wp_set_current_user( $this->subscriber );
		$this->assertRequestResponseStatus( 'GET', $gated, self::auth_required_code(), 'rest_forbidden' );

		wp_set_current_user( $author );
		$this->assertRequestResponseStatus( 'GET', $gated, 200 );
		$this->assertRequestResponseStatus( 'GET', $open, 200 );
	}

	/**
	 * Without a field-level declaration, a field falls back to the box's — here, reads
	 * disabled at the box level cover every field on it.
	 */
	public function test_field_read_capability_falls_back_to_box_prop() {
		$this->register_field_cap_box( null, array(
			'rest_read_capability' => false,
		) );

		wp_set_current_user( $this->administrator );

		foreach ( array( 'gated_field', 'open_field' ) as $field_id ) {
			$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/field_cap_box/fields/' . $field_id;
			$this->assertRequestResponseStatus( 'GET', $url, self::auth_required_code(), 'rest_forbidden' );
		}
	}

	/**
	 * The `cmb2_api_get_box_permissions_check` filter still runs after the
	 * capability gate and keeps the final say (see CONVENTIONS.md C4).
	 */
	public function test_read_capability_prop_still_overridable_by_permissions_filter() {
		$this->register_options_page_box( array(
			'rest_read_capability' => 'manage_options',
		) );
		add_filter( 'cmb2_api_get_box_permissions_check', '__return_true' );

		wp_set_current_user( 0 );

		$url = '/' . CMB2_REST::NAME_SPACE . '/boxes/opts_box';
		$this->assertRequestResponseStatus( 'GET', $url, 200 );

		remove_filter( 'cmb2_api_get_box_permissions_check', '__return_true' );
	}

	protected static function auth_required_code() {
		return function_exists( 'rest_authorization_required_code' ) ? rest_authorization_required_code() : 403;
	}

}
