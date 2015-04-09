<?php
/**
 * CMB2_Utils tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

class Test_CMB2_Utils extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create();
		$this->img_name = 'image.jpg';
		$this->attachment_id = $this->factory->attachment->create_object( $this->img_name, $this->post_id, array(
			'post_mime_type' => 'image/jpeg',
			'post_type' => 'attachment'
		) );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_image_id_from_url() {
		global $wp_version;

		$_id_value = cmb2_utils()->image_id_from_url( esc_url_raw( get_permalink( $this->attachment_id ) ) );
		if ( $wp_version > 3.9 ) {
			$this->assertEquals( $_id_value, $this->attachment_id );
		} else {
			$this->assertGreaterThan( 0, $this->attachment_id );
		}
	}

}
