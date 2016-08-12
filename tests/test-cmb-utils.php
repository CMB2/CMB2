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

	protected $test_empty = array(
		array(
			'val' => null,
			'empty' => true,
		),
		array(
			'val' => false,
			'empty' => true,
		),
		array(
			'val' => '',
			'empty' => true,
		),
		array(
			'val' => 0,
			'empty' => false,
		),
		array(
			'val' => 0.0,
			'empty' => false,
		),
		array(
			'val' => '0',
			'empty' => false,
		),
		array(
			'val' => '0.0',
			'empty' => false,
		),
		array(
			'val' => 1,
			'empty' => false,
		),
		array(
			'val' => ' ',
			'empty' => false,
		),
		array(
			'val' => "\n",
			'empty' => false,
		),
		array(
			'val' => '&nbsp;',
			'empty' => false,
		),
	);

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

	public function test_get_url_from_dir() {
		$this->assertEquals(
			trailingslashit( site_url() ),
			CMB2_Utils::get_url_from_dir( ABSPATH )
		);

		foreach ( array(
			'cmb2',
			'wp-content/cmb2',
			'vendor/cmb2/',
			'wp-content/themes/cmb2/',
			'wp-content/themes/twentysixteen/cmb2/',
			'wp-content/plugins/cmb2/',
			'wp-content/plugins/some-plugin/cmb2/',
			'wp-content/mu-plugins/cmb2/',
			'wp-content/mu-plugins/some-mu-plugin/cmb2/',
		) as $located ) {
			$this->assertEquals(
				site_url( $located ),
				CMB2_Utils::get_url_from_dir( ABSPATH . $located )
			);

			add_filter( 'theme_root', array( 'CMB2_Utils_WIN', '_change_to_wamp_theme_root' ) );
			$this->assertEquals(
				site_url( $located ),
				CMB2_Utils_WIN::get_url_from_dir( ABSPATH . $located )
			);
			remove_filter( 'theme_root', array( 'CMB2_Utils_WIN', '_change_to_wamp_theme_root' ) );
		}

	}

	public function test_isempty() {
		foreach ( $this->test_empty as $test ) {
			$this->assertEquals( $test['empty'], cmb2_utils()->isempty( $test['val'] ) );
		}
	}

	public function test_notempty() {
		foreach ( $this->test_empty as $test ) {
			$this->assertEquals( ! $test['empty'], cmb2_utils()->notempty( $test['val'] ) );
		}
	}

	public function test_filter_empty() {
		$vals = wp_list_pluck( $this->test_empty, 'val' );

		$non_empties = array(
			3 => 0,
			4 => 0.0,
			5 => '0',
			6 => '0.0',
			7 => 1,
			8 => ' ',
			9 => "\n",
			10 => '&nbsp;',
		);

		$this->assertEquals( $non_empties, cmb2_utils()->filter_empty( $vals ) );
	}

}

class CMB2_Utils_WIN extends CMB2_Utils {
	public static $ABSPATH = 'C:\xampp\htdocs\the-site-dir';

	public static function _change_to_wamp_theme_root() {
		return self::$ABSPATH . '/wp-content/themes/';
	}
}
