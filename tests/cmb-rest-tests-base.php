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
		parent::setUp();
		update_option( 'permalink_structure', '/%postname%/' );
		rest_get_server();
	}

	public function tearDown() {
		parent::tearDown();
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

		/**
		 * Array of readable field objects.
		 * @var   CMB2_Field[]
		 * @since 2.2.4
		 */
		public $read_fields = array();

		/**
		 * Array of editable field objects.
		 * @var   CMB2_Field[]
		 * @since 2.2.4
		 */
		public $edit_fields = array();

		/**
		 * whether CMB2 object is readable via the rest api.
		 * @var boolean
		 */
		public $rest_read = false;

		/**
		 * whether CMB2 object is editable via the rest api.
		 * @var boolean
		 */
		public $rest_edit = false;

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
