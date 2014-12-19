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
			'before_field' => array( $this, 'before_field_cb' ),
			'after_field' => 'after_field_static',
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

	public function tearDown() {
		parent::tearDown();
	}

	public function test_cmb2_field_instance() {
		$this->assertInstanceOf( 'CMB2_Field', $this->field  );
	}

	public function test_cmb2_before_and_after_field_callbacks() {
		ob_start();
		$this->field->peform_param_callback( 'before_field' );
		$this->field->peform_param_callback( 'after_field' );
		// grab the data from the output buffer and add it to our $content variable
		$content = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( 'before_field_cb_test_testafter_field_static', $content );
	}

	public function before_field_cb( $args ) {
		echo 'before_field_cb_'. $args['id'];
	}

}
