<?php

class CMB_Core_Test extends WP_UnitTestCase {

	public function test_cmb_has_version_number() {
		$this->assertNotNull( cmb_Meta_Box::CMB_VERSION );
	}

}
