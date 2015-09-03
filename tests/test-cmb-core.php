<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

class Test_CMB2_Core extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';
		$this->metabox_array = array(
			'id' => $this->cmb_id,
			'fields' => array(
				'test_test' => array(
					'name'        => 'Name',
					'description' => 'Description',
					'id'          => 'test_test',
					'type'        => 'text',
					'before_row'  => array( $this, 'cmb_before_row' ),
					'before'      => 'testing before',
					'after'       => array( $this, 'cmb_after' ),
					'after_row'   => 'testing after row',
				),
			),
		);

		$this->metabox_array2 = array(
			'id' => 'test2',
			'fields' => array(
				'test_test' => array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
				),
			),
		);

		$this->option_metabox_array = array(
			'id'            => 'options_page',
			'title'         => 'Theme Options Metabox',
			'show_on'    => array( 'options-page' => array( 'theme_options', ), ),
			'fields'        => array(
				'bg_color' => array(
					'name'    => 'Site Background Color',
					'desc'    => 'field description (optional)',
					'id'      => 'bg_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
			)
		);

		$this->user_metabox_array = array(
			'id'               => 'user_metabox',
			'title'            => 'User Profile Metabox',
			'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
			'show_names'       => true,
			'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
		    'fields'           => array(
			    'extra_info' => array(
				    'name'     => 'Extra Info',
				    'desc'     => 'field description (optional)',
				    'id'       => 'extra_info',
				    'type'     => 'title',
				    'on_front' => false,
			    )
		    )
		);

		$this->defaults = array(
			'id'           => $this->cmb_id,
			'title'        => '',
			'type'         => '',
			'object_types' => array(), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
			'show_on'      => array(), // Specific post IDs or page templates to display this metabox
			'show_on_cb'   => null, // Callback to determine if metabox should display. Overrides 'show_on'
			'cmb_styles'   => true, // Include cmb bundled stylesheet
			'enqueue_js'   => true, // Include CMB2 JS
			'fields'       => array(),
			'hookup'       => true,
			'save_fields'  => true, // Will not save during hookup if false
			'closed'       => false, // Default to metabox being closed?
			'new_user_section' => 'add-new-user', // or 'add-existing-user'
		);

		$this->cmb = new CMB2( $this->metabox_array );

		$this->options_cmb = new CMB2( $this->option_metabox_array );

		$this->opt_set = array(
			'bg_color' => '#ffffff',
			'my_name' => 'Justin',
		);
		add_option( $this->options_cmb->cmb_id, $this->opt_set );

		$this->post_id = $this->factory->post->create();

	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_cmb2_definitions() {
		foreach ( array(
			'CMB2_LOADED',
			'CMB2_VERSION',
			'CMB2_DIR',
		) as $key => $definition ) {
			$this->assertIsDefined( $definition );
		}
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_cmb2_die_with_no_id() {
		$cmb = new CMB2( array() );
	}

	/**
	 * @expectedException Test_CMB2_Exception
	 */
	public function test_set_metabox_after_offlimits() {
		try {
			// Fyi you don't need to do an assert test here, as we are only testing the exception, so just make the call
			$this->cmb->metabox['title'] = 'title';
		} catch ( Exception $e ) {
			if ( 'Exception' === get_class( $e ) ) {
				throw new Test_CMB2_Exception( $e->getMessage(), $e->getCode() );
			}
		}
	}

	public function test_id_get() {
		$cmb = new CMB2( array( 'id' => $this->cmb_id ) );
		$this->assertEquals( $cmb->cmb_id, $this->cmb_id );
	}

	public function test_defaults_set() {
		$cmb = new CMB2( array( 'id' => $this->cmb_id ) );

		$this->assertEquals( $this->defaults, $cmb->meta_box, print_r( array( $this->defaults, $cmb->meta_box ), true ) );
	}

	public function test_url_set() {
		$cmb2_url = str_replace(
			array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
			array( WP_CONTENT_URL, WP_PLUGIN_URL ),
			cmb2_dir()
		);

		$this->assertEquals( cmb2_utils()->url(), $cmb2_url );
	}

	public function test_array_insert() {
		$array = array(
			'one' => array( 1,2,3 ),
			'two' => array( 1,2,3 ),
			'three' => array( 1,2,3 ),
		);

		$new = array( 'new' => array( 4,5,6 ) );

		cmb2_utils()->array_insert( $array, $new, 2 );

		$this->assertEquals( array(
			'one' => array( 1,2,3 ),
			'new' => array( 4,5,6 ),
			'two' => array( 1,2,3 ),
			'three' => array( 1,2,3 ),
		), $array );
	}

	public function test_cmb2_get_metabox() {
		// Test that successful retrieval by box ID
		$retrieve = cmb2_get_metabox( $this->cmb_id );
		$this->assertEquals( $this->cmb, $retrieve );

		// Test that successful retrieval by box Array
		$cmb = cmb2_get_metabox( $this->metabox_array );
		$this->assertEquals( $this->cmb, $cmb );


		// Test successful creation of new MB
		$cmb1 = cmb2_get_metabox( $this->metabox_array2 );
		$cmb2 = new CMB2( $this->metabox_array2 );
		$this->assertEquals( $cmb1, $cmb2 );

		$cmb_user = cmb2_get_metabox( $this->user_metabox_array );
		$this->assertEquals( 'user', $cmb_user->mb_object_type() );
	}

	public function test_cmb2_get_field() {
		$field_id    = 'test_test';
		$retrieved   = cmb2_get_field( $this->cmb_id, $field_id, $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $retrieved );
	}

	public function test_cmb2_get_field_value() {
		$val_to_save = '123Abc';
		$field_id    = 'test_test';
		$added       = add_post_meta( $this->post_id, $field_id, $val_to_save );

		$retrieved   = cmb2_get_field_value( $this->cmb_id, $field_id, $this->post_id );

		// Test retrieved value matches value we saved
		$this->assertEquals( $val_to_save, $retrieved );

		$get = get_post_meta( $this->post_id, $field_id, 1 );

		// Test retrieved value matches normal WP retrieved value
		$this->assertEquals( $get, $retrieved );
	}

	public function test_cmb2_print_metabox_form() {
		$expected_form = '
		<form class="cmb-form" method="post" id="' . $this->cmb_id . '" enctype="multipart/form-data" encoding="multipart/form-data">
			<input type="hidden" name="object_id" value="' . $this->post_id . '">
			' . wp_nonce_field( $this->cmb->nonce(), $this->cmb->nonce(), false, false ) . '
			<!-- Begin CMB2 Fields -->
			<div class="cmb2-wrap form-table">
				<div id="cmb2-metabox-' . $this->cmb_id . '" class="cmb2-metabox cmb-field-list">
					function test_before_row Description test_test
					<div class="cmb-row cmb-type-text cmb2-id-test-test table-layout">
						<div class="cmb-th">
							<label for="test_test">Name</label>
						</div>
						<div class="cmb-td">
							testing before
							<input type="text" class="regular-text" name="test_test" id="test_test" value=""/>
							<p class="cmb2-metabox-description">Description</p>
							function test_after Description test_test
						</div>
					</div>
					testing after row
				</div>
			</div>
			<!-- End CMB2 Fields -->
			<input type="submit" name="submit-cmb" value="Save" class="button-primary">
		</form>
		';

		$form_get = cmb2_get_metabox_form( $this->cmb_id, $this->post_id );

		$this->assertHTMLstringsAreEqual( $expected_form, $form_get );
	}

	public function cmb_before_row( $field_args, $field ) {
		echo 'function test_before_row ' . $field_args['description'] . ' ' . $field->id();
	}

	public function cmb_after( $field_args, $field ) {
		echo 'function test_after ' . $field_args['description'] . ' ' . $field->id();
	}

	public function test_cmb2_options() {
		$opts = cmb2_options( $this->options_cmb->cmb_id );
		$this->assertEquals( $opts->get_options(), $this->opt_set );
	}

	public function test_cmb2_get_option() {
		$get = get_option( $this->options_cmb->cmb_id );
		$val = cmb2_get_option( $this->options_cmb->cmb_id, 'my_name' );

		$this->assertEquals( $this->opt_set['my_name'], $get['my_name'] );
		$this->assertEquals( $val, $get['my_name'] );
		$this->assertEquals( $val, $this->opt_set['my_name'] );
	}

	public function test_cmb2_update_option() {
		$new_value = 'James';

		cmb2_update_option( $this->options_cmb->cmb_id, 'my_name', $new_value );

		$get = get_option( $this->options_cmb->cmb_id );
		$val = cmb2_get_option( $this->options_cmb->cmb_id, 'my_name' );

		$this->assertEquals( $new_value, $get['my_name'] );
		$this->assertEquals( $val, $get['my_name'] );
		$this->assertEquals( $val, $new_value );
	}

	public function test_class_getters() {
		$this->assertInstanceOf( 'CMB2_Ajax', cmb2_ajax() );
		$this->assertInstanceOf( 'CMB2_Utils', cmb2_utils() );
		$this->assertInstanceOf( 'CMB2_Option', cmb2_options( 'test' ) );
	}

	public function test_boxes_get_all() {
		$this->assertContainsOnlyInstancesOf( 'CMB2', CMB2_Boxes::get_all() );
	}

	public function test_boxes_get() {
		new Test_CMB2_Object( $this->metabox_array2 );

		// Retrieve the instance
		$cmb = cmb2_get_metabox( 'test2' );

		$after_args_parsed = wp_parse_args( $this->metabox_array2, $cmb->get_metabox_defaults() );
		foreach ( $after_args_parsed as $key => $value ) {
			// Field are tested separately, below
			if ( 'fields' != $key ) {
				$this->assertEquals( $value, $cmb->meta_box[ $key ] );
			}
		}

		foreach( $after_args_parsed[ 'fields' ] as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $cmb->meta_box[ 'fields' ][ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_update_field_property() {
		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		$this->assertInstanceOf( 'CMB2', $cmb );

		$test = $cmb->update_field_property( 'test_test', 'type', 'textarea' );

		$this->assertEquals( 'test_test', $test );

		$field_id = $cmb->update_field_property( 'test_test', 'name', 'Test Name' );

		$this->assertNotFalse( $field_id );

		// Get all fields for this metabox
		$fields = $cmb->prop( 'fields' );

		// Get the attributes array if it exists, or else create it
		$attributes = isset( $fields[ $field_id ]['attributes'] )
			? $fields[ $field_id ]['attributes']
			: array();

		// Add placeholder text
		$attributes['placeholder'] = "I'm some placeholder text";

		// Update the field's 'attributes' property
		$cmb->update_field_property( 'test_test', 'attributes', $attributes );

	}

	public function test_updated_fields_properties() {
		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );
		$this->assertInstanceOf( 'CMB2', $cmb );

		$field = cmb2_get_field( $cmb, 'test_test', $this->post_id );

		$this->assertEquals( 'textarea', $field->type() );
		$this->assertEquals( array( 'placeholder' => "I'm some placeholder text" ), $field->attributes() );

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
			)
		);
		$test_fields = $cmb->prop( 'fields' );
		foreach( $field_array as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name] );
			}
		}
	}

	public function test_add_field() {

		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		// This should return false because we don't have a 'demo_text2' field
		$field_id = $cmb->update_field_property( 'demo_text2', 'type', 'text' );
		$this->assertFalse( $field_id );

		$field_id = $cmb->add_field( array(
			'name'       => 'Test Text 2',
			'desc'       => 'Test Text 2 description',
			'id'         => 'demo_text2',
			'type'       => 'text',
			'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
		) );

		$this->assertEquals( 'demo_text2', $field_id );
	}

	public function test_added_field() {

		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
			),
			'demo_text2' => array(
				'name'       => 'Test Text 2',
				'desc'       => 'Test Text 2 description',
				'id'         => 'demo_text2',
				'type'       => 'text',
				'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
			)
		);
		$test_fields = $cmb->prop( 'fields' );

		foreach( $field_array as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name] );
			}
		}
	}

	public function test_add_group_field( $do_assertions = null ) {

		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		// This should return false because we don't have a 'group_field' field
		$field_id = $cmb->update_field_property( 'group_field', 'type', 'group' );
		$this->assertFalse( $field_id );

		$field_id = $cmb->add_field( array(
			'name' => 'Group',
			'desc' => 'Group description',
			'id'   => 'group_field',
			'type' => 'group',
		) );

		$this->assertEquals( 'group_field', $field_id );

		$sub_field_id = $cmb->add_group_field( $field_id, array(
			'name' => 'Field 1',
			'id'   => 'first_field',
			'type' => 'text',
		) );

		$this->assertEquals( array( 'group_field', 'first_field' ), $sub_field_id );

		$sub_field_id = $cmb->add_group_field( $field_id, array(
			'name' => 'Colorpicker',
			'id'   => 'colorpicker',
			'type' => 'colorpicker',
		), 1 ); // Test that the position argument is working

		$this->assertEquals( array( 'group_field', 'colorpicker' ), $sub_field_id );

	}

	public function test_group_field_param_callbacks() {

		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		$field_id = $cmb->update_field_property( 'group_field', 'before_group', 'before_group output' );
		$field_id = $cmb->update_field_property( 'group_field', 'options', array( 'closed' => true ) );

		$this->assertTrue( ! empty( $field_id ) );

		$cmb->update_field_property( 'group_field', 'before_group_row', 'before_group_row output' );
		$cmb->update_field_property( 'group_field', 'after_group_row', 'after_group_row output' );
		$cmb->update_field_property( 'group_field', 'after_group', 'after_group output' );

		$fields = $cmb->prop( 'fields' );
		$field = new CMB2_Field( array(
			'field_args'  => $fields['group_field'],
			'object_type' => $cmb->object_type(),
			'object_id'   => $cmb->object_id(),
		) );

		$expected_group_render = '
		before_group output
		<div class="cmb-row cmb-repeat-group-wrap">
			<div class="cmb-td">
				<div id="group_field_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group non-sortable repeatable" style="width:100%;">
					<div class="cmb-row cmb-group-description">
						<div class="cmb-th">
							<h2 class="cmb-group-name">Group</h2>
							<p class="cmb2-metabox-description">Group description</p>
						</div>
					</div>
					before_group_row output
					<div class="postbox cmb-row cmb-repeatable-grouping closed" data-iterator="0">
						<button disabled="disabled" data-selector="group_field_repeat" class="dashicons-before dashicons-no-alt cmb-remove-group-row"></button>
						<div class="cmbhandle" title="Click to toggle"><br></div>
						<h3 class="cmb-group-title cmbhandle-title"><span></span></h3>
						<div class="inside cmb-td cmb-nested cmb-field-list">
							<div class="cmb-row cmb-type-colorpicker cmb2-id-group-field-0-colorpicker cmb-repeat-group-field">
								<div class="cmb-th">
									<label for="group_field_0_colorpicker">Colorpicker</label>
								</div>
								<div class="cmb-td">
									<input type="text" class="cmb2-colorpicker cmb2-text-small" name="group_field[0][colorpicker]" id="group_field_0_colorpicker" value="#"/>
								</div>
							</div>
							<div class="cmb-row cmb-type-text cmb2-id-group-field-0-first-field cmb-repeat-group-field table-layout">
								<div class="cmb-th">
									<label for="group_field_0_first_field">Field 1</label>
								</div>
								<div class="cmb-td"><input type="text" class="regular-text" name="group_field[0][first_field]" id="group_field_0_first_field" value=""/></div>
							</div>
							<div class="cmb-row cmb-remove-field-row">
								<div class="cmb-remove-row">
									<button disabled="disabled" data-selector="group_field_repeat" class="button cmb-remove-group-row alignright">Remove Group</button>
								</div>
							</div>
						</div>
					</div>
					after_group_row output
					<div class="cmb-row">
						<div class="cmb-td">
						<p class="cmb-add-row">
						<button data-selector="group_field_repeat" data-grouptitle="" class="cmb-add-group-row button">Add Group</button>
						</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		after_group output
		';

		ob_start();
		$cmb->render_group( $field->args );
		// grab the data from the output buffer and add it to our $content variable
		$rendered_group = ob_get_clean();

		$this->assertHTMLstringsAreEqual( $expected_group_render, $rendered_group );

	}

	public function test_disable_group_repeat() {

		// Retrieve a CMB2 instance
		$cmb = cmb2_get_metabox( 'test2' );

		$field_id = $cmb->add_field( array(
			'name' => 'group 2',
			'type' => 'group',
			'id'   => 'group_field2',
			'repeatable' => false,
		) );

		$cmb->add_group_field( $field_id, array(
			'name' => 'Field 1',
			'id'   => 'first_field',
			'type' => 'text',
		) );

		$field = $cmb->get_field( 'group_field2' );

		$expected_group_render = '
		<div class="cmb-row cmb-repeat-group-wrap">
			<div class="cmb-td">
				<div id="group_field2_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group non-sortable non-repeatable" style="width:100%;">
					<div class="cmb-row">
						<div class="cmb-th">
							<h2 class="cmb-group-name">group 2</h2>
						</div>
					</div>
					<div class="postbox cmb-row cmb-repeatable-grouping" data-iterator="0">
						<div class="cmbhandle" title="Click to toggle"><br></div>
						<h3 class="cmb-group-title cmbhandle-title"><span></span></h3>
						<div class="inside cmb-td cmb-nested cmb-field-list">
							<div class="cmb-row cmb-type-text cmb2-id-group-field2-0-first-field cmb-repeat-group-field table-layout">
								<div class="cmb-th">
									<label for="group_field2_0_first_field">Field 1</label>
								</div>
								<div class="cmb-td"><input type="text" class="regular-text" name="group_field2[0][first_field]" id="group_field2_0_first_field" value=""/></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		';

		ob_start();
		$cmb->render_group( $field->args() );
		// grab the data from the output buffer and add it to our $content variable
		$rendered_group = ob_get_clean();

		$this->assertHTMLstringsAreEqual( $expected_group_render, $rendered_group );

	}


	public function test_added_group_field() {

		$field = cmb2_get_field( 'test2', 'group_field', $this->post_id );
		$this->assertInstanceOf( 'CMB2_Field', $field );

		$fields = $field->fields();
		$mock_fields = array(
			'colorpicker' => array(
				'name' => 'Colorpicker',
				'id'   => 'colorpicker',
				'type' => 'colorpicker',
			),
			'first_field' => array(
				'name' => 'Field 1',
				'id'   => 'first_field',
				'type' => 'text',
			),
		);
		foreach( $mock_fields as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_remove_group_field() {

		$cmb = cmb2_get_metabox( 'test2' );
		$cmb->remove_field( 'colorpicker', 'group_field' );

		$field = cmb2_get_field( 'test2', 'group_field', $this->post_id );

		$fields = $field->fields();
		$mock_fields = array(
			'first_field' => array(
				'name' => 'Field 1',
				'id'   => 'first_field',
				'type' => 'text',
			),
		);
		foreach( $mock_fields as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_remove_field() {

		$cmb = cmb2_get_metabox( 'test2' );
		$cmb->remove_field( 'group_field' );
		$cmb->remove_field( 'group_field2' );

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
			),
			'demo_text2' => array(
				'name'       => 'Test Text 2',
				'desc'       => 'Test Text 2 description',
				'id'         => 'demo_text2',
				'type'       => 'text',
				'attributes' => array( 'placeholder' => "I'm some placeholder text" ),
			)
		);
		$test_fields = $cmb->prop( 'fields' );
		foreach( $field_array as $field_id => $field_props_array ) {
			foreach( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name] );
			}
		}
	}

	public function test_get_sanitized_values() {
		// Set our object id. Do this to test that it doesn't get broken
		$this->cmb->object_id( $this->post_id );

		// Add another field to test that multiple field sanitized vals will be returned.
		$this->cmb->add_field( array(
			'name' => 'another field',
			'type' => 'textrea',
			'id'   => 'another_field',
		) );

		// add some xss for good measure
		$dirty_val   = 'test<html><stuff><script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>';
		$cleaned_val = sanitize_text_field( $dirty_val );

		// Values to sanitize
		$vals = array(
			'test_test'     => $dirty_val,
			'another_field' => $dirty_val,
		);

		// Expected clean val
		$expected = array(
			'test_test'     => $cleaned_val,
			'another_field' => $cleaned_val
		);

		// Verify sanitization works
		$this->assertEquals( $expected, cmb2_get_metabox_sanitized_values( $this->cmb_id, $vals ) );

		// Then verify that the object id was properly returned.
		$this->assertEquals( $this->post_id, $this->cmb->object_id() );

		$meta_values = get_post_meta( $this->post_id );

		// And verify that the post-meta was not saved to the post
		$this->assertTrue( ! isset( $meta_values['test_test'], $meta_values['another_field'] ) );
	}

	public function test_get_field() {
		$cmb = new CMB2( $this->metabox_array );

		$field = $cmb->get_field( 'test_test' );
		$this->assertInstanceOf( 'CMB2_Field', $field );
	}

	public function test_disable_save_fields() {
		$this->assertTrue( $this->cmb->prop( 'save_fields' ) );
		$args = $this->metabox_array;
		$args['save_fields'] = false;
		$cmb = new CMB2( $args );
		$this->assertFalse( $cmb->prop( 'save_fields' ) );
	}

	public function test_cmb_magic_getters() {

		$cmb = cmb2_get_metabox( 'test' );

		$this->assertEquals( 'test', $cmb->cmb_id );
		$this->assertEquals( array(), $cmb->updated );
		$this->assertEquals( 0, $cmb->object_id );
	}

	/**
	 * @expectedException Test_CMB2_Exception
	 */
	public function test_invalid_cmb_magic_getter() {

		$cmb = cmb2_get_metabox( 'test' );

		try {
			// Calling a non-existent getter property should generate an exception
			$cmb->foo_bar_baz;
		} catch ( Exception $e ) {
			if ( 'Exception' === get_class( $e ) ) {
				throw new Test_CMB2_Exception( $e->getMessage(), $e->getCode() );
			}
		}

	}

	public function test_cmb2_props() {

		$cmb = cmb2_get_metabox( 'test' );

		// Test known state of all props except fields
		$prop_values = array(
			'id'               => 'test',
			'title'            => '',
			'type'             => '',
			'object_types'     => array(),
			'context'          => 'normal',
			'priority'         => 'high',
			'show_names'       => true,
			'show_on_cb'       => null,
			'show_on'          => array(),
			'cmb_styles'       => true,
			'enqueue_js'       => true,
			'hookup'           => true,
			'save_fields'      => true,
			'closed'           => false,
			'new_user_section' => 'add-new-user'
		);
		foreach( $prop_values as $prop_key => $expected_value ) {
			$this->assertEquals( $expected_value, $cmb->prop( $prop_key ) );
		}

		// Test adding a new property
		$new_prop_name   = 'new_prop';
		$new_prop_value  = 'new value';
		$unused_fallback = 'should not be used';

		// Property is unset so the fallback should be used
		$prop_value = $cmb->prop( $new_prop_name, $new_prop_value );
		$this->assertEquals( $new_prop_value, $prop_value);

		// Property is now set so the fallback should not overwrite
		$prop_value = $cmb->prop( $new_prop_name, $unused_fallback );
		$this->assertEquals( $new_prop_value, $prop_value);

		// Test with no fallback specified
		$prop_value = $cmb->prop( $new_prop_name );
		$this->assertEquals( $new_prop_value, $prop_value);

		// The new property should show up in the meta_box array as well
		$prop_value = $cmb->meta_box[ $new_prop_name ];
		$this->assertEquals( $new_prop_value, $prop_value);

	}

}

/**
 * Simply allows access to the mb_defaults protected property (for testing)
 */
class Test_CMB2_Object extends CMB2 {
	public function get_metabox_defaults() {
		return $this->mb_defaults;
	}
}

/**
 * Custom exception class because PHPunit < 3.7 has the following error:
 * "InvalidArgumentException: You must not expect the generic exception class."
 *
 * @link http://stackoverflow.com/a/10744841
 */
class Test_CMB2_Exception extends Exception {
	public function __construct( $message = null, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code );
	}
}