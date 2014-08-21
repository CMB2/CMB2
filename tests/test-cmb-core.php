<?php

class CMB2_Core_Test extends WP_UnitTestCase {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';
		$this->metabox_array = array(
			'id' => $this->cmb_id,
			'fields' => array(
				array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
				),
			),
		);

		$this->metabox_array2 = array(
			'id' => 'test2',
			'fields' => array(
				array(
					'name' => 'Name',
					'id'   => 'test_test',
					'type' => 'text',
				),
			),
		);

		$this->option_metabox_array = array(
			'id'            => 'options_page',
			'title'         => 'Theme Options Metabox',
			'show_on'    => array( 'key' => 'options-page', 'value' => array( 'theme_options', ), ),
			'fields'        => array(
				array(
					'name'    => 'Site Background Color',
					'desc'    => 'field description (optional)',
					'id'      => 'bg_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
			)
		);

		$this->defaults = array(
			'id'               => $this->cmb_id,
			'title'            => false,
			'type'             => false,
			'object_types'     => array(),
			'context'          => 'normal',
			'priority'         => 'high',
			'show_names'       => 1,
			'cmb_styles'       => 1,
			'fields'           => array(),
			'hookup'           => 1,
			'new_user_section' => 'add-new-user',
			'show_on'          => array(
				'key'   => false,
				'value' => false,
			),
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

	public function test_cmb2_has_version_number() {
		$this->assertTrue( defined( 'CMB2_VERSION' ) );
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_cmb2_die_with_no_id() {
		$cmb = new CMB2( array() );
	}

	/**
	 * @expectedException Exception
	 */
	public function test_set_metabox_after_offlimits() {
		$this->cmb->metabox['title'] = 'title';
	}

	public function test_defaults_set() {
		$cmb = new CMB2( array( 'id' => $this->cmb_id ) );
		$this->assertEquals( $cmb->meta_box, $this->defaults );
	}

	public function test_url_set() {
	  $cmb2_url = str_replace(
			array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
			array( WP_CONTENT_URL, WP_PLUGIN_URL ),
			cmb2_dir()
		);

		$this->assertEquals( cmb2_utils()->url(), $cmb2_url );
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
	}

	public function test_cmb2_get_field() {
		$val_to_save = '123Abc';
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
		$form = '
		<form class="cmb-form" method="post" id="'. $this->cmb_id .'" enctype="multipart/form-data" encoding="multipart/form-data">
			<input type="hidden" name="object_id" value="'. $this->post_id .'">
			'.wp_nonce_field( $this->cmb->nonce(), $this->cmb->nonce(), false, false ) .'
			<!-- Begin CMB Fields -->
			<div class="cmb2_wrap form-table">
				<ul id="cmb2_metabox_'. $this->cmb_id .'" class="cmb2_metabox">
					<li class="cmb-row cmb-type-text cmb2_id_test_test">
						<div class="cmb-th">
							<label for="test_test">Name</label>
						</div>
						<div class="cmb-td">
							<input type="text" class="regular-text" name="test_test" id="test_test" value=""/>
							<p class="cmb2_metabox_description"></p>
						</div>
					</li>
				</ul>
			</div>
			<!-- End CMB Fields -->
			<input type="submit" name="submit-cmb" value="Save" class="button-primary">
		</form>
		';

		$form_get = cmb2_get_metabox_form( $this->cmb_id, $this->post_id );

		$this->assertEquals( $this->clean_string( $form_get ), $this->clean_string( $form ) );
	}

	public function test_cmb2_options() {
		$opts = cmb2_options( $this->options_cmb->cmb_id );
		$this->assertEquals( $opts->get_options(), $this->opt_set );

		$get = get_option( $this->options_cmb->cmb_id );
		$val = cmb2_get_option( $this->options_cmb->cmb_id, 'my_name' );

		$this->assertEquals( $this->opt_set['my_name'], $get['my_name'] );
		$this->assertEquals( $val, $get['my_name'] );
		$this->assertEquals( $val, $this->opt_set['my_name'] );

	}

	public function test_class_getters() {
		$this->assertInstanceOf( 'CMB2_Ajax', cmb2_ajax() );
		$this->assertInstanceOf( 'CMB2_Utils', cmb2_utils() );
		$this->assertInstanceOf( 'CMB2_Option', cmb2_options( 'test' ) );
	}

	public function clean_string( $string ) {
		return trim( str_ireplace( array(
			"\n", "\r", "\t", '  ', '> <',
		), array(
			'', '', '', ' ', '><',
		), $string ) );
	}

}
