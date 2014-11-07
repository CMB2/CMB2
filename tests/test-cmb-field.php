<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Field_Test extends CMB2_Test {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create();

		$this->field_args = array(
			'name' => 'Name',
			'id'   => 'test_test',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'disabled' => 'disabled',
				'data-test' => 'data-value',
				'data-test' => json_encode( array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
				) ),
			),
		);

		$this->object_id   = $this->post_id;
		$this->object_type = 'post';
		$this->group       = false;

		$this->field = new CMB2_Field( array(
			'object_id' => $this->object_id,
			'object_type' => $this->object_type,
			'group' => $this->group,
			'field_args' => $this->field_args,
		) );

	}

	public function test_cmb2_field_instance() {
		$this->assertInstanceOf( 'CMB2_Field', $this->field  );
	}

}
