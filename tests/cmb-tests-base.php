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

	protected function oembed_success_result( $args ) {
		return sprintf( '<div class="cmb2-oembed embed-status">%s<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button" rel="%s">' . esc_html__( 'Remove Embed', 'cmb2' ) . '</a></p></div>', $args['oembed_result'], $args['field_id'] );
	}

	protected function oembed_success_result_verifiers( $args ) {
		$verifiers             = $args['oembed_result'];
		$args['oembed_result'] = 'SPLIT';
		return array_merge(
			$verifiers,
			explode( 'SPLIT', $this->oembed_success_result( $args ) )
		);
	}

	protected function oembed_no_connection_result( $url ) {
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

	protected function oembed_no_connection_result_verifiers( $url ) {
		return array( $this->oembed_no_connection_result( $url ) );
	}

	public function assertOEmbedResult( $args ) {
		$actual = $this->normalize_http_string( cmb2_ajax()->get_oembed( $args ) );

		if ( isset( $args['result_verifiers'] ) || is_array( $args['oembed_result'] ) ) {
			if ( empty( $args['result_verifiers']['connected'] ) ) {
				$args['result_verifiers']['connected'] = $this->oembed_success_result_verifiers( $args );
			}

			if ( empty( $args['result_verifiers']['no_connection'] ) ) {
				$args['result_verifiers']['no_connection'] = $this->oembed_no_connection_result_verifiers( $args['url'] );
			}

			$verifiers = $args['result_verifiers'];
			$this->assertVerifiersMatch( $verifiers, $actual );
		} else {
			$this->assertOEmbedResultString( $args, $actual );
		}
	}

	protected function assertOEmbedResultString( $args, $actual ) {
		$possibilities = array(
			$this->normalize_http_string( $this->oembed_success_result( $args ) ),
			$this->normalize_http_string( $this->oembed_no_connection_result( $args['url'] ) ),
		);

		$results = array();
		foreach ( $possibilities as $key => $expected ) {
			$results[ $key ] = $this->compare_strings( $expected, $actual );
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

	protected function assertVerifiersMatch( $result_verifiers, $actual ) {
		$failed = array();

		$possibilities = array(
			'connected' => array_map( array( $this, 'normalize_http_string' ), $result_verifiers['connected'] ),
		);

		if ( ! empty( $result_verifiers['no_connection'] ) ) {
			$possibilities['no_connection'] = array_map( array( $this, 'normalize_http_string' ), $result_verifiers['no_connection'] );
		}

		$actual = $this->normalize_http_string( $actual );

		foreach ( $possibilities as $key => $verifiers ) {
			foreach ( $verifiers as $key2 => $expected ) {
				if ( false === strpos( $actual, $expected ) ) {
					$failed[ $key ][ $key2 ] = $expected;
				}
			}
		}

		$failed_test = ! empty( $failed['connected'] );
		if ( ! empty( $result_verifiers['no_connection'] ) ) {
			$failed_test = $failed_test && ! empty( $failed['no_connection'] );
		}

		if ( $failed_test ) {
			$msg = "\nThese verifiers are missing:\n";
			foreach ( $failed as $key => $fails ) {
				foreach ( $fails as $key2 => $fail ) {
					$msg .= "\n$key - $key2:\n$fail\n";
				}
			}

			$msg .= "\nin:\n$actual\n\n";
			$this->assertTrue( false, $msg );
		} elseif ( empty( $failed['connected'] ) ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( empty( $failed['no_connection'] ) );
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

	public static function compare_strings( $orig_string, $new_string, $orig_label = 'Expected', $compare_label = 'Actual' ) {
		$orig_length = strlen( $orig_string );
		$new_length  = strlen( $new_string );
		$compare     = strcmp( $orig_string, $new_string );

		if ( 0 !== $compare ) {

			$label_spacer = str_repeat( ' ', abs( strlen( $compare_label ) - strlen( $orig_label ) ) );
			$compare_spacer = $orig_spacer = '';

			if ( strlen( $compare_label ) > strlen( $orig_label ) ) {
				$orig_spacer = $label_spacer;
			} elseif ( strlen( $compare_label ) < strlen( $orig_label ) ) {
				$compare_spacer = $label_spacer;
			}

			$compare      = strspn( $orig_string ^ $new_string, "\0" );
			$chars_before = 15;
			$chars_after  = 75;
			$start        = ( $compare - $chars_before );
			$pointer      = '| ----> |';
			$ol           = '  ' . $orig_label . ':  ' . $orig_spacer;
			$cl           = '  ' . $compare_label . ':  ' . $compare_spacer;
			$sep          = "\n" . str_repeat( '-', $chars_after + $chars_before + strlen( $pointer ) + strlen( $ol ) + 2 );

			$compare = sprintf(
				$sep . '%8$s%8$s  First difference at position %1$d.%8$s%8$s  %9$s length: %2$d, %10$s length: %3$d%8$s%8$s%4$s%5$s%8$s%6$s%7$s%8$s' . $sep,
				$compare,
				$orig_length,
				$new_length,
				$ol,
				substr( $orig_string, $start, 15 ) . $pointer . substr( $orig_string, $compare, $chars_after ),
				$cl,
				substr( $new_string, $start, 15 ) . $pointer . substr( $new_string, $compare, $chars_after ),
				"\n",
				$orig_label,
				$compare_label
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
		$reflection = new ReflectionClass( get_class( $object ) );
		$property = $reflection->getProperty( $propertyName );
		$property->setAccessible( true );

		return $property->getValue( $object );
	}

	public function assertHTMLstringsAreEqual( $expected_string, $string_to_test, $msg = null ) {
		$expected_string = $this->normalize_string( $expected_string );
		$string_to_test = $this->normalize_string( $string_to_test );

		$compare = $this->compare_strings( $expected_string, $string_to_test );

		if ( ! empty( $compare ) ) {
			$msg .= $compare;
		}

		return $this->assertEquals( $expected_string, $string_to_test, $msg );
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
