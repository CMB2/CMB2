<?php
/**
 * CMB2 tests base
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
abstract class Test_CMB2 extends WP_UnitTestCase {

	public $hooks_to_die = array();

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function normalize_string( $string ) {
		return trim( preg_replace( array(
			'/[\t\n\r]/', // Remove tabs and newlines
			'/\s{2,}/', // Replace repeating spaces with one space
			'/> </', // Remove spaces between carats
			), array(
			'',
			' ',
			'><',
		), $string ) );
	}

	public function is_connected() {
		$connected = @fsockopen( 'www.youtube.com', 80 );
		if ( $connected ) {
			$is_conn = true;
			fclose( $connected );
		} else {
			$is_conn = false; //action in connection failure
		}

		return $is_conn;
	}

	public function expected_youtube_oembed_results( $args ) {
		if ( $this->is_connected() ) {
			$args['oembed_result'] = sprintf( '<iframe width="640" height="360" src="%s" frameborder="0" allowfullscreen></iframe>', $args['src'] );
			return $this->expected_oembed_success_results( $args );
		}

		return $this->no_connection_oembed_result( $args['url'] );
	}

	public function expected_oembed_success_results( $args ) {
		return sprintf( '<div class="cmb2-oembed embed-status">%s<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button" rel="%s">' . esc_html__( 'Remove Embed', 'cmb2' ) . '</a></p></div>', $args['oembed_result'], $args['field_id'] );
	}

	public function no_connection_oembed_result( $url ) {
		global $wp_embed;
		return sprintf(
			'<p class="ui-state-error-text">%s</p>',
			sprintf(
				/* translators: 1: results for. 2: link to codex.wordpress.org/Embeds */
				esc_html__( 'No oEmbed Results Found for %1$s. View more info at %2$s.', 'cmb2' ),
				$wp_embed->maybe_make_link( $url ),
				'<a href="https://codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>'
			)
		);
	}

	public function assertOEmbedResult( $args ) {
		$possibilities = array(
			$this->normalize_http_string( $this->expected_oembed_success_results( $args ) ),
			$this->normalize_http_string( $this->no_connection_oembed_result( $args['url'] ) ),
		);

		$actual = $this->normalize_http_string( cmb2_ajax()->get_oembed( $args ) );

		$results = array();
		foreach ( $possibilities as $key => $expected ) {
			$results[ $key ] = $this->compareHTMLstrings( $expected, $actual );
		}

		if ( ! empty( $results[0] ) && ! empty( $results[1] ) ) {
			// If they both failed, this will tell us.
			$this->assertEquals( $possibilities[0], $actual, $results[0] );
			$this->assertEquals( $possibilities[1], $actual, $results[1] );
		} elseif ( empty( $results[0] ) ) {
			$this->assertEquals( $possibilities[0], $actual );
		} else {
			$this->assertEquals( $possibilities[1], $actual );
		}
	}

	public function normalize_http_string( $string ) {
		return preg_replace( '~https?://~', '', $this->normalize_string( $string ) );
	}

	protected function capture_render( $cb ) {
		ob_start();
		call_user_func( $cb );
		// grab the data from the output buffer and add it to our $content variable
		return ob_get_clean();
	}

	protected function render_field( $field ) {
		return $this->capture_render( array( $field, 'render_field' ) );
	}

	protected function hook_to_wp_die( $hook ) {
		$this->hooks_to_die[] = $hook;
		add_action( $hook, array( $this, 'wp_die' ) );
	}

	public function wp_die() {
		$hook = array_pop( $this->hooks_to_die );
		remove_action( $hook, array( $this, 'wp_die' ) );
		wp_die( $hook . ' die' );
	}

	protected function compareHTMLstrings( $expected_string, $string_to_test ) {
		$compare = strcmp( $expected_string, $string_to_test );

		if ( 0 !== $compare ) {

			$compare       = strspn( $expected_string ^ $string_to_test, "\0" );
			$chars_to_show = 75;
			$start         = ( $compare - 5 );
			$pointer       = '|--->>';
			$sep           = "\n" . str_repeat( '-', 75 );

			$compare = sprintf(
			    $sep . "\nFirst difference at position %d:\n\n  Expected: \t%s\n  Actual: \t%s\n" . $sep,
			    $compare,
			    substr( $expected_string, $start, 5 ) . $pointer . substr( $expected_string, $compare, $chars_to_show ),
			    substr( $string_to_test, $start, 5 ) . $pointer . substr( $string_to_test, $compare, $chars_to_show )
			);
		}

		return $compare;
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object $object     Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod( $object, $methodName ) {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			$this->markTestSkipped( 'PHP version does not support ReflectionClass::setAccessible()' );
		}

		$args = func_get_args();
		unset( $args[0], $args[1] );

		$reflection = new ReflectionClass( get_class( $object ) );
		$method = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $args );
	}

	/**
	 * Get protected/private property of a class.
	 *
	 * @param object $object       Instantiated object that we will get property for.
	 * @param string $propertyName Property to get.
	 *
	 * @return mixed               Value of property.
	 */
	protected function getProperty( $object, $propertyName ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$property = $reflection->getProperty( $propertyName );
		$property->setAccessible( true );

		return $property->getValue( $object );
	}

	public function assertHTMLstringsAreEqual( $expected_string, $string_to_test ) {
		$expected_string = $this->normalize_string( $expected_string );
		$string_to_test = $this->normalize_string( $string_to_test );

		$compare = $this->compareHTMLstrings( $expected_string, $string_to_test );

		return $this->assertEquals( $expected_string, $string_to_test, ! empty( $compare ) ? $compare : null );
	}

	public function assertIsDefined( $definition ) {
		return $this->assertTrue( defined( $definition ), "$definition is not defined." );
	}

	/**
	 * Backport assertNotFalse to PHPUnit 3.6.12 which only runs in PHP 5.2
	 *
	 * @link   https://github.com/woothemes/woocommerce/blob/f5ff10711dc664f1c5ec5277634a5eea2b828765/tests/framework/class-wc-unit-test-case.php
	 *
	 */
	public static function assertNotFalse( $condition, $message = '' ) {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			self::assertThat( $condition, self::logicalNot( self::isFalse() ), $message );
		} else {
			parent::assertNotFalse( $condition, $message );
		}
	}

	/**
	 * Backport assertContainsOnlyInstancesOf to PHPUnit 3.6.12 which only runs in PHP 5.2
	 */
	public static function assertContainsOnlyInstancesOf( $classname, $haystack, $message = '' ) {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			foreach ( $haystack as $to_check ) {
				self::assertInstanceOf( $classname, $to_check, $message );
			}
		} else {
			parent::assertContainsOnlyInstancesOf( $classname, $haystack, $message );
		}
	}
}
