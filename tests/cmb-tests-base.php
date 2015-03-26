<?php

abstract class CMB2_Test extends WP_UnitTestCase {

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
		if ( $connected ){
			$is_conn = true;
			fclose( $connected );
		} else {
			$is_conn = false; //action in connection failure
		}

		return $is_conn;
	}

	protected function capture_render( $cb ) {
		ob_start();
		call_user_func( $cb );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	protected function render_field( $field ) {
		return $this->capture_render( array( $field, 'render_field' ) );
	}

	public function assertHTMLstringsAreEqual( $expected_string, $string_to_test ) {
		$expected_string = $this->normalize_string( $expected_string );
		$string_to_test = $this->normalize_string( $string_to_test );

		$compare = strcmp( $expected_string, $string_to_test );

		if ( 0 !== $compare ) {

			$compare       = strspn( $expected_string ^ $string_to_test, "\0" );
			$chars_to_show = 50;
			$start         = ( $compare - 5 );
			$pointer       = '|--->>';
			$sep           = "\n". str_repeat( '-', 75 );

			$compare = sprintf(
			    $sep . "\nFirst difference at position %d:\n\n  Expected: \t%s\n  Actual: \t%s\n" . $sep,
			    $compare,
			    substr( $expected_string, $start, 5 ) . $pointer . substr( $expected_string, $compare, $chars_to_show ),
			    substr( $string_to_test, $start, 5 ) . $pointer . substr( $string_to_test, $compare, $chars_to_show )
			);
		}

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
