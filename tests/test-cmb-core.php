<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-tests-base.php' );

/**
 * @todo Tests for maybe_hook_parameter.
 */
class Test_CMB2_Core extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';
		$this->metabox_array = array(
			'classes' => 'custom-class another-class',
			'classes_cb' => array( __CLASS__, 'custom_classes' ),
			'id' => $this->cmb_id,
			'fields' => array(
				'test_test' => array(
					'name'        => 'Name',
					'description' => 'Description',
					'id'          => 'test_test',
					'type'        => 'text',
					'before_row'  => array( __CLASS__, 'cmb_before_row' ),
					'before'      => 'testing before',
					'after'       => array( __CLASS__, 'cmb_after' ),
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
				),
			),
		);

		$this->term_metabox_array = array(
			'id'           => 'term_metabox',
			'title'        => 'User Profile Metabox',
			'object_types' => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'show_names'   => true,
			'taxonomies'   => 'category', // where form will show on new user page. 'add-existing-user' is only other valid option.
			'fields'       => array(
				'extra_info' => array(
					'name'     => 'Extra Info',
					'desc'     => 'field description (optional)',
					'id'       => 'extra_info',
					'type'     => 'title',
					'on_front' => false,
				),
			),
		);

		$this->defaults = array(
			'id'               => $this->cmb_id,
			'title'            => '',
			'object_types'     => array(), // Post type
			'context'          => 'normal',
			'priority'         => 'high',
			'show_names'       => true, // Show field names on the left
			'show_on'          => array(), // Specific post IDs or page templates to display this metabox
			'show_on_cb'       => null, // Callback to determine if metabox should display. Overrides 'show_on'
			'cmb_styles'       => true, // Include cmb bundled stylesheet
			'enqueue_js'       => true, // Include CMB2 JS
			'fields'           => array(),
			'hookup'           => true,
			'show_in_rest'     => false,
			'save_fields'      => true, // Will not save during hookup if false
			'closed'           => false, // Default to metabox being closed?
			'taxonomies'       => array(),
			'new_user_section' => 'add-new-user', // or 'add-existing-user'
			'new_term_section' => true,
			'classes'          => null,
			'classes_cb'       => '',
			'remove_box_wrap'  => false,
			'parent_slug'      => '',
			'capability'       => 'manage_options',
			'icon_url'         => '',
			'position'         => null,
			'admin_menu_hook'  => 'admin_menu',
			'display_cb'       => false,
			'save_button'      => '',
			'message_cb'       => '',
			'option_key'       => '',
			'disable_settings_errors' => false, // On settings pages (not options-general.php sub-pages), allows disabling.
			'tab_group'        => '',
		);

		$this->cmb = new CMB2( $this->metabox_array );

		$this->post_id = $this->factory->post->create();

		$this->term_id = $this->factory->term->create( array(
			'taxonomy' => 'category',
			'name' => 'save_term',
		) );
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
	public function test_setting_protected_property() {
		try {
			// Fyi you don't need to do an assert test here, as we are only testing the exception, so just make the call
			$this->cmb->metabox['title'] = 'title';
		} catch ( Exception $e ) {
			if ( 'Exception' === get_class( $e ) ) {
				throw new Test_CMB2_Exception( $e->getMessage(), $e->getCode() );
			}
		}
	}

	public function test_cmb2_init_hook() {
		$this->assertTrue( (bool) did_action( 'cmb2_init' ) );
	}

	public function test_cmb2_admin_init_hook() {
		$this->assertTrue( (bool) did_action( 'cmb2_admin_init' ) );
	}

	public function test_id_get() {
		$cmb = new CMB2( array(
			'id' => $this->cmb_id,
		) );
		$this->assertEquals( $cmb->cmb_id, $this->cmb_id );
	}

	public function test_defaults_set() {
		$cmb = new CMB2( array(
			'id' => $this->cmb_id,
		) );

		$this->assertEquals( $this->defaults, $cmb->meta_box, print_r( array( $this->defaults, $cmb->meta_box ), true ) );
	}

	public function test_cmb2_get_metabox() {
		// Test that successful retrieval by box ID
		$retrieve = cmb2_get_metabox( $this->cmb_id );
		$this->assertEquals( $this->cmb, $retrieve );

		// Test that successful retrieval by box Array
		$cmb = cmb2_get_metabox( $this->metabox_array );
		$this->assertEquals( $this->cmb, $cmb );

		CMB2_Boxes::remove( 'test2' );
		// Test successful creation of new MB
		$cmb1 = cmb2_get_metabox( $this->metabox_array2 );
		$cmb2 = new CMB2( $this->metabox_array2 );
		$this->assertEquals( $cmb1, $cmb2 );

		$cmb_user = cmb2_get_metabox( $this->user_metabox_array );
		$this->assertEquals( 'user', $cmb_user->mb_object_type() );

		$cmb_term = cmb2_get_metabox( $this->term_metabox_array );
		$this->assertEquals( 'term', $cmb_term->mb_object_type() );
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
			<!-- Begin CMB2 Fields -->
			' . wp_nonce_field( $this->cmb->nonce(), $this->cmb->nonce(), false, false ) . '
			<div class="cmb2-wrap form-table callback-class ' . $this->cmb_id . ' custom-class another-class filter-class custom-class-another-class">
				<div id="cmb2-metabox-' . $this->cmb_id . '" class="cmb2-metabox cmb-field-list">
					function test_before_row Description test_test
					<div class="cmb-row cmb-type-text cmb2-id-test-test table-layout" data-fieldtype="text">
						<div class="cmb-th">
							<label for="test_test">Name</label>
						</div>
						<div class="cmb-td">
							testing before
							<input type="text" class="regular-text" name="test_test" id="test_test" value="" data-hash=\'7i3hkvqmp7v0\'/>
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

		add_filter( 'cmb2_wrap_classes', array( __CLASS__, 'custom_classes_filter' ), 10, 2 );
		$form_get = cmb2_get_metabox_form( $this->cmb_id, $this->post_id );
		remove_filter( 'cmb2_wrap_classes', array( __CLASS__, 'custom_classes_filter' ), 10, 2 );

		$this->assertHTMLstringsAreEqual( $expected_form, $form_get );
	}

	public function test_class_getters() {
		$this->assertInstanceOf( 'CMB2_Ajax', cmb2_ajax() );
		$this->assertInstanceOf( 'CMB2_Utils', cmb2_utils() );
		$this->assertInstanceOf( 'CMB2_Option', cmb2_options( 'test' ) );
	}

	public function test_boxes_get_all() {
		$this->assertContainsOnlyInstancesOf( 'CMB2', CMB2_Boxes::get_all() );
	}

	public function test_boxes_get_by() {
		$boxes = CMB2_Boxes::get_by( 'classes', 'custom-class another-class' );
		$this->assertContainsOnlyInstancesOf( 'CMB2', $boxes );
		$this->assertSame( 1, count( $boxes ) );
	}

	public function test_boxes_filter_by() {
		$all   = CMB2_Boxes::get_all();
		$with  = CMB2_Boxes::get_by( 'classes', 'custom-class another-class' );
		$boxes = CMB2_Boxes::filter_by( 'classes', 'custom-class another-class' );
		$this->assertContainsOnlyInstancesOf( 'CMB2', $boxes );
		$this->assertSame( count( $all ) - count( $with ), count( $boxes ) );
	}

	public function test_boxes_get() {
		new Test_CMB2_Object( $this->metabox_array2 );

		// Retrieve the instance
		$cmb = $this->get_test2_box();

		$after_args_parsed = wp_parse_args( $this->metabox_array2, $cmb->get_metabox_defaults() );
		foreach ( $after_args_parsed as $key => $value ) {
			// Field are tested separately, below
			if ( 'fields' != $key ) {
				$this->assertEquals( $value, $cmb->meta_box[ $key ] );
			}
		}

		foreach ( $after_args_parsed['fields'] as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $cmb->meta_box['fields'][ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_update_field_property() {
		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

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
		$cmb = $this->get_test2_box();
		$this->assertInstanceOf( 'CMB2', $cmb );

		$field = cmb2_get_field( $cmb, 'test_test', $this->post_id );

		$this->assertEquals( 'textarea', $field->type() );
		$this->assertEquals( array(
			'placeholder' => "I'm some placeholder text",
		), $field->attributes() );

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array(
					'placeholder' => "I'm some placeholder text",
				),
			),
		);
		$test_fields = $cmb->prop( 'fields' );
		foreach ( $field_array as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_add_field() {

		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

		// This should return false because we don't have a 'demo_text2' field
		$field_id = $cmb->update_field_property( 'demo_text2', 'type', 'text' );
		$this->assertFalse( $field_id );

		$field_id = $cmb->add_field( array(
			'name'       => 'Test Text 2',
			'desc'       => 'Test Text 2 description',
			'id'         => 'demo_text2',
			'type'       => 'text',
			'attributes' => array(
				'placeholder' => "I'm some placeholder text",
			),
		) );

		$this->assertEquals( 'demo_text2', $field_id );
	}

	public function test_added_field() {

		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array(
					'placeholder' => "I'm some placeholder text",
				),
			),
			'demo_text2' => array(
				'name'       => 'Test Text 2',
				'desc'       => 'Test Text 2 description',
				'id'         => 'demo_text2',
				'type'       => 'text',
				'attributes' => array(
					'placeholder' => "I'm some placeholder text",
				),
			),
		);
		$test_fields = $cmb->prop( 'fields' );

		foreach ( $field_array as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_add_group_field( $do_assertions = null ) {

		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

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
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			$this->assertTrue( true );
			return;
		}

		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

		$field_id = $cmb->update_field_property( 'group_field', 'before_group', 'before_group output' );
		$field_id = $cmb->update_field_property( 'group_field', 'options', array(
			'closed' => true,
		) );

		$this->assertTrue( ! empty( $field_id ) );

		$cmb->update_field_property( 'group_field', 'before_group_row', 'before_group_row output' );
		$cmb->update_field_property( 'group_field', 'after_group_row', 'after_group_row output' );
		$cmb->update_field_property( 'group_field', 'after_group', 'after_group output' );

		$sub_field_id = $cmb->add_group_field( $field_id, array(
			'name' => 'Name',
			'id'   => 'test_file',
			'type' => 'file',
		) ); // Test that the position argument is working

		$all_fields = $cmb->prop( 'fields' );
		$field = $cmb->get_field( $all_fields['group_field'] );

		$expected_group_render = '
		before_group output
		<div class="cmb-row cmb-repeat-group-wrap cmb-type-group cmb2-id-group-field cmb-repeat" data-fieldtype="group">
			<div class="cmb-td">
				<div data-groupid="group_field" id="group_field_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group non-sortable repeatable" style="width:100%;">
					<div class="cmb-row cmb-group-description">
						<div class="cmb-th">
							<h2 class="cmb-group-name">Group</h2>
							<p class="cmb2-metabox-description">Group description</p>
						</div>
					</div>
					before_group_row output
					<div class="postbox cmb-row cmb-repeatable-grouping closed" data-iterator="0">
						<button type="button" data-selector="group_field_repeat" class="dashicons-before dashicons-no-alt cmb-remove-group-row" title="Remove Group"></button>
						<div class="cmbhandle" title="Click to toggle"><br></div>
						<h3 class="cmb-group-title cmbhandle-title"><span></span></h3>
						<div class="inside cmb-td cmb-nested cmb-field-list">
							<div class="cmb-row cmb-type-colorpicker cmb2-id-group-field-0-colorpicker cmb-repeat-group-field" data-fieldtype="colorpicker">
								<div class="cmb-th">
									<label for="group_field_0_colorpicker">Colorpicker</label>
								</div>
								<div class="cmb-td">
									<input type="text" class="cmb2-text-small cmb2-colorpicker" name="group_field[0][colorpicker]" id="group_field_0_colorpicker" value="#" data-hash=\'2jqpbm4qpv9g\'/>
								</div>
							</div>
							<div class="cmb-row cmb-type-text cmb2-id-group-field-0-first-field cmb-repeat-group-field table-layout" data-fieldtype="text">
								<div class="cmb-th">
									<label for="group_field_0_first_field">Field 1</label>
								</div>
								<div class="cmb-td"><input type="text" class="regular-text" name="group_field[0][first_field]" id="group_field_0_first_field" value="" data-hash=\'6ju7p2ui96b0\'/></div>
							</div>
							<div class="cmb-row cmb-type-file cmb2-id-group-field-0-test-file cmb-repeat-group-field" data-fieldtype="file">
								<div class="cmb-th">
									<label for="group_field_0_test_file">Name</label>
								</div>
								<div class="cmb-td">
									<input type="text" class="cmb2-upload-file regular-text" name="group_field[0][test_file]" id="group_field_0_test_file" value="" size="45" data-previewsize=\'[350,350]\' data-sizename=\'large\' data-queryargs=\'\' data-hash=\'2fvuslh3crdg\'/>
									<input class="cmb2-upload-button button-secondary" type="button" value="Add or Upload File" />
									<input type="hidden" class="cmb2-upload-file-id" name="group_field[0][test_file_id]" id="group_field_0_test_file_id" value=""/>
									<div id="group_field_0_test_file-status" class="cmb2-media-status">
									</div>
								</div>
							</div>
							<div class="cmb-row cmb-remove-field-row">
								<div class="cmb-remove-row">
									<button type="button" data-selector="group_field_repeat" class="cmb-remove-group-row cmb-remove-group-row-button alignright button-secondary">Remove Group</button>
								</div>
							</div>
						</div>
					</div>
					after_group_row output
					<div class="cmb-row">
						<div class="cmb-td">
						<p class="cmb-add-row">
						<button type="button" data-selector="group_field_repeat" data-grouptitle="" class="cmb-add-group-row button-secondary">Add Group</button>
						</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		after_group output
		';

		ob_start();
		$cmb->render_group( $field );
		$this->assertHTMLstringsAreEqual( $expected_group_render, ob_get_clean() );

		// Test after modifying the cmb2_group_wrap_attributes filter.
		add_filter( 'cmb2_group_wrap_attributes', array( __CLASS__, 'modify_group_attributes' ) );

		$updated_expected_render = str_replace(
			'style="width:100%;"',
			'style="width:100%;" modify_group_attributes="modify_group_attributes"',
			$expected_group_render
		);

		ob_start();
		$cmb->render_group( $field );
		// The render will not be updated yet...
		$this->assertNotSame( $updated_expected_render, ob_get_clean() );

		// Because the cache for that callback needs to be dumped.
		$field->unset_param_callback_cache( 'render_row_cb' );

		ob_start();
		$cmb->render_group( $field );
		// Now it should match.
		$this->assertHTMLstringsAreEqual( $updated_expected_render, ob_get_clean() );

		remove_filter( 'cmb2_group_wrap_attributes', array( __CLASS__, 'modify_group_attributes' ) );

		// Test replacing default group render_row_cb.
		$cmb->update_field_property( 'group_field', 'render_row_cb', array( __CLASS__, 'echo_field_id' ) );

		ob_start();
		$cmb->render_group( $cmb->get_field( 'group_field', null, true ) );
		// Now it should match.
		$this->assertHTMLstringsAreEqual( 'group_field', ob_get_clean() );

		// Test using a proxy for the default group render callback.
		$cmb->update_field_property( 'group_field', 'render_row_cb', array( __CLASS__, 'do_default_cmb_group_render_cb' ) );

		ob_start();
		$cmb->render_group( $cmb->get_field( 'group_field', null, true ) );
		// Should match the default output.
		$this->assertHTMLstringsAreEqual( $expected_group_render, ob_get_clean() );
	}

	public function test_disable_group_repeat() {

		// Retrieve a CMB2 instance
		$cmb = $this->get_test2_box();

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
		<div class="cmb-row cmb-repeat-group-wrap cmb-type-group cmb2-id-group-field2" data-fieldtype="group">
			<div class="cmb-td">
				<div data-groupid="group_field2" id="group_field2_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group non-sortable non-repeatable" style="width:100%;">
					<div class="cmb-row">
						<div class="cmb-th">
							<h2 class="cmb-group-name">group 2</h2>
						</div>
					</div>
					<div class="postbox cmb-row cmb-repeatable-grouping" data-iterator="0">
						<div class="cmbhandle" title="Click to toggle"><br></div>
						<h3 class="cmb-group-title cmbhandle-title"><span></span></h3>
						<div class="inside cmb-td cmb-nested cmb-field-list">
							<div class="cmb-row cmb-type-text cmb2-id-group-field2-0-first-field cmb-repeat-group-field table-layout" data-fieldtype="text">
								<div class="cmb-th">
									<label for="group_field2_0_first_field">Field 1</label>
								</div>
								<div class="cmb-td"><input type="text" class="regular-text" name="group_field2[0][first_field]" id="group_field2_0_first_field" value="" data-hash=\'4nhr1ugfjlb0\'/></div>
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
		foreach ( $mock_fields as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_remove_group_field() {

		$cmb = $this->get_test2_box();
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
		foreach ( $mock_fields as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value, $fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_remove_field() {

		$cmb = $this->get_test2_box();
		$cmb->remove_field( 'group_field' );
		$cmb->remove_field( 'group_field2' );

		$field_array = array(
			'test_test' => array(
				'name'       => 'Test Name',
				'id'         => 'test_test',
				'type'       => 'textarea',
				'attributes' => array(
					'placeholder' => "I'm some placeholder text",
				),
			),
			'demo_text2' => array(
				'name'       => 'Test Text 2',
				'desc'       => 'Test Text 2 description',
				'id'         => 'demo_text2',
				'type'       => 'text',
				'attributes' => array(
					'placeholder' => "I'm some placeholder text",
				),
			),
		);
		$test_fields = $cmb->prop( 'fields' );
		foreach ( $field_array as $field_id => $field_props_array ) {
			foreach ( $field_props_array as $prop_name => $prop_value ) {
				$this->assertEquals( $prop_value,  $test_fields[ $field_id ][ $prop_name ] );
			}
		}
	}

	public function test_cmb2_get_metabox_sanitized_values() {
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
			'another_field' => $cleaned_val,
		);

		// Verify sanitization works
		$this->assertEquals( $expected, cmb2_get_metabox_sanitized_values( $this->cmb_id, $vals ) );

		// Then verify that the object id was properly returned.
		$this->assertEquals( $this->post_id, $this->cmb->object_id() );

		$meta_values = get_post_meta( $this->post_id );

		// And verify that the post-meta was not saved to the post
		$this->assertTrue( ! isset( $meta_values['test_test'], $meta_values['another_field'] ) );
	}

	public function test_get_sanitized_values() {
		$cmb = new CMB2( array(
			'id' => __FUNCTION__,
			'fields' => array(
				array(
					'id'   => 'test_test',
					'type' => 'text',
				),
				array(
					'id'       => 'test_tax',
					'type'     => 'taxonomy_multicheck',
					'taxonomy' => 'category',
				),
			),
		) );

		$term = get_term_by( 'id', $this->term_id, 'category' );

		$value = array(
			'nope' => 'nope',
			'remove' => array( 'remove', 'this' ),
			'test_test' => 'A value',
			'test_tax' => array(
				$term->slug
			),
		);

		$sanitized = $cmb->get_sanitized_values( $value );

		$expected = array(
			'test_test' => 'A value',
			'test_tax' => array(
				$term->slug
			),
		);

		$this->assertEquals( $expected, $sanitized );
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

	/**
	 * @expectedException Test_CMB2_Exception
	 */
	public function test_invalid_cmb_magic_call() {

		$cmb = cmb2_get_metabox( 'test' );

		try {
			// Calling a non-existent method should generate an exception
			$cmb->foo_bar_baz();
		} catch ( Exception $e ) {
			if ( 'Exception' === get_class( $e ) ) {
				throw new Test_CMB2_Exception( $e->getMessage(), $e->getCode() );
			}
		}

	}

	public function test_overloaded_cmb_method() {

		$cmb = cmb2_get_metabox( 'test' );

		add_action( 'cmb2_inherit_fabulous', array( __CLASS__, 'overloading_test' ), 10, 2 );

		$this->assertEquals( 'Fabulous hair', $cmb->fabulous( 'hair' ) );
		$this->assertObjectHasAttribute( 'fabulous_noun', $cmb );
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
			'new_user_section' => 'add-new-user',
		);
		foreach ( $prop_values as $prop_key => $expected_value ) {
			$this->assertEquals( $expected_value, $cmb->prop( $prop_key ) );
		}

		// Test adding a new property
		$new_prop_name   = 'new_prop';
		$new_prop_value  = 'new value';
		$unused_fallback = 'should not be used';

		// Property is unset so the fallback should be used
		$prop_value = $cmb->prop( $new_prop_name, $new_prop_value );
		$this->assertEquals( $new_prop_value, $prop_value );

		// Property is now set so the fallback should not overwrite
		$prop_value = $cmb->prop( $new_prop_name, $unused_fallback );
		$this->assertEquals( $new_prop_value, $prop_value );

		// Test with no fallback specified
		$prop_value = $cmb->prop( $new_prop_name );
		$this->assertEquals( $new_prop_value, $prop_value );

		// The new property should show up in the meta_box array as well
		$prop_value = $cmb->meta_box[ $new_prop_name ];
		$this->assertEquals( $new_prop_value, $prop_value );

		// Test updating property
		$new_prop_value = $cmb->set_prop( $new_prop_name, $unused_fallback );
		$this->assertEquals( $unused_fallback, $new_prop_value );
		$this->assertEquals( $new_prop_value, $cmb->prop( $new_prop_name ) );

		// Reset value
		$prop_value = $cmb->set_prop( $new_prop_name, $new_prop_value );
	}

	public function test_group_wrap_attributes() {
		$cmb = $this->new_group_box( __FUNCTION__ );
		$field_group = $cmb->get_field( 'group_field' );

		$expected = array(
			'',
			'class="cmb-nested cmb-field-list cmb-repeatable-group non-sortable repeatable"',
			'style="width:100%;"',
		);

		$this->assertEquals( implode( ' ', $expected ), $cmb->group_wrap_attributes( $field_group ) );

		$this->json = '{"glossary": {"title": "example glossary","GlossDiv": {"title": "S","GlossList": {"GlossEntry": {"ID": "SGML","SortAs": "SGML","GlossTerm": "Standard Generalized Markup Language","Acronym": "SGML","Abbrev": "ISO 8879:1986","GlossDef": {"para": "A meta-markup language, used to create markup languages such as DocBook.","GlossSeeAlso": ["GML", "XML"]},"GlossSee": "<script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>"}}}}}';

		add_filter( 'cmb2_group_wrap_attributes', array( $this, 'for_testing_cmb2_group_wrap_attributes' ) , 10, 2 );

		$clean_json = str_replace(
			'<script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>',
			'xssClick to Download',
			$this->json
		);

		$expected[] = 'heyo="it\'s Zao"';
		$expected[] = 'data-json=\'' . $clean_json . '\'';
		$expected[] = 'scriptxssscriptahrefhttpxssattackexamplescomClicktoDownloada="hackers"';
		$expected[] = 'hackers="&quot;&gt;&lt;script&gt;xss&lt;/script&gt;&lt;a href=&quot;http://xssattackexamples.com/&quot;&gt;Click to Download&lt;/a&gt;"';

		$this->assertHTMLstringsAreEqual( implode( ' ', $expected ), $cmb->group_wrap_attributes( $field_group ) );
	}

	public function for_testing_cmb2_group_wrap_attributes( $group_wrap_atts, $field_group ) {
		$group_wrap_atts['heyo'] = "it's Zao";
		$group_wrap_atts['data-json'] = $this->json;

		$group_wrap_atts['"><script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>'] = 'hackers';
		$group_wrap_atts['hackers'] = '"><script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>';

		return $group_wrap_atts;
	}

	public static function overloading_test( $cmb2, $noun = '' ) {
		$cmb2->fabulous_noun = $noun;
		return 'Fabulous ' . $noun;
	}

	public static function custom_classes( $cmb ) {
		return array( 'callback-class', $cmb->cmb_id );
	}

	public static function custom_classes_filter( $classes, $cmb ) {
		$classes[] = 'filter-class';
		$classes[] = sanitize_title_with_dashes( $cmb->prop( 'classes' ) );
		return $classes;
	}

	public static function modify_group_attributes( $atts ) {
		$atts['modify_group_attributes'] = 'modify_group_attributes';
		return $atts;
	}

	public static function echo_field_id( $field_args, $field ) {
		echo $field->id();
	}

	public static function do_default_cmb_group_render_cb( $field_args, $field ) {
		$cmb = $field->get_cmb();
		return $cmb->render_group_callback( $field_args, $field );
	}

	public static function cmb_before_row( $field_args, $field ) {
		echo 'function test_before_row ' . $field_args['description'] . ' ' . $field->id();
	}

	public static function cmb_after( $field_args, $field ) {
		echo 'function test_after ' . $field_args['description'] . ' ' . $field->id();
	}

	protected function new_group_box( $id ) {
		$cmb = cmb2_get_metabox( array(
			'id'              => $id,
			'title'           => $id,
			'object_types'    => array( 'post' ),
		), $this->post_id, 'post' );

		$field_id = $cmb->add_field( array(
			'name' => 'Group',
			'desc' => 'Group description',
			'id'   => 'group_field',
			'type' => 'group',
		) );

		$sub_field_id = $cmb->add_group_field( $field_id, array(
			'name' => 'Field 1',
			'id'   => 'first_field',
			'type' => 'text',
		) );

		$sub_field_id = $cmb->add_group_field( $field_id, array(
			'name' => 'Colorpicker',
			'id'   => 'colorpicker',
			'type' => 'colorpicker',
		) );

		return $cmb;
	}

	protected function get_test2_box() {
		return cmb2_get_metabox( $this->metabox_array2 );
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
