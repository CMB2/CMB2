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
		$this->img_name = 'test-image.jpg';

		$filename = ( CMB2_TESTDATA.'/images/test-image.jpg' );
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( basename( $filename ), null, $contents );

		$this->attachment_id = $this->_make_attachment( $upload, $this->post_id );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_image_id_from_url() {
		global $wp_version;

		$_id_value = cmb2_utils()->image_id_from_url( esc_url_raw( wp_get_attachment_url( $this->attachment_id ) ) );
		if ( $wp_version > 3.9 ) {
			$this->assertEquals( $_id_value, $this->attachment_id );
		} else {
			$this->assertGreaterThan( 0, $this->attachment_id );
		}
	}

	function _make_attachment( $upload, $parent_post_id = -1 ) {

		$type = '';
		if ( !empty($upload['type']) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ($mime)
				$type = $mime['type'];
		}

		$attachment = array(
			'post_title' => basename( $upload['file'] ),
			'post_content' => '',
			'post_type' => 'attachment',
			'post_parent' => $parent_post_id,
			'post_mime_type' => $type,
			'guid' => $upload[ 'url' ],
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload[ 'file' ], $parent_post_id );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

}
