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
		$metabox_array = array(
			'id' => strtolower( __CLASS__ ) . '_post',
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
		);

		$metabox_array['id'] = strtolower( __CLASS__ ) . '_user';
		$metabox_array['object_types'] = 'user';
		$this->user_box = new Test_CMB2_REST_Object( new CMB2( $metabox_array ) );

		$metabox_array['id'] = strtolower( __CLASS__ ) . '_comment';
		$metabox_array['object_types'] = 'comment';
		$this->comment_box = new Test_CMB2_REST_Object( new CMB2( $metabox_array ) );

		$metabox_array['id'] = strtolower( __CLASS__ ) . '_term';
		$metabox_array['object_types'] = 'term';
		$metabox_array['taxonomies'] = 'category';
		$this->term_box = new Test_CMB2_REST_Object( new CMB2( $metabox_array ) );

		$metabox_array['id'] = strtolower( __CLASS__ ) . '_post';
		$metabox_array['object_types'] = 'post';
		unset( $metabox_array['taxonomies'] );
		$this->set_up_and_init( $metabox_array );

		$this->comment_id = $this->factory->comment->create( array(
			'comment_author' => $this->administrator,
		) );

		$this->term_id = $this->factory->term->create( array(
			'taxonomy' => 'category',
			'name' => 'test_category',
		) );
	}

	public function tearDown() {
		wp_delete_comment( $this->comment_id, true );
		wp_delete_term( $this->term_id, 'category' );

		parent::tearDown();
	}

	public function test_rest_posts_controller_exists() {
		$this->assertTrue( class_exists( 'WP_REST_Posts_Controller' ) );
	}

	public function test_read_post_has_cmb2_data() {
		wp_set_current_user( $this->subscriber );

		$expected = array(
			'cmb2' => array(
				$this->cmb_id => array(),
			),
		);
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
		$this->update_data_authorized(
			$this->administrator,
			$this->post_id,
			'/wp/v2/posts/' . $this->post_id,
			strtolower( __CLASS__ ) . '_post'
		);
	}

	public function test_read_user_fields() {
		wp_set_current_user( $this->subscriber );

		$cmb_id = strtolower( __CLASS__ ) . '_user';

		foreach ( $this->metabox_array['fields'] as $field ) {
			update_user_meta( $this->subscriber, $field['id'], md5( $field['id'] ) );
		}

		foreach ( $this->metabox_array['fields'] as $field ) {
			$expected['cmb2'][ $cmb_id ][ $field['id'] ] = md5( $field['id'] );
		}

		$url = '/wp/v2/users/' . $this->subscriber;
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertResponseData( $expected, $response );
	}

	public function test_update_user_fields() {
		$this->update_data_authorized(
			$this->subscriber,
			$this->subscriber,
			'/wp/v2/users/' . $this->subscriber,
			strtolower( __CLASS__ ) . '_user',
			'get_user_meta'
		);
	}

	public function test_read_comment_fields() {
		wp_set_current_user( $this->administrator );

		$cmb_id = strtolower( __CLASS__ ) . '_comment';

		foreach ( $this->metabox_array['fields'] as $field ) {
			update_comment_meta( $this->comment_id, $field['id'], md5( $field['id'] ) );
		}

		foreach ( $this->metabox_array['fields'] as $field ) {
			$expected['cmb2'][ $cmb_id ][ $field['id'] ] = md5( $field['id'] );
		}

		$url = '/wp/v2/comments/' . $this->comment_id;
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertResponseData( $expected, $response );
	}

	public function test_update_comment_fields() {
		$this->update_data_authorized(
			$this->administrator,
			$this->comment_id,
			'/wp/v2/comments/' . $this->comment_id,
			strtolower( __CLASS__ ) . '_comment',
			'get_comment_meta'
		);
	}

	public function test_read_term_fields() {
		wp_set_current_user( $this->subscriber );

		$cmb_id = strtolower( __CLASS__ ) . '_term';

		foreach ( $this->metabox_array['fields'] as $field ) {
			update_term_meta( $this->term_id, $field['id'], md5( $field['id'] ) );
		}

		foreach ( $this->metabox_array['fields'] as $field ) {
			$expected['cmb2'][ $cmb_id ][ $field['id'] ] = md5( $field['id'] );
		}

		$url = '/wp/v2/categories/' . $this->term_id;
		$response = rest_do_request( new WP_REST_Request( 'GET', $url ) );
		$this->assertResponseData( $expected, $response );
	}

	public function test_update_term_fields() {
		$this->update_data_authorized(
			$this->administrator,
			$this->term_id,
			'/wp/v2/categories/' . $this->term_id,
			strtolower( __CLASS__ ) . '_term',
			'get_term_meta'
		);
	}

	protected function update_data_authorized( $user, $object_id, $url, $cmb_id, $get_callback = 'get_post_meta' ) {
		wp_set_current_user( $user );

		$request = new WP_REST_Request( 'POST', $url );
		$cmb2 = array();
		foreach ( $this->metabox_array['fields'] as $field ) {
			$cmb2[ $cmb_id ][ $field['id'] ] = $field['id'];
		}

		$request['cmb2'] = $cmb2;
		$request['content'] = 'test'; // b/c comment endpoint requires wp_update_comment to pass.

		$response = rest_do_request( $request );
		$this->assertResponseStatus( 200, $response );

		foreach ( $this->metabox_array['fields'] as $field ) {
			$this->assertEquals( $field['id'], $get_callback( $object_id, $field['id'], 1 ) );
		}

		$request = new WP_REST_Request( 'POST', $url );
		$request['cmb2'] = array(
			$cmb_id => array(
				'rest_test2_registered_fields' => 'new value',
			),
		);
		$request['content'] = 'test2'; // b/c comment endpoint requires wp_update_comment to pass.

		$this->assertResponseStatus( 200, rest_do_request( $request ) );

		$this->assertEquals( 'new value', $get_callback( $object_id, 'rest_test2_registered_fields', 1 ) );
		$this->assertEquals( 'rest_test_registered_fields', $get_callback( $object_id, 'rest_test_registered_fields', 1 ) );
	}

}
