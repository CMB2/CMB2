<?php

class CMB2_Core_Test extends WP_UnitTestCase {

	public function test_cmb2_has_version_number() {
		$this->assertTrue( defined( 'CMB2_VERSION' ) );
	}

}
