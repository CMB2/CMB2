<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Types_Test extends CMB2_Test {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';

		$this->metabox_array_test_attributes = array(
			'id' => $this->cmb_id,
			'fields' => array(
				array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
					'attributes' => array(
						'type'      => 'number',
						'disabled'  => 'disabled',
						'id'        => 'arbitrary-id',
						'data-test' => 'data-value',
						'data-test' => json_encode( array(
							'one'   => 'One',
							'two'   => 'Two',
							'true'  => true,
							'false' => false,
							'array' => array(
								'nested_data' => true,
							),
						) ),
					),
				),
			),
		);

		$this->cmb = new CMB2( $this->metabox_array_test_attributes );

		$this->post_id = $this->factory->post->create();
	}

	public function test_cmb2_field() {

		$field = cmb2_get_field( $this->cmb_id, 'test_test', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		ob_start();
		$field->render_field();
		// grab the data from the output buffer and add it to our $content variable
		$output = ob_get_contents();
		ob_end_clean();

		$field_gen = '
		<div class="cmb-row cmb-type-text cmb2-id-test-test">
			<div class="cmb-td">
				<label for="test_test">Name</label>
				<input type="number" class="regular-text" name="test_test" id="arbitrary-id" value="" disabled="disabled" data-test=\'{"one":"One","two":"Two","true":true,"false":false,"array":{"nested_data":true}}\'/>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $output ), $this->clean_string( $field_gen ) );
	}

}
