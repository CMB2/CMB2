<?php
/**
 * CMB2_Types tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'test-cmb-types-base.php' );

class Test_CMB2_Types extends Test_CMB2_Types_Base {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
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
		<div class="cmb-row cmb-type-text cmb2-id-field-test-field cmb-repeat table-layout" data-fieldtype="text">
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
								<button type="button" class="button cmb-remove-row-button button-disabled">' . __( 'Remove', 'cmb2' ) . '</button>
							</div>
						</div>
						<div class="cmb-row empty-row hidden">
							<div class="cmb-td">
								<input type="text" class="regular-text" name="field_test_field[1]" id="field_test_field_1" data-iterator="1" value=""/>
							</div>
							<div class="cmb-td cmb-remove-row">
								<button type="button" class="button cmb-remove-row-button">' . __( 'Remove', 'cmb2' ) . '</button>
							</div>
						</div>
					</div>
				</div>
				<p class="cmb-add-row">
					<button type="button" data-selector="field_test_field_repeat" class="cmb-add-row-button button">ADD NEW ROW</button>
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
		<div class="cmb-row cmb-type-select cmb2-id-options-cb-test-field" data-fieldtype="select">
			<div class="cmb-th"><label for="options_cb_test_field">Name</label></div>
			<div class="cmb-td">
				<select class="cmb2_select" name="options_cb_test_field" id="options_cb_test_field">
					<option value="one" >One</option>
					<option value="two" >Two</option>
					<option value="true" >1</option>
					<option value="false" ></option>
					<option value="post_id" >' . $this->post_id . '</option>
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
		<div class="cmb-row cmb-type-select cmb2-id-options-test-field" data-fieldtype="select">
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

	public function test_field_options_bools() {
		$cmb   = new CMB2( $this->options_test );
		$field = cmb2_get_field( $this->options_test['id'], 'options_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$this->assertEquals( $field->options( 'one' ), 'One' );
		$this->assertEquals( $field->options( 'two' ), 'Two' );
		$this->assertTrue( $field->options( 'true' ) );
		$this->assertFalse( $field->options( 'false' ) );
		$this->assertFalse( $field->options( 'random_string' ) );
	}

	public function test_field_attributes() {
		$cmb   = new CMB2( $this->attributes_test );
		$field = cmb2_get_field( $this->attributes_test['id'], 'attributes_test_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$expected_field = '
		<div class="cmb-row cmb-type-text cmb2-id-attributes-test-field table-layout" data-fieldtype="text">
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
		$type->type = new CMB2_Type_File( $type );

		$ext = $type->get_file_ext( site_url( '/wp-content/uploads/2014/12/test-file.pdf' ) );
		$this->assertFalse( $type->is_valid_img_ext( $ext ) );
		$this->assertFalse( $type->is_valid_img_ext( '.pdf' ) );
		$this->assertFalse( $type->is_valid_img_ext( 'jpg' ) );
		$this->assertFalse( $type->is_valid_img_ext( '.test' ) );

		$valid_types = apply_filters( 'cmb2_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) );

		foreach ( $valid_types as $ext ) {
			$is_valid = $type->is_valid_img_ext( '/test.' . $ext, true );
			$this->assertEquals( $is_valid, $type->type->is_valid_img_ext( '/test.' . $ext, true ) );
			$this->assertTrue( $is_valid );
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
			'<input type="hidden" class="cmb2-hidden" name="field_test_field" id="field_test_field" value=""/>',
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

		$value = 'test value';
		update_post_meta( $this->post_id, $this->text_type_field['id'], $value );

		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-text-url cmb2-text-medium regular-text" name="field_test_field" id="field_test_field" value="' . esc_url_raw( $value ) . '"/><p class="cmb2-metabox-description">This is a description</p>',
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
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field" id="field_test_field" value="%s" data-datepicker=\'{"dateFormat":"mm&#39;\/&#39;dd&#39;\/&#39;yy"}\'/><span class="cmb2-metabox-description">This is a description</span>', $value ),
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
			sprintf( '<input type="text" class="cmb2-timepicker text-time" name="field_test_field" id="field_test_field" value="%s" data-timepicker=\'{"timeFormat":"hh:mm TT"}\'/><span class="cmb2-metabox-description">This is a description</span>', $value ),
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

	public function test_text_money_field_value_update() {
		$field = $this->get_field_object( 'text_money' );
		$field->save_field( '8.2' );
		$this->assertEquals( '8.20', get_post_meta( $this->post_id, $this->text_type_field['id'], 1 ) );

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
		$field = $this->get_field_object( 'text_money' );
		$field->save_field( '0.00' );
		$this->assertEquals( '0.00', get_post_meta( $this->post_id, $this->text_type_field['id'], 1 ) );

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
		$field->save_field( '0' );
		$this->assertEquals( '', get_post_meta( $this->post_id, $this->text_type_field['id'], 1 ) );

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

		$field = $this->get_field_object( array( 'type' => 'wysiwyg', 'options' => array( 'quicktags' => false ) ) );
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
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field" id="field_test_field" value="%s" data-datepicker=\'{"dateFormat":"mm&#39;\/&#39;dd&#39;\/&#39;yy"}\'/><span class="cmb2-metabox-description">This is a description</span>', $formatted_val_to_update ),
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
			sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field[date]" id="field_test_field_date" value="%s" data-datepicker=\'{"dateFormat":"mm&#39;\/&#39;dd&#39;\/&#39;yy"}\'/><input type="text" class="cmb2-timepicker text-time" name="field_test_field[time]" id="field_test_field_time" value="%s" data-timepicker=\'{"timeFormat":"hh:mm TT"}\'/><span class="cmb2-metabox-description">This is a description</span>', $date_val, $time_val ),
			$this->capture_render( array( $this->get_field_type_object( $field ), 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_text_datetime_timestamp_timezone_field_after_value_update() {

		if ( version_compare( PHP_VERSION, '5.3' ) >= 0 ) {

			// date_default_timezone_set( 'America/New_York' );
			// $tzstring = cmb2_utils()->timezone_string();
			$tzstring = 'America/New_York';
			$test_stamp = strtotime( '2pm April 12 2016' );

			$field = $this->get_field_object( 'text_datetime_timestamp_timezone' );
			$date_val = $field->format_timestamp( $test_stamp );
			$time_val = $field->format_timestamp( $test_stamp, 'time_format' );

			$value_to_save = new DateTime( $date_val . ' ' . $time_val, new DateTimeZone( $tzstring ) );
			$value_to_save = serialize( $value_to_save );

			update_post_meta( $this->post_id, $this->text_type_field['id'], $value_to_save );

			$get_val = get_post_meta( $this->post_id, $this->text_type_field['id'], 1 );
			$this->assertEquals( $value_to_save, $get_val );

			$zones = wp_timezone_choice( $tzstring );

			$this->assertHTMLstringsAreEqual(
				sprintf( '<input type="text" class="cmb2-text-small cmb2-datepicker" name="field_test_field[date]" id="field_test_field_date" value="04/12/2016" data-datepicker=\'{"dateFormat":"mm&#39;\/&#39;dd&#39;\/&#39;yy"}\'/><input type="text" class="cmb2-timepicker text-time" name="field_test_field[time]" id="field_test_field_time" value="06:00 PM" data-timepicker=\'{"timeFormat":"hh:mm TT"}\'/><select class="cmb2_select cmb2-select-timezone" name="field_test_field[timezone]" id="field_test_field_timezone">%s</select><p class="cmb2-metabox-description">This is a description</p>', $zones ),
				$this->capture_render( array( $this->get_field_type_object( 'text_datetime_timestamp_timezone' ), 'render' ) )
			);

			delete_post_meta( $this->post_id, $this->text_type_field['id'] );
		}
	}

	public function test_select_timezone_field_after_value_update() {
		$value_to_save = cmb2_utils()->timezone_string();
		update_post_meta( $this->post_id, $this->text_type_field['id'], $value_to_save );
		$zones = wp_timezone_choice( $value_to_save );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<select class="cmb2_select cmb2-select-timezone" name="field_test_field" id="field_test_field">%s</select><span class="cmb2-metabox-description">This is a description</span>', $zones ),
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
		$type_object = $this->get_field_type_object( 'checkbox' );
		$this->check_box_assertion( array( $type_object, 'render' ) );

		update_post_meta( $type_object->field->object_id, 'field_test_field', 'true' );

		// Test when value exists
		$this->check_box_assertion( array( $this->get_field_type_object( 'checkbox' ), 'render' ), true );

		$type_object = $this->get_field_type_object( 'checkbox' );

		// Test when value exists again
		$this->check_box_assertion( $type_object->checkbox(), true );

		// Test when value exists but we tell checkbox it's not checked
		$this->check_box_assertion( $type_object->checkbox( array(), false ) );

		delete_post_meta( $type_object->field->object_id, 'field_test_field' );

		// Test when value doesn't exist but we tell checkbox it is checked
		$this->check_box_assertion( $type_object->checkbox( array(), true ), true );

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
			'<input type="hidden" class="cmb2-upload-file cmb2-upload-list" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[120,120]\' data-queryargs=\'\'/><input type="button" class="cmb2-upload-button button cmb2-upload-list" name="" id="" value="' . __( 'Add or Upload Files', 'cmb2' ) . '"/><p class="cmb2-metabox-description">This is a description</p><ul id="field_test_field-status" class="cmb2-media-status cmb-attach-list"></ul>',
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'file_list', 'preview_size' => array( 120, 120 ) ) ), 'render' ) )
		);
	}

	public function test_file_list_field_after_value_update() {

		$images = get_attached_media( 'image', $this->post_id );
		$attach_1_url = get_permalink( $this->attachment_id );
		$attach_2_url = get_permalink( $this->attachment_id2 );

		$this->assertEquals( $images, array(
			$this->attachment_id => get_post( $this->attachment_id ),
			$this->attachment_id2 => get_post( $this->attachment_id2 ),
		) );

		update_post_meta( $this->post_id, $this->text_type_field['id'], array(
			$this->attachment_id => $attach_1_url,
			$this->attachment_id2 => $attach_2_url,
		) );

		$field_type = $this->get_field_type_object( 'file_list' );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="hidden" class="cmb2-upload-file cmb2-upload-list" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[50,50]\' data-queryargs=\'\'/><input type="button" class="cmb2-upload-button button cmb2-upload-list" name="" id="" value="' . __( 'Add or Upload Files', 'cmb2' ) . '"/><p class="cmb2-metabox-description">This is a description</p><ul id="field_test_field-status" class="cmb2-media-status cmb-attach-list">%1$s%2$s</ul>',
				$this->file_sprintf( array(
					'file_name'     => $field_type->get_file_name_from_path( $attach_1_url ),
					'attachment_id' => $this->attachment_id,
					'url'           => $attach_1_url,
				) ),
				$this->file_sprintf( array(
					'file_name'     => $field_type->get_file_name_from_path( $attach_2_url ),
					'attachment_id' => $this->attachment_id2,
					'url'           => $attach_2_url,
				) )
			),
			$this->capture_render( array( $field_type, 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_file_field() {
		$this->assertHTMLstringsAreEqual(
			'<input type="text" class="cmb2-upload-file regular-text" name="field_test_field" id="field_test_field" value="" size="45" data-previewsize=\'[199,199]\' data-queryargs=\'\'/><input class="cmb2-upload-button button" type="button" value="' . __( 'Add or Upload File', 'cmb2' ) . '" /><p class="cmb2-metabox-description">This is a description</p><input type="hidden" class="cmb2-upload-file-id" name="field_test_field_id" id="field_test_field_id" value="0"/><div id="field_test_field_id-status" class="cmb2-media-status"></div>',
			$this->capture_render( array( $this->get_field_type_object( array( 'type' => 'file', 'preview_size' => array( 199, 199 ) ) ), 'render' ) )
		);
	}

	public function test_file_field_after_value_update() {
 		update_post_meta( $this->post_id, $this->text_type_field['id'], get_permalink( $this->attachment_id ) );
 		update_post_meta( $this->post_id, $this->text_type_field['id'] . '_id', $this->attachment_id );

 		$field_type = $this->get_field_type_object( array(
			'type'         => 'file',
			'preview_size' => array( 199, 199 ),
		) );

 		$file_url = get_permalink( $this->attachment_id );
 		$file_name = $field_type->get_file_name_from_path( $file_url );

		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-upload-file regular-text" name="field_test_field" id="field_test_field" value="%2$s" size="45" data-previewsize=\'[199,199]\' data-queryargs=\'\'/><input class="cmb2-upload-button button" type="button" value="' . __( 'Add or Upload File', 'cmb2' ) . '" /><p class="cmb2-metabox-description">This is a description</p><input type="hidden" class="cmb2-upload-file-id" name="field_test_field_id" id="field_test_field_id" value="%1$d"/><div id="field_test_field_id-status" class="cmb2-media-status"><div class="file-status"><span>' . __( 'File:', 'cmb2' ) . ' <strong>%3$s</strong></span>&nbsp;&nbsp; (<a href="%2$s" target="_blank" rel="external">' . __( 'Download','cmb2' ) . '</a> / <a href="#" class="cmb2-remove-file-button" rel="field_test_field">' . __( 'Remove', 'cmb2' ) . '</a>)</div></div>',
				$this->attachment_id,
				$file_url,
				$file_name
			),
			$this->capture_render( array( $field_type, 'render' ) )
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
		delete_post_meta( $this->post_id, $this->text_type_field['id'] . '_id' );
	}

	public function test_oembed_field() {
		$this->assertHTMLstringsAreEqual(
			sprintf( '<input type="text" class="cmb2-oembed regular-text" name="field_test_field" id="field_test_field" value="" data-objectid=\'%1$d\' data-objecttype=\'post\'/><p class="cmb2-metabox-description">This is a description</p><p class="cmb-spinner spinner" style="display:none;"></p><div id="field_test_field-status" class="cmb2-media-status ui-helper-clearfix embed_wrap"></div>', $this->post_id ),
			$this->capture_render( array( $this->get_field_type_object( 'oembed' ), 'render' ) )
		);
	}

	public function test_oembed_field_after_value_update() {
		$vid = 'EOfy5LDpEHo';
		$value = 'https://www.youtube.com/watch?v=' . $vid;
 		update_post_meta( $this->post_id, $this->text_type_field['id'], $value );

 		$results = $this->expected_youtube_oembed_results( array(
			'src'      => 'http://www.youtube.com/embed/' . $vid . '?feature=oembed',
			'url'      => $value,
			'field_id' => 'field_test_field',
		) );

 		$expected_field = sprintf( '<input type="text" class="cmb2-oembed regular-text" name="field_test_field" id="field_test_field" value="%1$s" data-objectid=\'%2$d\' data-objecttype=\'post\'/><p class="cmb2-metabox-description">This is a description</p><p class="cmb-spinner spinner" style="display:none;"></p><div id="field_test_field-status" class="cmb2-media-status ui-helper-clearfix embed_wrap">%3$s</div>', $value, $this->post_id, $results );

 		$actual_field = $this->capture_render( array( $this->get_field_type_object( 'oembed' ), 'render' ) );

		$this->assertHTMLstringsAreEqual(
			preg_replace( '~https?://~', '', $expected_field ), // normalize http differences
			preg_replace( '~https?://~', '', $actual_field ) // normalize http differences
		);

		delete_post_meta( $this->post_id, $this->text_type_field['id'] );
	}

	public function test_js_dependencies() {
		$this->assertEquals( array(
			'jquery'                   => 'jquery',
			'jquery-ui-core'           => 'jquery-ui-core',
			'jquery-ui-datepicker'     => 'jquery-ui-datepicker',
			'jquery-ui-datetimepicker' => 'jquery-ui-datetimepicker',
			'media-editor'             => 'media-editor',
			'wp-color-picker'          => 'wp-color-picker',
		), Test_CMB2_JS::dependencies() );
	}

	public function test_save_group() {
		$is_53 = version_compare( PHP_VERSION, '5.3' ) >= 0;

		$cmb_group = new_cmb2_box( array(
			'id'           => 'group_metabox',
			'title'        => 'title',
			'object_types' => array( 'page', ),
		) );
		$group_field_id = $cmb_group->add_field( array(
			'id'   => 'group',
			'type' => 'group',
		) );
		foreach ( array( 'text', 'textarea_small', 'file', ) as $type ) {
			$cmb_group->add_group_field( $group_field_id, array(
				'id'   => $type,
				'type' => $type,
			) );
		}
		if ( $is_53 ) {
			$date_args = array(
				'id' => 'text_datetime_timestamp_timezone',
				'type' => 'text_datetime_timestamp_timezone',
				'time_format' => 'H:i',
				'date_format' => 'Y-m-d',
				'repeatable' => true,
			);
			$cmb_group->add_group_field( $group_field_id, $date_args );
		}

		$to_save = array(
			'group' => array(
				array(
					'text' => 'Entry Title',
					'textarea_small' => 'Nullam id dolor id nibh ultricies vehicula ut id elit. ',
					'file' => 'http://example.com/files/2015/07/IMG.jpg',
					'file_id' => 518,
					'text_datetime_timestamp_timezone' => array(
						array(
							'date' => '2015-11-20',
							'time' => '17:00',
							'timezone' => 'America/New_York',
						),
						array(
							'date' => '2015-11-20',
							'time' => '17:00',
							'timezone' => 'America/Chicago',
						),
						array(
							'date' => null,
							'time' => null,
							'timezone' => null,
						),
					),
				),
			),
		);

		if ( ! $is_53 ) {
			unset( $to_save['group'][0]['text_datetime_timestamp_timezone'] );
		} else {
			$date_values = array();
			foreach ( $to_save['group'][0]['text_datetime_timestamp_timezone'] as $key => $value ) {
				if ( null === $value['date'] ) {
					continue;
				}

				$tzstring = $value['timezone'];
				$offset = cmb2_utils()->timezone_offset( $tzstring );

				if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
					$tzstring = timezone_name_from_abbr( '', $offset, 0 );
					$tzstring = false !== $tzstring ? $tzstring : timezone_name_from_abbr( '', 0, 0 );
				}

				$full_format = $date_args['date_format'] . ' ' . $date_args['time_format'];
				$full_date   = $value['date'] . ' ' . $value['time'];

				$datetime = date_create_from_format( $full_format, $full_date );

				if ( ! is_object( $datetime ) ) {
					$date_values[] = '';
				} else {
					$timestamp = $datetime->setTimezone( new DateTimeZone( $tzstring ) )->getTimestamp();
					$date_values[] = serialize( $datetime );
				}
			}
		}

		$values = cmb2_get_metabox( $cmb_group->cmb_id, $this->post_id, 'post' )->get_sanitized_values( $to_save );

		$expected = array(
			'group' => array(
				array(
					'text' => 'Entry Title',
					'textarea_small' => 'Nullam id dolor id nibh ultricies vehicula ut id elit. ',
					'file_id' => 518,
					'file' => 'http://example.com/files/2015/07/IMG.jpg',
				),
			),
		);

		if ( $is_53 ) {

			date_default_timezone_set( 'America/New_York' );

			$expected['group'][0]['text_datetime_timestamp_timezone_utc'] = array( 1448056800, 1448060400 );

			// If DST, remove an hour.
			if ( date( 'I' ) ) {
				foreach ( $expected['group'][0]['text_datetime_timestamp_timezone_utc'] as $key => $value ) {
					$expected['group'][0]['text_datetime_timestamp_timezone_utc'][ $key ] = $value - 3600;
				}
			}

			$expected['group'][0]['text_datetime_timestamp_timezone'] = $date_values;
		}

		$this->assertEquals( $expected, $values );
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
		return '£ ' . $field_args['type'];
	}

}
