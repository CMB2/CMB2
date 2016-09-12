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
class Test_CMB2_REST_Registered_Fields extends Test_CMB2_Rest_Base {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		$this->set_up_and_init( array(
			'id' => strtolower( __CLASS__ ),
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'object_types' => array( 'post' ),
			'fields' => array(
				'rest_test_registered_fields' => array(
					'name'        => 'Name',
					'id'          => 'rest_test_registered_fields',
					'type'        => 'text',
				),
				'rest_test2_registered_fields' => array(
					'name'        => 'Name',
					'id'          => 'rest_test2_registered_fields',
					'type'        => 'text',
				),
			),
		) );
	}

	public function test_rest_posts_controller_exists() {
		$this->assertTrue( class_exists( 'WP_REST_Posts_Controller' ) );
	}

	public function test_read_post_has_cmb2_data() {
		wp_set_current_user( $this->subscriber );

		$expected = array( 'cmb2' => array( $this->cmb_id => array(), ), );
		foreach ( $this->metabox_array['fields'] as $field ) {
			$expected['cmb2'][ $this->cmb_id ][ $field['id'] ] = md5( $field['id'] );
		}

		$url = '/wp/v2/posts/' . $this->post_id;
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertResponseData( $expected, $response );
	}

	public function test_update_post_cmb2_data_unauthorized() {
		wp_set_current_user( $this->subscriber );

		$url = '/wp/v2/posts/' . $this->post_id;

		$request = new WP_REST_Request( 'POST', $url );
		$cmb2 = array();
		foreach ( $this->metabox_array['fields'] as $field ) {
			$cmb2[ $this->cmb_id ][ $field['id'] ] = $field['id'];
		}

		$request['cmb2'] = $cmb2;

		$this->assertResponseStatus( 403, rest_do_request( $request ), 'rest_cannot_edit' );
	}

	public function test_update_post_cmb2_data_authorized() {
		wp_set_current_user( $this->administrator );

		$url = '/wp/v2/posts/' . $this->post_id;

		$request = new WP_REST_Request( 'POST', $url );
		$cmb2 = array();
		foreach ( $this->metabox_array['fields'] as $field ) {
			$cmb2[ $this->cmb_id ][ $field['id'] ] = $field['id'];
		}

		$request['cmb2'] = $cmb2;

		$this->assertResponseStatus( 200, rest_do_request( $request ) );

		foreach ( $this->metabox_array['fields'] as $field ) {
			$this->assertEquals( $field['id'], get_post_meta( $this->post_id, $field['id'], 1 ) );
		}

		$request = new WP_REST_Request( 'POST', $url );
		$request['cmb2'] = array( $this->cmb_id => array(
			'rest_test2_registered_fields' => 'new value',
		) );

		$this->assertResponseStatus( 200, rest_do_request( $request ) );

		$this->assertEquals( 'new value', get_post_meta( $this->post_id, 'rest_test2_registered_fields', 1 ) );
		$this->assertEquals( 'rest_test_registered_fields', get_post_meta( $this->post_id, 'rest_test_registered_fields', 1 ) );
	}

}
