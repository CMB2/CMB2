<?php
/**
 * CMB2 tests base
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

abstract class Test_CMB2_Rest_Base extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		$this->reset_instances();

		parent::setUp();
		update_option( 'permalink_structure', '/%postname%/' );

		if ( ! did_action( 'rest_api_init' ) ) {
			do_action( 'rest_api_init', rest_get_server() );
		}
	}

	/**
	 * Set up the test fixture
	 */
	public function set_up_and_init( $metabox_array ) {
		$this->metabox_array = $metabox_array;
		$this->cmb_id = $metabox_array['id'];
		$this->rest_box = new Test_CMB2_REST_Object( new CMB2( $this->metabox_array ) );

		self::setUp();

		$this->subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$this->post_id = $this->factory->post->create();

		foreach ( $this->metabox_array['fields'] as $field ) {
			update_post_meta( $this->post_id, $field['id'], md5( $field['id'] ) );
		}
	}

	public function tearDown() {
		parent::tearDown();
		foreach ( $this->metabox_array['fields'] as $field ) {
			delete_post_meta( $this->post_id, $field['id'] );
		}
		CMB2_Boxes::remove( $this->cmb_id );
		CMB2_REST::remove( $this->cmb_id );
	}

	protected function reset_instances() {
		foreach ( CMB2_REST::$boxes as $cmb_id => $rest ) {
			$rest = new CMB2_REST( $rest->cmb );
			$rest->universal_hooks();
		}
	}

	protected function assertResponseStatuses( $url, $statuses, $debug = false ) {
		foreach ( $statuses as $method => $status ) {
			$error_code = '';

			if ( is_array( $status ) ) {
				$error_code = current( $status );
				$status = key( $status );
			}

			$this->assertRequestResponseStatus( $method, $url, $status, $error_code, $debug );
		}
	}

	protected function assertRequestResponseStatus( $method, $url, $status, $error_code = '', $debug = false ) {
		if ( $debug ) {
			error_log( $method . ' $url: '. print_r( $url, true ) );
		}

		$request = new WP_REST_Request( $method, $url );
		$this->assertResponseStatus( $status, rest_do_request( $request ), $error_code, $debug );
	}

	protected function assertResponseStatus( $status, $response, $error_code = '', $debug = false ) {
		if ( $debug ) {
			error_log( '$response->get_data(): '. print_r( $response->get_data(), true ) );
		}
		$this->assertEquals( $status, $response->get_status() );

		if ( $error_code ) {
			$this->assertResponseErrorCode( $error_code, $response );
		}
	}

	protected function assertResponseErrorCode( $error_code, $response ) {
		$response_data = $response->get_data();
		$this->assertEquals( $error_code, $response_data['code'] );
	}

	protected function assertResponseData( $data, $response ) {
		$response_data = $response->get_data();
		$tested_data = array();
		foreach( $data as $key => $value ) {
			if ( isset( $response_data[ $key ] ) ) {
				$tested_data[ $key ] = $response_data[ $key ];
			} else {
				$tested_data[ $key ] = null;
			}
		}
		$this->assertEquals( $data, $tested_data );
	}

}

if ( ! class_exists( 'Test_CMB2_REST_Object' ) ) {
	/**
	 * Simply allows access to the mb_defaults protected property (for testing)
	 */
	class Test_CMB2_REST_Object extends CMB2_REST {
		public function declare_read_edit_fields() {
			return parent::declare_read_edit_fields();
		}
		public function can_read( $show_in_rest ) {
			return parent::can_read( $show_in_rest );
		}
		public function can_edit( $show_in_rest ) {
			return parent::can_edit( $show_in_rest );
		}
		public static function get_object_data( $object ) {
			return parent::get_object_data( $object );
		}
	}
}
