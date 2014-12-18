<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Utils_Test extends CMB2_Test {

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
		$_id_value = cmb2_utils()->image_id_from_url( esc_url_raw( get_permalink( $this->attachment_id ) ) );
		$this->assertEquals( $_id_value, $this->attachment_id );
	}

}
