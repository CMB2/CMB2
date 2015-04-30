<?php
/**
 * CMB2_Field tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

class Test_CMB2_Field extends Test_CMB2 {

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
			'after_field'  => 'after_field_static',
			'row_classes'  => array( $this, 'row_classes_array_cb' ),
			'default'      => array( $this, 'cb_to_set_default' ),
		);

		$this->object_id   = $this->post_id;
		$this->object_type = 'post';
		$this->group       = false;

		$this->field = new CMB2_Field( array(
			'object_id'   => $this->object_id,
			'object_type' => $this->object_type,
			'group'       => $this->group,
			'field_args'  => $this->field_args,
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

	public function test_cmb2_row_classes_field_callback_with_array() {
		// Add row classes dynamically with a callback that returns an array
		$classes = $this->field->row_classes();
		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout type name desc before after options_cb options attributes protocols default select_all_button multiple repeatable inline on_front show_names date_format time_format description preview_size id before_field after_field row_classes _id _name', $classes );
	}

	public function test_cmb2_default_field_callback_with_array() {
		// Add row classes dynamically with a callback that returns an array
		$default = $this->field->args( 'default' );
		$this->assertEquals( 'type, name, desc, before, after, options_cb, options, attributes, protocols, default, select_all_button, multiple, repeatable, inline, on_front, show_names, date_format, time_format, description, preview_size, id, before_field, after_field, row_classes, _id, _name', $default );
	}

	public function test_cmb2_row_classes_field_callback_with_string() {

		// Test with string
		$args = $this->field_args;

		// Add row classes dynamically with a callback that returns a string
		$args['row_classes'] = array( $this, 'row_classes_string_cb' );

		$field = new CMB2_Field( array(
			'object_id' => $this->object_id,
			'object_type' => $this->object_type,
			'group' => $this->group,
			'field_args' => $args,
		) );

		$classes = $field->row_classes();

		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout callback with string', $classes );
	}

	public function test_cmb2_row_classes_string() {

		// Test with string
		$args = $this->field_args;

		// Add row classes statically as a string
		$args['row_classes'] = 'these are some classes';

		$field = new CMB2_Field( array(
			'object_id' => $this->object_id,
			'object_type' => $this->object_type,
			'group' => $this->group,
			'field_args' => $args,
		) );

		$classes = $field->row_classes();

		$this->assertEquals( 'cmb-type-text cmb2-id-test-test table-layout these are some classes', $classes );
	}

	public function test_empty_field_with_empty_object_id() {
		$field = new CMB2_Field( array(
			'field_args' => $this->field_args,
		) );

		// data should be empty since we have no object id
		$this->assertEmpty( $field->get_data() );

		// add some xss for good measure
		$dirty_val = 'test<html><stuff><script>xss</script><a href="http://xssattackexamples.com/">Click to Download</a>';
		$cleaned_val = sanitize_text_field( $dirty_val );

		// Make sure it sanitizes as expected
		$this->assertEquals( $cleaned_val, $field->sanitization_cb( $dirty_val ) );

		// Sanitize/store the field
		$this->assertTrue( $field->save_field( $dirty_val ) );

		// Retrieve saved value(s)
		$this->assertEquals( $cleaned_val, cmb2_options( 0 )->get( $field->id() ) );
		$this->assertEquals( array( 'test_test' => $cleaned_val ), cmb2_options( 0 )->get_options() );
	}

	public function test_show_option_none() {
		$args = array(
			'name'             => 'Test Radio inline',
			'desc'             => 'field description (optional)',
			'id'               => 'radio_inline',
			'type'             => 'radio_inline',
			'options'          => array(
				'standard' => 'Option One',
				'custom'   => 'Option Two',
				'none'     => 'Option Three',
			),
		);
		$field = new CMB2_Field( array(
			'field_args' => $args,
		) );

		$this->assertFalse( $field->args( 'show_option_none' ) );

		$this->assertHTMLstringsAreEqual(
			'<div class="cmb-row cmb-type-radio-inline cmb2-id-radio-inline cmb-inline"><div class="cmb-th"><label for="radio_inline">Test Radio inline</label></div><div class="cmb-td"><ul class="cmb2-radio-list cmb2-list"><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline1" value="standard"/><label for="radio_inline1">Option One</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline2" value="custom"/><label for="radio_inline2">Option Two</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline3" value="none"/><label for="radio_inline3">Option Three</label></li></ul><p class="cmb2-metabox-description">field description (optional)</p></div></div>',
			$this->render_field( $field )
		);

		$args['show_option_none'] = true;
		$field = new CMB2_Field( array(
			'field_args' => $args,
		) );

		$this->assertEquals( __( 'None', 'cmb2' ), $field->args( 'show_option_none' ) );

		$this->assertHTMLstringsAreEqual(
			'<div class="cmb-row cmb-type-radio-inline cmb2-id-radio-inline cmb-inline"><div class="cmb-th"><label for="radio_inline">Test Radio inline</label></div><div class="cmb-td"><ul class="cmb2-radio-list cmb2-list"><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline1" value="" checked="checked"/><label for="radio_inline1">None</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline2" value="standard"/><label for="radio_inline2">Option One</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline3" value="custom"/><label for="radio_inline3">Option Two</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline4" value="none"/><label for="radio_inline4">Option Three</label></li></ul><p class="cmb2-metabox-description">field description (optional)</p></div></div>',
			$this->render_field( $field )
		);

		$args['show_option_none'] = 'No Value';
		$field = new CMB2_Field( array(
			'field_args' => $args,
		) );

		$this->assertEquals( 'No Value', $field->args( 'show_option_none' ) );

		$this->assertHTMLstringsAreEqual(
			'<div class="cmb-row cmb-type-radio-inline cmb2-id-radio-inline cmb-inline"><div class="cmb-th"><label for="radio_inline">Test Radio inline</label></div><div class="cmb-td"><ul class="cmb2-radio-list cmb2-list"><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline1" value="" checked="checked"/><label for="radio_inline1">No Value</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline2" value="standard"/><label for="radio_inline2">Option One</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline3" value="custom"/><label for="radio_inline3">Option Two</label></li><li><input type="radio" class="cmb2-option" name="radio_inline" id="radio_inline4" value="none"/><label for="radio_inline4">Option Three</label></li></ul><p class="cmb2-metabox-description">field description (optional)</p></div></div>',
			$this->render_field( $field )
		);

	}

	public function test_multiple_meta_rows() {
		$prefix    = 'testing';
		$post_id   = $this->post_id;
		$array_val = array( 'check1', 'check2' );

		$cmb_demo = cmb2_get_metabox( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Test Metabox', 'cmb2' ),
			'object_types'  => array( 'page', ), // Post type
			'show_on_cb'    => 'yourprefix_show_if_front_page', // function should return a bool value
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true, // Show field names on the left
			// 'cmb_styles' => false, // false to disable the CMB stylesheet
			// 'closed'     => true, // true to keep the metabox closed by default
		), $post_id );

		$field_id = $cmb_demo->add_field( array(
			'name'    => __( 'Test Multi Checkbox', 'cmb2' ),
			'desc'    => __( 'field description (optional)', 'cmb2' ),
			'id'      => $prefix . 'multicheckbox',
			'type'    => 'multicheck',
			'multiple' => true, // Store values in individual rows
			'options' => array(
				'check1' => __( 'Check One', 'cmb2' ),
				'check2' => __( 'Check Two', 'cmb2' ),
				'check3' => __( 'Check Three', 'cmb2' ),
			),
		) );

		$field = $cmb_demo->get_field( $field_id );

		$saved = $field->save_field( $array_val );

		$this->assertEquals( 2, $saved );

		$this->assertEquals( $array_val, $field->get_data() );
		$this->assertEquals( $array_val, get_post_meta( $post_id, $field_id ) );

		$val = get_post_meta( $post_id, $field_id, 1 );
		$this->assertEquals( reset( $array_val ), $val );

	}

	public function test_set_get_filters() {
		// Value to set
		$array_val = array( 'check1', 'check2' );

		// Set the field value normally
		$this->field->save_field( $array_val );

		// Verify that field was saved succesfully to post-meta
		$this->assertEquals( $array_val, get_post_meta( $this->field->object_id, $this->field->_id(), 1 ) );

		// Now delete the post-meta
		delete_post_meta( $this->field->object_id, $this->field->_id() );

		// Verify that the post-meta no longer exists
		$this->assertFalse( !! get_post_meta( $this->field->object_id, $this->field->_id(), 1 ) );

		// Now filter the setting of the meta.. will use update_option instead
		add_filter( "cmb2_override_{$this->field->_id()}_meta_save", array( $this, 'override_set' ), 10, 4 );
		// Set the value
		$this->field->save_field( $array_val );

		// Verify that the post-meta is still empty
		$this->assertFalse( !! get_post_meta( $this->field->object_id, $this->field->_id(), 1 ) );

		// Re-create the option key that we used to save the data
		$opt_key = 'test-'. $this->field->object_id . '-' . $this->field->_id();
		// And retrieve the option
		$opt = get_option( $opt_key );

		// Verify it is the value we set
		$this->assertEquals( $array_val, $opt );

		// Now retireve the value.. will default to getting from post-meta
		$value = $this->field->get_data();

		// Verify that there's still nothing there in post-meta
		$this->assertFalse( !! $value );

		// Now filter the getting of the meta, which will use get_option
		add_filter( "cmb2_override_{$this->field->_id()}_meta_value", array( $this, 'override_get' ), 10, 4 );
		// Get the value
		$value = $this->field->get_data();

		// Verify that the data we retrieved now matches the value we set
		$this->assertEquals( $array_val, $value );
	}

	public function before_field_cb( $args ) {
		echo 'before_field_cb_' . $args['id'];
	}

	public function row_classes_array_cb( $args ) {
		/**
		 * Side benefit: this will call out when default args change
		 */
		return array_keys( $args );
	}

	public function row_classes_string_cb( $args ) {
		return 'callback with string';
	}

	public function cb_to_set_default( $args ) {
		/**
		 * Side benefit: this will call out when default args change
		 */
		return implode( ', ', array_keys( $args ) );
	}

	public function override_set( $override, $args, $field_args, $field ) {
		$opt_key = 'test-'. $args['id'] . '-' . $args['field_id'];
		$updated = update_option( $opt_key, $args['value'] );
		return true;
	}

	public function override_get( $override_val, $object_id, $args, $field ) {
		$opt_key = 'test-'. $args['id'] . '-' . $args['field_id'];
		return get_option( $opt_key );
	}

}
