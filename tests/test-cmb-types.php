<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Types_Test extends CMB2_Test {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';

		$this->field_test = array(
			'id' => 'field_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'desc' => 'This is a description',
					'id'   => 'field_test_field',
					'type' => 'text',
				),
			),
		);

		$this->attributes_test = array(
			'id' => 'attributes_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'desc' => 'This is a description',
					'id'   => 'attributes_test_field',
					'type' => 'text',
					'attributes' => array(
						'type'      => 'number',
						'disabled'  => 'disabled',
						'id'        => 'arbitrary-id',
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

		$this->options_test = array(
			'id' => 'options_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_test_field',
					'type' => 'select',
					'options' => array(
						'one'   => 'One',
						'two'   => 'Two',
						'true'  => true,
						'false' => false,
					),
				),
			),
		);

		$this->options_cb_test = array(
			'id' => 'options_cb_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_cb_test_field',
					'type' => 'select',
					'options_cb' => array( $this, 'options_cb' ),
				),
			),
		);

		$this->options_cb_and_array_test = array(
			'id' => 'options_cb_and_array_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_cb_and_array_test_field',
					'type' => 'select',
					'options' => array(
						'one'   => 'One',
						'two'   => 'Two',
						'true'  => true,
						'false' => false,
					),
					'options_cb' => array( $this, 'options_cb' ),
				),
			),
		);

		$this->post_id = $this->factory->post->create();
	}

	public function test_wysiwyg_field() {
		global $wp_version;

		$this->field_test['fields'][0]['type'] = 'wysiwyg';
		$cmb   = new CMB2( $this->field_test );
		$field = cmb2_get_field( $this->field_test['id'], 'field_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version = 'ver=' . $wp_version;

		$field_gen = '
		<div class="cmb-row cmb-type-wysiwyg cmb2-id-field-test-field">
			<div class="cmb-td">
				<label for="field_test_field">Name</label>
				<div id="wp-field_test_field-wrap" class="wp-core-ui wp-editor-wrap html-active">
					<link rel=\'stylesheet\' id=\'dashicons-css\' href=\'' . includes_url( "css/dashicons$suffix.css?$version" ) . '\' type=\'text/css\' media=\'all\' />
					<link rel=\'stylesheet\' id=\'editor-buttons-css\' href=\'' . includes_url( "css/editor$suffix.css?$version" ) . '\' type=\'text/css\' media=\'all\' />
					<div id="wp-field_test_field-editor-container" class="wp-editor-container">
						<textarea class="wp-editor-area" rows="20" cols="40" name="field_test_field" id="field_test_field">
						</textarea>
					</div>
				</div>
				<p class="cmb2-metabox-description">This is a description</p>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $this->render_field( $field ) ), $this->clean_string( $field_gen ) );
	}

	public function test_repeatable_field() {
		$this->field_test['fields'][0]['repeatable'] = true;
		$this->field_test['fields'][0]['options'] = array(
			'add_row_text' => 'ADD NEW ROW',
		);
		$cmb   = new CMB2( $this->field_test );
		$field = cmb2_get_field( $this->field_test['id'], 'field_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$field_gen = '
		<div class="cmb-row cmb-type-text cmb2-id-field-test-field cmb-repeat">
			<div class="cmb-td">
				<label for="field_test_field">Name</label>
				<p class="cmb2-metabox-description">This is a description</p>
				<div id="field_test_field_repeat" class="cmb-repeat-table cmb-nested">
					<div class="cmb-tbody cmb-field-list">
						<div class="cmb-row cmb-repeat-row">
							<div class="cmb-td">
								<input type="text" class="regular-text" name="field_test_field[0]" id="field_test_field_0" data-iterator="0" value=""/>
							</div>
							<div class="cmb-td cmb-remove-row">
								<a class="button cmb-remove-row-button button-disabled" href="#">'. __( 'Remove', 'cmb2' ) .'</a>
							</div>
						</div>
						<div class="cmb-row empty-row hidden">
							<div class="cmb-td">
								<input type="text" class="regular-text" name="field_test_field[1]" id="field_test_field_1" data-iterator="1" value=""/>
							</div>
							<div class="cmb-td cmb-remove-row">
								<a class="button cmb-remove-row-button" href="#">'. __( 'Remove', 'cmb2' ) .'</a>
							</div>
						</div>
					</div>
				</div>
				<p class="cmb-add-row">
					<a data-selector="field_test_field_repeat" class="cmb-add-row-button button" href="#">ADD NEW ROW</a>
				</p>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $this->render_field( $field ) ), $this->clean_string( $field_gen ) );
	}

	public function test_field_options_cb() {
		$cmb   = new CMB2( $this->options_cb_test );
		$field = cmb2_get_field( $this->options_cb_test['id'], 'options_cb_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$field_gen = '
		<div class="cmb-row cmb-type-select cmb2-id-options-cb-test-field">
			<div class="cmb-td">
				<label for="options_cb_test_field">Name</label>
				<select class="cmb2_select" name="options_cb_test_field" id="options_cb_test_field">
					<option value="one" >One</option>
					<option value="two" >Two</option>
					<option value="true" >1</option>
					<option value="false" ></option>
					<option value="post_id" >'. $this->post_id .'</option>
					<option value="object_type" >post</option>
					<option value="type" >select</option>
				</select>
				<p class="cmb2-metabox-description">This is a description</p>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $this->render_field( $field ) ), $this->clean_string( $field_gen ) );
	}

	public function test_field_options() {
		$cmb   = new CMB2( $this->options_test );
		$field = cmb2_get_field( $this->options_test['id'], 'options_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$field_gen = '
		<div class="cmb-row cmb-type-select cmb2-id-options-test-field">
			<div class="cmb-td">
				<label for="options_test_field">Name</label>
				<select class="cmb2_select" name="options_test_field" id="options_test_field">
					<option value="one" >One</option>
					<option value="two" >Two</option>
					<option value="true" >1</option>
					<option value="false" >
					</option>
				</select>
				<p class="cmb2-metabox-description">This is a description</p>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $this->render_field( $field ) ), $this->clean_string( $field_gen ) );
	}

	public function test_field_attributes() {
		$cmb   = new CMB2( $this->attributes_test );
		$field = cmb2_get_field( $this->attributes_test['id'], 'attributes_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$field_gen = '
		<div class="cmb-row cmb-type-text cmb2-id-attributes-test-field">
			<div class="cmb-td">
				<label for="attributes_test_field">Name</label>
				<input type="number" class="regular-text" name="attributes_test_field" id="arbitrary-id" value="" disabled="disabled" data-test=\'{"one":"One","two":"Two","true":true,"false":false,"array":{"nested_data":true}}\'/>
				<p class="cmb2-metabox-description">This is a description</p>
			</div>
		</div>
		';

		$this->assertEquals( $this->clean_string( $this->render_field( $field ) ), $this->clean_string( $field_gen ) );
	}

	public function options_cb( $field ) {
		return array(
			'one'         => 'One',
			'two'         => 'Two',
			'true'        => true,
			'false'       => false,
			'post_id'     => $field->object_id,
			'object_type' => $field->object_type,
			'type'        => $field->args( 'type' ),
		);
	}

	public function render_field( $field ) {
		ob_start();
		$field->render_field();
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

}
