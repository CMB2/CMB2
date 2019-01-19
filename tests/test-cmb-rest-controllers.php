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
	public function setUp() {
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

	public function tearDown() {
		parent::tearDown();
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
	 * @expectedException WPDieException
	 */
	public function test_read_box_with_read_permissions_callback() {
		$rest = new CMB2_REST( new CMB2( array(
			'id' => __FUNCTION__,
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'get_box_permissions_check_cb' => 'wp_die',
		) ) );
		$rest->universal_hooks();

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
	 * @expectedException WPDieException
	 */
	public function test_read_box_field_with_read_permissions_callback() {
		$rest = new CMB2_REST( new CMB2( array(
			'id' => __FUNCTION__,
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'get_field_permissions_check_cb' => 'wp_die',
		) ) );
		$rest->universal_hooks();

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

	protected static function auth_required_code() {
		return function_exists( 'rest_authorization_required_code' ) ? rest_authorization_required_code() : 403;
	}

}
