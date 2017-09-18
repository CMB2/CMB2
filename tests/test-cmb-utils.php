<?php
/**
 * CMB2_Utils tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
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
		array(
			'val' => array(),
			'empty' => true,
		),
		array(
			'val' => array( 0 ),
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

		$filename = ( CMB2_TESTDATA . '/images/test-image.jpg' );
		$contents = file_get_contents( $filename );
		$upload   = wp_upload_bits( basename( $filename ), null, $contents );

		$this->attachment_id = $this->_make_attachment( $upload, $this->post_id );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_image_id_from_url() {

		$_id_value = CMB2_Utils::image_id_from_url( esc_url_raw( wp_get_attachment_url( $this->attachment_id ) ) );
		if ( get_bloginfo( 'version' ) > 3.9 ) {
			$this->assertEquals( $_id_value, $this->attachment_id );
		} else {
			$this->assertGreaterThan( 0, $this->attachment_id );
		}
	}

	function _make_attachment( $upload, $parent_post_id = -1 ) {

		$type = '';
		if ( ! empty( $upload['type'] ) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ( $mime ) {
				$type = $mime['type'];
			}
		}

		$attachment = array(
			'post_title' => basename( $upload['file'] ),
			'post_content' => '',
			'post_type' => 'attachment',
			'post_parent' => $parent_post_id,
			'post_mime_type' => $type,
			'guid' => $upload['url'],
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload['file'], $parent_post_id );
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

			add_filter( 'theme_root', array( 'Test_CMB2_Utils_WIN', '_change_to_wamp_theme_root' ) );

			$this->assertEquals(
				site_url( $located ),
				Test_CMB2_Utils_WIN::get_url_from_dir( ABSPATH . $located )
			);
			remove_filter( 'theme_root', array( 'Test_CMB2_Utils_WIN', '_change_to_wamp_theme_root' ) );
		}

	}

	public function test_isempty() {
		foreach ( $this->test_empty as $test ) {
			$this->assertEquals( $test['empty'], CMB2_Utils::isempty( $test['val'] ) );
		}
	}

	public function test_notempty() {
		foreach ( $this->test_empty as $test ) {
			$this->assertEquals( ! $test['empty'], CMB2_Utils::notempty( $test['val'] ) );
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
			12 => array( 0 ),
		);

		$this->assertEquals( $non_empties, CMB2_Utils::filter_empty( $vals ) );
	}

	public function test_concat_attrs() {
		$data = array(
			'att3'  => 'att3 value',
			'att5'  => 'att5 value',
			'att7'  => 'att7 value',
			'att10' => 'att10 value',
		);

		$attributes = array(
			'rendered'  => 'rendered',
			'false'     => false,
			'value'     => false,
			'att3'      => 'att3 value',
			'att5'      => 'att5 value',
			'att7'      => 'att7 value',
			'att10'     => 'att10 value',
			'data-blah' => function_exists( 'wp_json_encode' ) ? wp_json_encode( $data ) : json_encode( $data ),
		);

		$to_exclude = array(
			'att5',
			'att7',
		);

		$this->assertHTMLstringsAreEqual(
			'value="" att3="att3 value" att10="att10 value" data-blah=\'{"att3":"att3 value","att5":"att5 value","att7":"att7 value","att10":"att10 value"}\'',
			CMB2_Utils::concat_attrs( $attributes, $to_exclude )
		);
	}

	public function test_ensure_array() {
		$this->assertEquals( array( 'test' ), CMB2_Utils::ensure_array( '', array( 'test' ) ) );
		$this->assertEquals( array( 'test' ), CMB2_Utils::ensure_array( false, array( 'test' ) ) );
		$this->assertEquals( array( 'test' ), CMB2_Utils::ensure_array( 0, array( 'test' ) ) );

		$this->assertEquals( array( 'test' ), CMB2_Utils::ensure_array( 'test' ) );
		$this->assertEquals( array( 'test' ), CMB2_Utils::ensure_array( array( 'test' ) ) );

		$this->assertEquals( array(
			'errors' => array(),
			'error_data' => array(),
		), CMB2_Utils::ensure_array( new WP_Error ) );
	}

	public function test_url_set() {
		$cmb2_url = str_replace(
			array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
			array( WP_CONTENT_URL, WP_PLUGIN_URL ),
			cmb2_dir()
		);

		$this->assertEquals( CMB2_Utils::url(), $cmb2_url );
	}

	public function test_array_insert() {
		$array = array(
			'one' => array( 1,2,3 ),
			'two' => array( 1,2,3 ),
			'three' => array( 1,2,3 ),
		);

		$new = array(
			'new' => array( 4, 5, 6 ),
		);

		CMB2_Utils::array_insert( $array, $new, 2 );

		$this->assertEquals( array(
			'one' => array( 1,2,3 ),
			'new' => array( 4,5,6 ),
			'two' => array( 1,2,3 ),
			'three' => array( 1,2,3 ),
		), $array );
	}

	public function test_normalize_if_numeric() {
		$tests = array(
			array( '0.1', 0.10000000000000001 ),
			array( 0.1, 0.1 ),
			array( .1, .1 ),
			array( 1, 1 ),
			array( '1', 1 ),
			array( 'one', 'one' ),
			array( '0.0', 0.0 ),
			array( 0, 0 ),
			array( '', '' ),
		);
		foreach ( $tests as $index => $test ) {
			$this->assertSame(
				$test[1],
				CMB2_Utils::normalize_if_numeric( $test[0] ),
				"Test index: $index"
			);
		}

	}

}

class Test_CMB2_Utils_WIN extends CMB2_Utils {
	protected static function get_normalized_abspath() {
		return self::normalize_path( 'C:\xampp\htdocs\the-site-dir' );
	}

	public static function _change_to_wamp_theme_root() {
		return self::get_normalized_abspath() . '/wp-content/themes/';
	}
}
