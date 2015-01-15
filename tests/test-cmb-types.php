<?php

require_once( 'cmb-tests-base.php' );

class CMB2_Types_Test extends CMB2_Test {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';

		$this->text_type_field = array(
			'name' => 'Name',
			'desc' => 'This is a description',
			'id'   => 'field_test_field',
			'type' => 'text',
		);

		$this->field_test = array(
			'id' => 'field_test',
			'fields' => array(
				$this->text_type_field,
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
		$this->term = $this->factory->term->create( array( 'taxonomy' => 'category', 'name' => 'test_category' ) );
		$this->term2 = $this->factory->term->create( array( 'taxonomy' => 'category', 'name' => 'number_2' ) );

		wp_set_object_terms( $this->post_id, 'test_category', 'category' );

		$this->img_name = 'image.jpg';
		$this->attachment_id = $this->factory->attachment->create_object( $this->img_name, $this->post_id, array(
			'post_mime_type' => 'image/jpeg',
			'post_type' => 'attachment'
		) );
		$this->attachment_id2 = $this->factory->attachment->create_object( '2nd-'.$this->img_name, $this->post_id, array(
			'post_mime_type' => 'image/jpeg',
			'post_type' => 'attachment'
		) );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_repeatable_field() {
		$this->field_test['fields'][0]['repeatable'] = true;
		$this->field_test['fields'][0]['options'] = array(
			'add_row_text' => 'ADD NEW ROW',
		);
		$cmb   = new CMB2( $this->field_test );
		$field = cmb2_get_field( $this->field_test['id'], 'field_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$expected_field = '
		<div class="cmb-row cmb-type-text cmb2-id-field-test-field cmb-repeat table-layout">
			<div class="cmb-th"><label for="field_test_field">Name</label></div>
			<div class="cmb-td">
				<p class="cmb2-metabox-description">This is a description</p>
				<div id="field_test_field_repeat" class="cmb-repeat-table cmb-nested">
					<div class="cmb-tbody cmb-field-list">
						<div class="cmb-row cmb-repeat-row">
							<div class="cmb-td">
								<input type="text" class="regular-text" name="field_test_field[0]" id="field_test_field_0" data-iterator="0" value=""/>
							</div>
							<div class="cmb-td cmb-remove-row">
								<button class="button cmb-remove-row-button button-disabled">'. __( 'Remove', 'cmb2' ) .'</button>
							</div>
						</div>
						<div class="cmb-row empty-row hidden">
							<div class="cmb-td">
								<input type="text" class="regular-text" name="field_test_field[1]" id="field_test_field_1" data-iterator="1" value=""/>
							</div>
							<div class="cmb-td cmb-remove-row">
								<button class="button cmb-remove-row-button">'. __( 'Remove', 'cmb2' ) .'</button>
							</div>
						</div>
					</div>
				</div>
				<p class="cmb-add-row">
					<button data-selector="field_test_field_repeat" class="cmb-add-row-button button">ADD NEW ROW</button>
				</p>
			</div>
		</div>
		';

		$this->assertHTMLstringsAreEqual( $expected_field, $this->render_field( $field ) );
	}

	public function test_field_options_cb() {
		$cmb   = new CMB2( $this->options_cb_test );
		$field = cmb2_get_field( $this->options_cb_test['id'], 'options_cb_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$expected_field = '
		<div class="cmb-row cmb-type-select cmb2-id-options-cb-test-field">
			<div class="cmb-th"><label for="options_cb_test_field">Name</label></div>
			<div class="cmb-td">
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

		$this->assertHTMLstringsAreEqual( $expected_field, $this->render_field( $field ) );
	}

	public function test_field_options() {
		$cmb   = new CMB2( $this->options_test );
		$field = cmb2_get_field( $this->options_test['id'], 'options_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$expected_field = '
		<div class="cmb-row cmb-type-select cmb2-id-options-test-field">
			<div class="cmb-th"><label for="options_test_field">Name</label></div>
			<div class="cmb-td">
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

		$this->assertHTMLstringsAreEqual( $expected_field, $this->render_field( $field ) );
	}

	public function test_field_attributes() {
		$cmb   = new CMB2( $this->attributes_test );
		$field = cmb2_get_field( $this->attributes_test['id'], 'attributes_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$expected_field = '
		<div class="cmb-row cmb-type-text cmb2-id-attributes-test-field table-layout">
			<div class="cmb-th"><label for="attributes_test_field">Name</label></div>
			<div class="cmb-td">
				<input type="number" class="regular-text" name="attributes_test_field" id="arbitrary-id" value="" disabled="disabled" data-test=\'{"one":"One","two":"Two","true":true,"false":false,"array":{"nested_data":true}}\'/>
				<p class="cmb2-metabox-description">This is a description</p>
			</div>
		</div>
		';

		$this->assertHTMLstringsAreEqual( $expected_field, $this->render_field( $field ) );
	}

	public function test_get_file_ext() {
		$type = $this->get_field_type_object( 'file' );
		$ext = $type->get_file_ext( site_url( '/wp-content/uploads/2014/12/test-file.pdf' ) );
		$this->assertEquals( 'pdf', $ext );
	}

	public function test_get_file_name_from_path() {
		$type = $this->get_field_type_object( 'file' );
		$name = $type->get_file_name_from_path( site_url( '/wp-content/uploads/2014/12/test-file.pdf' ) );
		$this->assertEquals( 'test-file.pdf', $name );
	}

	public function test_is_valid_img_ext() {
		$type = $this->get_field_type_object( 'file' );
		$this->assertFalse( $type->is_valid_img_ext( $type->get_file_ext( site_url( '/wp-content/uploads/2014/12/test-file.pdf' ) ) ) );
		$this->assertFalse( $type->is_valid_img_ext( '.pdf' ) );
		$this->assertFalse( $type->is_valid_img_ext( 'jpg' ) );
		$this->assertFalse( $type->is_valid_img_ext( '.test' ) );

		$valid_types = apply_filters( 'cmb2_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) );

		foreach ( $valid_types as $ext ) {
			$this->assertTrue( $type->is_valid_img_ext( '/test.'. $ext ) );
		}

		// Add .test as a valid image type
		add_filter( 'cmb2_valid_img_types', array( $this, 'add_type_cb' ) );
		$this->assertTrue( $type->is_valid_img_ext( '/test.test' ) );
	}

	public function test_text_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="regular-text" name="field_test_field" id="field_test_field" value=""/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object(), 'render' ) )
		);
	}

	public function test_text_field_after_value_update() {

		update_post_meta( $this->post_id, $this->text_type_field['id'], 'test value' );

		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="regular-text" name="field_test_field" id="field_test_field" value="test value"/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object(), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_hidden_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="hidden" class="" name="field_test_field" id="field_test_field" value=""/>',
			$this->capture_render( array( $this->get_field_type_object( 'hidden' ), 'render' ) )
		);
	}

	public function test_text_medium_field() {

		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-text-medium" name="field_test_field" id="field_test_field" value=""/><span class="cmb2-metabox-description">This is a description</span>',
			$this->capture_render( array( $this->get_field_type_object( 'text_medium' ), 'render' ) )
		);
	}

	public function test_text_email_field() {

		$this->assertHTMLstringsAreEqual(
			'<input type="email" class="cmb2-text-email cmb2-text-medium" name="field_test_field" id="field_test_field" value=""/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'text_email' ), 'render' ) )
		);
	}

	public function test_text_url_field() {

		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-text-url cmb2-text-medium regular-text" name="field_test_field" id="field_test_field" value=""/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'text_url' ), 'render' ) )
		);
	}

	public function test_text_url_after_value_update() {

		update_post_meta( $this->post_id, $this->text_type_field['id'], 'test value' );

		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-text-url cmb2-text-medium regular-text" name="field_test_field" id="field_test_field" value="http://testvalue"/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'text_url' ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_date_field_after_value_update() {

		update_post_meta( $this->post_id, $this->text_type_field['id'], 'today' );

		$field = $this->get_field_object( 'text_date' );
		$type = $this->get_field_type_object( $field );

		// Check that date format is set to the default (since we didn't set it)
		$this->assertEquals( 'm\/d\/Y', $field->args( 'date_format' ) );

		$value = $field->format_timestamp( strtotime( 'today' ) );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field" id="field_test_field" value="%s"/><span class="cmb2-metabox-description">This is a description</span>', $value ),
			$this->capture_render( array( $type, 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_time_field_after_value_update() {

		update_post_meta( $this->post_id, $this->text_type_field['id'], 'today' );

		$field = $this->get_field_object( 'text_time' );
		$type = $this->get_field_type_object( $field );

		// Check that time format is set to the default (since we didn't set it)
		$this->assertEquals( 'h:i A', $field->args( 'time_format' ) );

		$value = $field->format_timestamp( strtotime( 'today' ), 'time_format' );


		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-timepicker text-time" name="field_test_field" id="field_test_field" value="%s"/><span class="cmb2-metabox-description">This is a description</span>', $value ),
			$this->capture_render( array( $type, 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_money_field() {

		$expected_field = '$ <input type="text" class="cmb2-text-money" name="field_test_field" id="field_test_field" value=""/><span class="cmb2-metabox-description">This is a description</span>';

		$this->assertHTMLstringsAreEqual(
			$expected_field,
			$this->capture_render( array( $this->get_field_type_object( 'text_money' ), 'render' ) )
		);

		/**
		 * Create a new field type object,
		 * but use a British pound symbol for the prefix
		 */

		// replace $ w/ £
		$expected_field = substr_replace( $expected_field, '£', 0, 1 );

		$type = $this->get_field_type_object( array(
			'type'         => 'text_money',
			'before_field' => '£',
		) );

		$this->assertHTMLstringsAreEqual(
			$expected_field,
			$this->capture_render( array( $type, 'render' ) )
		);

		/**
		 * Create a new field type object,
		 * but use a callback to produce the British pound symbol
		 */

		// update expected
		$expected_field = str_replace( '£', '£ text_money', $expected_field );

		$type = $this->get_field_type_object( array(
			'type'         => 'text_money',
			'before_field' => array( $this, 'change_money_cb' ),
		) );

		$this->assertHTMLstringsAreEqual(
			$expected_field,
			$this->capture_render( array( $type, 'render' ) )
		);

		$this->assertEquals( '£ text_money', $type->field->get_param_callback_result( 'before_field' ) );
	}

	public function test_textarea_small_field() {
		$this->assertHTMLstringsAreEqual(
			'<textarea class="cmb2-textarea-small" name="field_test_field" id="field_test_field" cols="60" rows="4"></textarea><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'textarea_small' ), 'render' ) )
		);
	}

	public function test_textarea_code_field() {
		$this->assertHTMLstringsAreEqual(
			'<pre><textarea class="cmb2-textarea-code" name="field_test_field" id="field_test_field" cols="60" rows="10"></textarea></pre><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'textarea_code' ), 'render' ) )
		);
	}

	public function test_wysiwyg_field() {
		global $wp_version;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$version = 'ver=' . $wp_version;

		$field = $this->get_field_object( 'wysiwyg' );
		$type = $this->get_field_type_object( $field );

		$this->assertHTMLstringsAreEqual(
			'
			<div id="wp-field_test_field-wrap" class="wp-core-ui wp-editor-wrap html-active">
				<link rel=\'stylesheet\' id=\'dashicons-css\' href=\'' . includes_url( "css/dashicons$suffix.css?$version" ) . '\' type=\'text/css\' media=\'all\' />
				<link rel=\'stylesheet\' id=\'editor-buttons-css\' href=\'' . includes_url( "css/editor$suffix.css?$version" ) . '\' type=\'text/css\' media=\'all\' />
				<div id="wp-field_test_field-editor-container" class="wp-editor-container">
					<textarea class="wp-editor-area" rows="20" cols="40" name="field_test_field" id="field_test_field">
					</textarea>
				</div>
			</div>
			<p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $type, 'render' ) )
		);
	}

	public function test_text_date_timestamp_field_after_value_update() {

		$val_to_update = strtotime( 'today' );

		update_post_meta( $this->post_id, $this->text_type_field['id'], $val_to_update );

		$get_val = get_post_meta( $this->post_id, $this->text_type_field['id'], 1 );

		$field = $this->get_field_object( 'text_date_timestamp' );

		$this->assertEquals( $val_to_update, $get_val );
		$this->assertEquals( $val_to_update, $field->escaped_value() );

		$formatted_val_to_update = $field->format_timestamp( $val_to_update );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field" id="field_test_field" value="%s"/><p class="cmb2-metabox-description">This is a description</p>', $formatted_val_to_update ),
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_datetime_timestamp_field_after_value_update() {

		update_post_meta( $this->post_id, $this->text_type_field['id'], strtotime( 'today' ) );
		$today_stamp = strtotime( 'today' );

		$field = $this->get_field_object( 'text_datetime_timestamp' );

		// Check that date format is set to the default (since we didn't set it)
		$this->assertEquals( 'm\/d\/Y', $field->args( 'date_format' ) );
		$this->assertEquals( 'h:i A', $field->args( 'time_format' ) );

		$date_val = $field->format_timestamp( $today_stamp );
		$time_val = $field->format_timestamp( $today_stamp, 'time_format' );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field[date]" id="field_test_field_date" value="%s"/><input type="text" class="cmb2-timepicker text-time" name="field_test_field[time]" id="field_test_field_time" value="%s"/><span class="cmb2-metabox-description">This is a description</span>', $date_val, $time_val ),
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_datetime_timestamp_timezone_field_after_value_update() {

		$tzstring = cmb2_utils()->timezone_string();
		$offset = cmb2_utils()->timezone_offset( $tzstring, true );
		if ( substr( $tzstring, 0, 3 ) === 'UTC' ) {
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );
		}
		$today_stamp = strtotime( 'today' );

		$field = $this->get_field_object( 'text_datetime_timestamp_timezone' );
		$date_val = $field->format_timestamp( $today_stamp );
		$time_val = $field->format_timestamp( $today_stamp, 'time_format' );

		$value_to_save = new DateTime( $date_val .' '. $time_val, new DateTimeZone( $tzstring ) );
		$value_to_save = serialize( $value_to_save );


		update_post_meta( $this->post_id, $this->text_type_field['id'], $value_to_save );

		$get_val = get_post_meta( $this->post_id, $this->text_type_field['id'], 1 );
		$this->assertEquals( $value_to_save, $get_val );

		$zones = wp_timezone_choice( $tzstring );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field[date]" id="field_test_field_date" value="%s"/><input type="text" class="cmb2-timepicker text-time" name="field_test_field[time]" id="field_test_field_time" value="%s"/><select name="field_test_field[timezone]" id="field_test_field_timezone">%s</select><span class="cmb2-metabox-description">This is a description</span>', $date_val, $time_val, $zones ),
			$this->capture_render( array( $this->get_field_type_object( 'text_datetime_timestamp_timezone' ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_select_timezone_field_after_value_update() {
		$value_to_save = cmb2_utils()->timezone_string();
		update_post_meta( $this->post_id, $this->text_type_field['id'], $value_to_save );
		$zones = wp_timezone_choice( $value_to_save );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<select name="field_test_field" id="field_test_field">%s</select><span class="cmb2-metabox-description">This is a description</span>', $zones ),
			$this->capture_render( array( $this->get_field_type_object( 'select_timezone' ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_colorpicker_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-colorpicker cmb2-text-small" name="field_test_field" id="field_test_field" value="#"/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'colorpicker' ), 'render' ) )
		);
	}

	public function test_colorpicker_field_default() {
		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-colorpicker cmb2-text-small" name="field_test_field" id="field_test_field" value="#bada55"/><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'colorpicker', 'default' => '#bada55' ) ), 'render' ) )
		);
	}

	public function test_title_field() {
		$this->assertHTMLstringsAreEqual(
			'<h5 class="cmb2-metabox-title">Name</h5><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( 'title' ), 'render' ) )
		);
	}

	public function test_select_field() {
		$field = $this->get_field_object( $this->options_test['fields'][0] );
		$this->assertHTMLstringsAreEqual(
			'<select class="cmb2_select" name="options_test_field" id="options_test_field"><option value="one" >One</option><option value="two" >Two</option><option value="true" >1</option><option value="false" ></option></select><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);
	}

	public function test_select_field_after_value_update() {
 		update_post_meta( $this->post_id, $this->options_test['fields'][0]['id'], 'one' );

		$field = $this->get_field_object( $this->options_test['fields'][0] );
		$this->assertHTMLstringsAreEqual(
			'<select class="cmb2_select" name="options_test_field" id="options_test_field"><option value="one" selected=\'selected\'>One</option><option value="two" >Two</option><option value="true" >1</option><option value="false" ></option></select><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_taxonomy_select_field() {

		$args = $this->options_test['fields'][0];
		$args['type'] = 'taxonomy_select';
		$args['taxonomy'] = 'category';
		$field = $this->get_field_object( $args );

		$this->assertHTMLstringsAreEqual(
			'<select class="cmb2_select" name="options_test_field" id="options_test_field"><option value="" >None</option><option value="number_2" >number_2</option><option value="test_category" selected=\'selected\'>test_category</option><option value="uncategorized" >Uncategorized</option></select><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);
	}

	public function test_radio_field() {
		$args = $this->options_test['fields'][0];
		$args['type'] = 'radio';
		$field = $this->get_field_object( $args );
		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-radio-list cmb2-list"><li><input type="radio" class="cmb2-option" name="options_test_field" id="options_test_field1" value="one"/><label for="options_test_field1">One</label></li><li><input type="radio" class="cmb2-option" name="options_test_field" id="options_test_field2" value="two"/><label for="options_test_field2">Two</label></li><li><input type="radio" class="cmb2-option" name="options_test_field" id="options_test_field3" value="true"/><label for="options_test_field3">1</label></li><li><input type="radio" class="cmb2-option" name="options_test_field" id="options_test_field4" value="false"/><label for="options_test_field4"></label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);
	}

	public function test_multicheck_field() {
		$args = $this->options_test['fields'][0];
		$args['type'] = 'multicheck';
		$field = $this->get_field_object( $args );
		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-checkbox-list cmb2-list"><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field1" value="one"/><label for="options_test_field1">One</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field2" value="two"/><label for="options_test_field2">Two</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field3" value="true"/><label for="options_test_field3">1</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field4" value="false"/><label for="options_test_field4"></label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);
	}

	public function test_multicheck_field_after_value_update() {
  		update_post_meta( $this->post_id, $this->options_test['fields'][0]['id'], array( 'false', 'one' ) );

		$args = $this->options_test['fields'][0];
		$args['type'] = 'multicheck';
		$field = $this->get_field_object( $args );
		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-checkbox-list cmb2-list"><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field1" value="one" checked="checked"/><label for="options_test_field1">One</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field2" value="two"/><label for="options_test_field2">Two</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field3" value="true"/><label for="options_test_field3">1</label></li><li><input type="checkbox" class="cmb2-option" name="options_test_field[]" id="options_test_field4" value="false" checked="checked"/><label for="options_test_field4"></label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_checkbox_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="checkbox" class="cmb2-option cmb2-list" name="field_test_field" id="field_test_field" value="on"/><label for="field_test_field"><span class="cmb2-metabox-description">This is a description</span></label>',
			$this->capture_render( array( $this->get_field_type_object( 'checkbox' ), 'render' ) )
		);
	}

	public function test_taxonomy_radio_field() {
		$args = $this->options_test['fields'][0];
		$args['type'] = 'taxonomy_radio';
		$args['taxonomy'] = 'category';
		$field = $this->get_field_object( $args );

		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-radio-list cmb2-list"><li><input type="radio" class="cmb2-option" name="field_test_field" id="field_test_field1" value=""/><label for="field_test_field1">None</label></li><li><input type="radio" class="cmb2-option" name="field_test_field" id="field_test_field2" value="number_2"/><label for="field_test_field2">number_2</label></li><li><input type="radio" class="cmb2-option" name="field_test_field" id="field_test_field3" value="test_category" checked="checked"/><label for="field_test_field3">test_category</label></li><li><input type="radio" class="cmb2-option" name="field_test_field" id="field_test_field4" value="uncategorized"/><label for="field_test_field4">Uncategorized</label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( array(
				'type' => 'taxonomy_radio',
				'taxonomy' => 'category',
			) ), 'render' ) )
		);
	}

	public function test_taxonomy_multicheck_field() {
		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-checkbox-list cmb2-list"><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field1" value="number_2"/><label for="field_test_field1">number_2</label></li><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field2" value="test_category" checked="checked"/><label for="field_test_field2">test_category</label></li><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field3" value="uncategorized"/><label for="field_test_field3">Uncategorized</label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $this->get_field_type_object( array(
				'type' => 'taxonomy_multicheck',
				'taxonomy' => 'category',
			) ), 'render' ) )
		);
	}

	public function test_taxonomy_multicheck_field_after_value_update() {

		$set = wp_set_post_categories( $this->post_id, array( $this->term, 1 ) );
		$terms = wp_get_post_categories( $this->post_id );
		$this->assertTrue( in_array( $this->term, $terms ) );
		$this->assertTrue( !! $set );
		// $this->assertEquals( 0, $this->term );
		$type = $this->get_field_type_object( array(
			'type' => 'taxonomy_multicheck',
			'taxonomy' => 'category',
		) );
		$this->assertHTMLstringsAreEqual(
			'<ul class="cmb2-checkbox-list cmb2-list"><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field1" value="number_2"/><label for="field_test_field1">number_2</label></li><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field2" value="test_category" checked="checked"/><label for="field_test_field2">test_category</label></li><li><input type="checkbox" class="cmb2-option" name="field_test_field[]" id="field_test_field3" value="uncategorized" checked="checked"/><label for="field_test_field3">Uncategorized</label></li></ul><p class="cmb2-metabox-description">This is a description</p>',
			$this->capture_render( array( $type, 'render' ) )
		);

		wp_set_object_terms( $this->post_id, 'test_category', 'category' );
	}

	public function test_file_list_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="hidden" class="cmb2-upload-file cmb2-upload-list" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[120,120]\'/><input type="button" class="cmb2-upload-button button cmb2-upload-list" name="" id="" value="'. __( 'Add or Upload File', 'cmb2' ) .'"/><p class="cmb2-metabox-description">This is a description</p><ul id="field_test_field-status" class="cmb2-media-status cmb-attach-list"></ul>',
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'file_list', 'preview_size' => array( 120, 120 ) ) ), 'render' ) )
		);
	}

	public function test_file_list_field_after_value_update() {

 		$images = get_attached_media( 'image', $this->post_id );
 		$this->assertEquals( $images, array(
 			$this->attachment_id => get_post( $this->attachment_id ),
 			$this->attachment_id2 => get_post( $this->attachment_id2 )
		) );

 		update_post_meta( $this->post_id, $this->text_type_field['id'], array(
 			$this->attachment_id => get_permalink( $this->attachment_id ),
 			$this->attachment_id2 => get_permalink( $this->attachment_id2 )
		) );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="hidden" class="cmb2-upload-file cmb2-upload-list" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[50,50]\'/><input type="button" class="cmb2-upload-button button cmb2-upload-list" name="" id="" value="%7$s"/><p class="cmb2-metabox-description">This is a description</p><ul id="field_test_field-status" class="cmb2-media-status cmb-attach-list"><li>%6$s <strong>?attachment_id=%1$d</strong>&nbsp;&nbsp;&nbsp; (<a href="%3$s/?attachment_id=%1$d" target="_blank" rel="external">%4$s</a> / <a href="#" class="cmb2-remove-file-button">%5$s</a>)<input type="hidden" class="" name="field_test_field[%1$d]" id="filelist-%1$d" value="%3$s/?attachment_id=%1$d"/></li><li>%6$s <strong>?attachment_id=%2$d</strong>&nbsp;&nbsp;&nbsp; (<a href="%3$s/?attachment_id=%2$d" target="_blank" rel="external">%4$s</a> / <a href="#" class="cmb2-remove-file-button">%5$s</a>)<input type="hidden" class="" name="field_test_field[%2$d]" id="filelist-%2$d" value="%3$s/?attachment_id=%2$d"/></li></ul>',
				$this->attachment_id,
				$this->attachment_id2,
				site_url(),
				__( 'Download','cmb2' ),
				__( 'Remove', 'cmb2' ),
				__( 'File:', 'cmb2' ),
				__( 'Add or Upload File', 'cmb2' )
			),
			$this->capture_render( array( $this->get_field_type_object( 'file_list' ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_file_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-upload-file regular-text" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[199,199]\'/><input class="cmb2-upload-button button" type="button" value="'. __( 'Add or Upload File', 'cmb2' ) .'" /><p class="cmb2-metabox-description">This is a description</p><input type="hidden" class="cmb2-upload-file-id" name="field_test_field_id" id="field_test_field_id" value="0"/><div id="field_test_field_id-status" class="cmb2-media-status"></div>',
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'file', 'preview_size' => array( 199, 199 ) ) ), 'render' ) )
		);
	}

	public function test_file_field_after_value_update() {
 		update_post_meta( $this->post_id, $this->text_type_field['id'], get_permalink( $this->attachment_id ) );
 		update_post_meta( $this->post_id, $this->text_type_field['id'] .'_id', $this->attachment_id );
		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-upload-file regular-text" name="field_test_field" id="field_test_field" value="%2$s/?attachment_id=%1$d" size="45" data-previewsize=\'[199,199]\'/><input class="cmb2-upload-button button" type="button" value="%6$s" /><p class="cmb2-metabox-description">This is a description</p><input type="hidden" class="cmb2-upload-file-id" name="field_test_field_id" id="field_test_field_id" value="%1$d"/><div id="field_test_field_id-status" class="cmb2-media-status">%5$s <strong>?attachment_id=%1$d</strong>&nbsp;&nbsp;&nbsp; (<a href="%2$s/?attachment_id=%1$d" target="_blank" rel="external">%3$s</a> / <a href="#" class="cmb2-remove-file-button" rel="field_test_field">%4$s</a>)</div>',
				$this->attachment_id,
				site_url(),
				__( 'Download','cmb2' ),
				__( 'Remove', 'cmb2' ),
				__( 'File:', 'cmb2' ),
				__( 'Add or Upload File', 'cmb2' )
			),
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'file', 'preview_size' => array( 199, 199 ) ) ), 'render' ) )
		);
		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
		delete_post_meta( $this->post_id, $this->text_type_field['id'] .'_id' );
	}

	public function test_oembed_field() {
		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-oembed regular-text" name="field_test_field" id="field_test_field" value="" data-objectid=\'%1$d\' data-objecttype=\'post\'/><p class="cmb2-metabox-description">This is a description</p><p class="cmb-spinner spinner" style="display:none;"></p><div id="field_test_field-status" class="cmb2-media-status ui-helper-clearfix embed_wrap"></div>', $this->post_id ),
			$this->capture_render( array( $this->get_field_type_object( 'oembed' ), 'render' ) )
		);
	}

	public function test_oembed_field_after_value_update() {
		global $wp_version;

		$vid = 'EOfy5LDpEHo';
		$value = 'https://www.youtube.com/watch?v='. $vid;
		$src = 'http'. ( $wp_version > 3.9 ? 's' : '' ) .'://www.youtube.com/embed/'. $vid .'?feature=oembed';
 		update_post_meta( $this->post_id, $this->text_type_field['id'], $value );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-oembed regular-text" name="field_test_field" id="field_test_field" value="%1$s" data-objectid=\'%2$d\' data-objecttype=\'post\'/><p class="cmb2-metabox-description">This is a description</p><p class="cmb-spinner spinner" style="display:none;"></p><div id="field_test_field-status" class="cmb2-media-status ui-helper-clearfix embed_wrap"><div class="embed-status"><iframe width="640" height="360" src="%3$s" frameborder="0" allowfullscreen></iframe><p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button" rel="field_test_field">' . __( 'Remove Embed', 'cmb2' ) . '</a></p></div></div>', $value, $this->post_id, $src ),
			$this->capture_render( array( $this->get_field_type_object( 'oembed' ), 'render' ) )
		);
		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}


	/**
	 * CMB2_Types_Test helper methods
	 */

	private function get_field_object( $type = '' ) {
		$args = $this->text_type_field;

		if ( $type ) {
			if ( is_string( $type ) ) {
				$args['type'] = $type;
			} elseif ( is_array( $type ) ) {
				$args = wp_parse_args( $type, $args );
			}
		}

		return new CMB2_Field( array(
			'field_args' => $args,
			'object_id' => $this->post_id,
		) );
	}

	private function get_field_type_object( $args = '' ) {
		$field = is_a( $args, 'CMB2_Field' ) ? $args : $this->get_field_object( $args );
		return new CMB2_Types( $field );
	}

	private function capture_render( $cb ) {
		ob_start();
		call_user_func( $cb );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	private function render_field( $field ) {
		return $this->capture_render( array( $field, 'render_field' ) );
	}

	/**
	 * Test Callbacks
	 */

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

	public function add_type_cb( $types ) {
		$types[] = 'test';
		return $types;
	}

	public function change_money_cb( $field_args ) {
		return '£ '. $field_args['type'];
	}

}
