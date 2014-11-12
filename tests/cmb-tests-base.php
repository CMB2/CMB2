<?php

class CMB2_Test extends WP_UnitTestCase {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
	}

	public function test_cmb2_has_version_number() {
		$this->assertTrue( defined( 'CMB2_VERSION' ) );
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_cmb2_die_with_no_id() {
		$cmb = new CMB2( array() );
	}

	public function clean_string( $string ) {
		return trim( str_ireplace( array(
			"\n", "\r", "\t", '  ', '> <',
		), array(
			'', '', '', ' ', '><',
		), $string ) );
	}

}
