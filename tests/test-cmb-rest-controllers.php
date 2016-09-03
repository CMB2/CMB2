<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-rest-tests-base.php' );

/**
 * Test the REST endpoints
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
		parent::setUp();

		update_option( 'permalink_structure', '/%postname%/' );

		$this->cmb_id = 'test';
		$this->metabox_array = array(
			'id' => $this->cmb_id,
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
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
		);

		$this->cmb = new CMB2( $this->metabox_array );

		$mb = $this->metabox_array;
		$mb['id'] = 'test2';
		foreach ( $mb['fields'] as &$field ) {
			$field['show_in_rest'] = WP_REST_Server::EDITABLE;
		}
		$this->cmb2 = new CMB2( $mb );

		$this->rest_box = new Test_CMB2_REST_Object( $this->cmb );
		$this->rest_box2 = new Test_CMB2_REST_Object( $this->cmb );
		$this->rest_box->universal_hooks();
		$this->rest_box2->universal_hooks();

		do_action( 'rest_api_init' );

		$this->subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$this->post_id = $this->factory->post->create();

		foreach ( $this->metabox_array['fields'] as $field ) {
			update_post_meta( $this->post_id, $field['id'], md5( $field['id'] ) );
		}

		cmb2_bootstrap();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_schema() {
		$this->assertResponseStatuses( '/' . CMB2_REST::NAMESPACE, array(
			'GET' => 200,
			'POST' => 404,
		) );
	}

	public function test_read_boxes() {
		$url = '/' . CMB2_REST::NAMESPACE . '/boxes';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => 404,
		) );
	}

	public function test_read_box() {
		$url = '/' . CMB2_REST::NAMESPACE . '/boxes/test';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => 404,
		) );
	}

	public function test_read_box_fields() {
		$url = '/' . CMB2_REST::NAMESPACE . '/boxes/test/fields';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => 404,
		) );
	}

	public function test_read_box_field() {
		$url = '/' . CMB2_REST::NAMESPACE . '/boxes/test/fields/rest_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => 200,
			'POST' => 403,
		) );

		$url = '/' . CMB2_REST::NAMESPACE . '/boxes/test2/fields/rest_test';
		$this->assertResponseStatuses( $url, array(
			'GET' => 403,
			'POST' => 403,
		) );
	}

	// public function test_get_unauthorized() {
	// 	// wp_set_current_user( 0 );

	// 	$path = '/' . CMB2_REST::NAMESPACE . '/boxes/test/fields/rest_test';
	// 	$response = rest_do_request( new WP_REST_Request( 'POST', $path ) );
	// 	error_log( '$response->data: '. print_r( $response->data, true ) );
	// 	$this->assertResponseStatus( 403, $response );

	// }

	// public function test_get_authorized() {
	// 	wp_set_current_user( $this->subscriber );
	// 	$request = new WP_REST_Request( 'GET', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '(555) 212-2121',
	// 	), $response );
	// }

	// public function test_get_authorized_reformatted() {
	// 	update_option( 'phone_number', '555 555 5555' );
	// 	wp_set_current_user( $this->subscriber );
	// 	$request = new WP_REST_Request( 'GET', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '(555) 555-5555',
	// 	), $response );
	// }

	// public function test_get_authorized_invalid_format() {
	// 	update_option( 'phone_number', 'will this work?' );
	// 	wp_set_current_user( $this->subscriber );
	// 	$request = new WP_REST_Request( 'GET', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '',
	// 	), $response );
	// }

	// public function test_update_unauthorized() {
	// 	wp_set_current_user( $this->subscriber );
	// 	$request = new WP_REST_Request( 'POST', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$request->set_param( 'phone_number', '(111) 222-3333' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 403, $response );
	// 	$this->assertEquals( '(555) 212-2121', get_option( 'phone_number' ) );
	// }

	// public function test_update_authorized() {
	// 	wp_set_current_user( $this->administrator );
	// 	$request = new WP_REST_Request( 'POST', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$request->set_param( 'phone_number', '(111) 222-3333' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '(111) 222-3333',
	// 	), $response );
	// 	$this->assertEquals( '(111) 222-3333', get_option( 'phone_number' ) );
	// }

	// public function test_update_authorized_reformatted() {
	// 	wp_set_current_user( $this->administrator );
	// 	$request = new WP_REST_Request( 'POST', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$request->set_param( 'phone_number', '555 555 5555' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '(555) 555-5555',
	// 	), $response );
	// 	$this->assertEquals( '(555) 555-5555', get_option( 'phone_number' ) );
	// }

	// public function test_update_authorized_empty() {
	// 	wp_set_current_user( $this->administrator );
	// 	$request = new WP_REST_Request( 'POST', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$request->set_param( 'phone_number', '' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 200, $response );
	// 	$this->assertResponseData( array(
	// 		'phone_number' => '',
	// 	), $response );
	// 	$this->assertEquals( '', get_option( 'phone_number' ) );
	// }

	// public function test_update_authorized_invalid_format() {
	// 	wp_set_current_user( $this->administrator );
	// 	$request = new WP_REST_Request( 'POST', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$request->set_param( 'phone_number', 'will this work?' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 400, $response );
	// 	$this->assertResponseData( array(
	// 		'message' => 'Invalid parameter(s): phone_number',
	// 	), $response );
	// 	$this->assertEquals( '(555) 212-2121', get_option( 'phone_number' ) );
	// }

	// function test_format_phone_number() {
	// 	$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '555-212-2121' ) );
	// 	$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '5552122121' ) );
	// 	$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '+1 (555) 212 2121' ) );
	// 	$this->assertEquals( '', rad_format_phone_number( '' ) );
	// }

}
