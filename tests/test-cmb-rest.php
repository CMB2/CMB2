<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

/**
 * Test the REST endpoints
 *
 * @link https://pantheon.io/blog/test-coverage-your-wp-rest-api-project
 * @link https://github.com/danielbachhuber/pantheon-rest-api-demo/blob/master/tests/test-rest-api-demo.php
 */
class Test_CMB2_REST extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->cmb_id = 'test';
		$this->metabox_array = array(
			'classes' => 'custom-class another-class',
			'classes_cb' => array( __CLASS__, 'custom_classes' ),
			'id' => $this->cmb_id,
			'fields' => array(
				'test_test' => array(
					'name'        => 'Name',
					'description' => 'Description',
					'id'          => 'test_test',
					'type'        => 'text',
					'before_row'  => array( __CLASS__, 'cmb_before_row' ),
					'before'      => 'testing before',
					'after'       => array( __CLASS__, 'cmb_after' ),
					'after_row'   => 'testing after row',
				),
			),
		);

		$this->cmb = new CMB2( $this->metabox_array );
		$this->rest_box = new CMB2_REST( $this->cmb );

		$this->subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );
		// $this->post_id = $this->factory->post->create();
	}

	public function tearDown() {
		parent::tearDown();

		global $wp_rest_server;
		$wp_rest_server = null;
	}

	public function test_construction() {
		$this->assertTrue( $this->rest_box->cmb instanceof CMB2 );
	}

	// public function test_get_unauthorized() {
	// 	wp_set_current_user( 0 );
	// 	$request = new WP_REST_Request( 'GET', CMB2_REST::NAMESPACE . '/site-info' );
	// 	$response = $this->server->dispatch( $request );
	// 	$this->assertResponseStatus( 401, $response );
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

	// protected function assertResponseStatus( $status, $response ) {
	// 	$this->assertEquals( $status, $response->get_status() );
	// }

	// protected function assertResponseData( $data, $response ) {
	// 	$response_data = $response->get_data();
	// 	$tested_data = array();
	// 	foreach( $data as $key => $value ) {
	// 		if ( isset( $response_data[ $key ] ) ) {
	// 			$tested_data[ $key ] = $response_data[ $key ];
	// 		} else {
	// 			$tested_data[ $key ] = null;
	// 		}
	// 	}
	// 	$this->assertEquals( $data, $tested_data );
	// }

}
