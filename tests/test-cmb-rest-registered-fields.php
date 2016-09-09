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
		parent::setUp();

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

		$this->rest_box = new Test_CMB2_REST_Object( $this->cmb );
		$this->rest_box->universal_hooks();

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

	public function test_rest_posts_controller_exists() {
		$this->assertTrue( class_exists( 'WP_REST_Posts_Controller' ) );
	}

}
