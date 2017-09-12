<?php

/**
 * Class Test_CMB2_Page_Display
 *
 * Tests: CMB2_Page_Display
 *
 * Method/Action                              Tested by:
 * -----------------------------------------------------------------------------------------------------------------
 * public __construct()                       test_invalid_CMB2PageDisplay_no_params()                 1
 * "                                          test_CMB2PageDisplay_construct_and_get()                 4
 * public render()                            test_protected_CMB2PageDisplay_render()                  2
 * protected maybe_output_settings_notices()
 * protected merge_default_args()             test_protected_CMB2PageDisplay_merge_default_args()      4
 * protected merge_inserted_args()            test_protected_CMB2PageDisplay_merge_inserted_args()     4
 * protected page_html()                      test_CMB2PageDisplay_page_html()                         5
 * protected page_form()                      test_protected_CMB2PageDisplay_page_form()               5
 * protected page_form_post()                 test_protected_CMB2PageDisplay_page_form_post()          5
 * protected page_form_post_sidebar()         test_protected_CMB2PageDisplay_page_form_post_sidebar()  4
 * protected page_form_post_nonces()          test_protected_CMB2PageDisplay_page_form_post_nonces()   3
 * protected save_button()                    test_protected_CMB2PageDisplay_save_button()             6
 * magic __get()                              test_CMB2PageDisplay_construct_and_get()                 -
 * apply filter 'cmb2_options_page_before'    test_CMB2PageDisplay_page_html()                         -
 * apply filter 'cmb2_options_page_after'     "                                                        -
 * apply filter 'cmb2_options_form_id'        test_protected_CMB2PageDisplay_page_form()               -
 * apply filter 'cmb2_options_form_top'       "                                                        -
 * apply filter 'cmb2_options_form_bottom'    "                                                        -
 * apply filter 'cmb2_options_page_save_html' test_protected_CMB2PageDisplay_save_button()             -
 * -----------------------------------------------------------------------------------------------------------------
 * 17 Tested                                  11 Tests                                  Assertions:   43
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Display extends Test_CMB2_Options_Base {
	
	protected $test_default = array();
	
	public function setUp() {
		
		parent::setUp();
		
		$this->test_default = array(
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'option_key'     => '',
			'page_format'    => 'simple',
			'simple_action'  => 'cmb2_options_simple_page',
			'page_nonces'    => TRUE,
			'page_columns'   => 1,
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'save_button'    => 'Save',
			'reset_button'   => '',
			'button_wrap'    => TRUE,
			'title'          => '',
			'page_id'        => '',
		);
	}
	
	public function tearDown() {
		
		$this->clear_test_properties();
		parent::tearDown();
	}
	
	/**
	 * @expectedException \TypeError
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group method_public
	 * @group cmb2_page_display
	 */
	public function test_invalid_CMB2PageDisplay_no_params() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page_Display();
	}
	
	/**
	 * CMB2_Page_Display->__get( $property ) should return values for 'option_key', 'page', 'shared'
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_magic
	 * @group cmb2_page_display
	 */
	public function test_CMB2PageDisplay_construct_and_get() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		/*
		 * Should create an instance and default args should match expect
		 */
		
		$expect = array(
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'disable_settings_errors' => false,
			'option_key'     => $opt,
			'page_format'    => 'simple',
			'simple_action'  => 'cmb2_options_simple_page',
			'page_nonces'    => TRUE,
			'page_columns'   => 1,
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'save_button'    => 'Save',
			'reset_button'   => '',
			'button_wrap'    => TRUE,
			'title'          => 'Test Box',
			'page_id'        => $opt,
		);
		
		$this->assertInstanceOf( 'CMB2_Page_Display', $DISPLAY );
		$this->assertEquals( $expect, $DISPLAY->default_args );
		$this->assertEquals( $HOOKUP->page, $DISPLAY->page );
		
		/*
		 * Trying to get a non-existent prop should return null
		 */
		
		$this->assertNull( $DISPLAY->something );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->merge_default_args( $option_key = '', $page = '', $shared = array() )
	 *
	 * Used by the constructor to merge any passed properties into the defaults needed by the HTML
	 * generating methods. Note that only suitable 'shared' arguments can be passed into method.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_merge_default_args() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$expect = array(
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'disable_settings_errors' => false,
			'option_key'     => $opt,
			'page_format'    => 'simple',
			'simple_action'  => 'cmb2_options_simple_page',
			'page_nonces'    => TRUE,
			'page_columns'   => 1,
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'save_button'    => 'Save',
			'reset_button'   => '',
			'button_wrap'    => TRUE,
			'title'          => 'Test Box',
			'page_id'        => $opt,
		);
		
		/*
		 * Calling without params should see constructor arguments used
		 */
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_default_args' );
		
		$this->assertEquals( $expect, $test );
		
		/*
		 * Inserting option key and page should see values reflected in resulting array
		 */
		
		$expect_optkey               = $expect;
		$expect_optkey['option_key'] = 'howdy';
		$expect_optkey['page_id']    = 'doody';
		
		$insert = array(
			'option_key' => 'howdy',
			'page_id'    => 'doody',
		);
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_default_args', $insert );
		$this->assertEquals( $expect_optkey, $test );
		
		/*
		 * Inserting non strings to either of first two args above will see them ignored
		 */
		
		$insert = array(
			'option_key' => array( 'howdy' ),
			'page_id'    => 123,
		);
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_default_args', $insert );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Sending extra properties, or properties whose type doesn't match, see them ignored
		 */
		
		$insert                        = array(
			'page_columns' => 'hi',
			'another'      => 'test',
			'page_format'  => 66,
			'reset_button' => array( 12 ),
			'save_button'  => 'By the Bell',
			'title'        => TRUE,
		);
		$expect_garbage                = $expect;
		$expect_garbage['save_button'] = 'By the Bell';
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_default_args', $insert );
		$this->assertEquals( $expect_garbage, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * Merges arguments inserted in rendering methods with the default arguments array. As with all merges in this
	 * class, strict type-checking of vars is employed and new keys cannot be introduced.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_merge_inserted_args() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$default = array(
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'disable_settings_errors' => false,
			'option_key'     => $opt,
			'page_format'    => 'simple',
			'simple_action'  => 'cmb2_options_simple_page',
			'page_nonces'    => TRUE,
			'page_columns'   => 1,
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'save_button'    => 'Save',
			'reset_button'   => '',
			'button_wrap'    => TRUE,
			'title'          => 'Test Box',
			'page_id'        => $opt,
		);
		
		/*
		 * No params should return defaults
		 */
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_inserted_args' );
		$this->assertEquals( $default, $test );
		
		/*
		 * Change some args
		 */
		
		$expect                           = $default;
		$expect['simple_action']          = 'test';
		$expect['page_id']                = 'test';
		$expect['page_metaboxes']['side'] = 'test';
		
		$insert = array(
			'page_id'        => 'test',
			'page_metaboxes' => array( 'side' => 'test' ),
			'simple_action'  => 'test',
		);
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_inserted_args', $insert );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Type checking is active
		 */
		
		$insert['button_wrap'] = 99;
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_inserted_args', $insert );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Method can take two arbitrary arrays, first will be merged with second, no new keys, type checking
		 */
		
		$old    = array(
			'one' => 'test',
			'two' => 'another',
		);
		$new    = array(
			'one'   => 'replaced',
			'two'   => 11,
			'three' => 'jam',
		);
		$expect = array(
			'one' => 'replaced',
			'two' => 'another',
		);
		
		$test = $this->invokeMethod( $DISPLAY, 'merge_inserted_args', $new, $old );
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->page_html( $inserted_args = array() )
	 *
	 * Method called to get option page HTML. This tests HTML and filters directly generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *   CMB2_Page_Display->page_form()
	 *
	 * Filters applied within this method:
	 *   'cmb2_options_page_before'
	 *   'cmb2_options_page_after'
	 *
	 * Inserted args which can affect HTML:
	 *   'option_key'
	 *   'page'
	 *   'title'
	 *   'page_format'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_CMB2PageDisplay_page_html() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$insert_simple          = array(
			'option_key'  => 'opttest',
			'page'        => 'pagetest',
			'title'       => 'Test Title',
			'page_format' => 'simple',
		);
		$insert_simple_no_title = array(
			'option_key'  => 'opttest',
			'page'        => 'pagetest',
			'title'       => '',
			'page_format' => 'simple',
		);
		$insert_post            = array(
			'option_key'  => 'opttest',
			'page'        => 'pagetest',
			'title'       => 'Test Title',
			'page_format' => 'post',
		);
		
		$expect_open_simple   = '<div class="wrap cmb2-options-page options-opttest">';
		$expect_open_post     = '<div class="wrap options-opttest">';
		$expect_close         = '</div>';
		$expect_title         = '<h1 class="wp-heading-inline">Test Title</h1>';
		$expect_filter_before = '<p>Before form.</p>';
		$expect_filter_after  = '<p>After form.</p>';
		
		/*
		 * Calling page with simple format and title
		 */
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_html', $insert_simple );
		$form   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_simple );
		$expect = $expect_open_simple . $expect_title . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Calling page with simple format and no title
		 */
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_html', $insert_simple_no_title );
		$form   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_simple_no_title );
		$expect = $expect_open_simple . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Calling page with post format and title
		 */
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_html', $insert_post );
		$form   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		$expect = $expect_open_post . $expect_title . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Adding content before the form via filter
		 */
		
		$before = function ( $html ) {
			
			return $html . '<p>Before form.</p>';
		};
		
		add_filter( 'cmb2_options_page_before', $before );
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_html', $insert_post );
		$form   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		$expect = $expect_open_post . $expect_title . $expect_filter_before . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Adding content after form via filter, 'before' filter is still active
		 */
		
		$after = function ( $html ) {
			
			return $html . '<p>After form.</p>';
		};
		
		add_filter( 'cmb2_options_page_after', $after );
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_html', $insert_post );
		$form   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		$expect = $expect_open_post . $expect_title . $expect_filter_before . $form
		          . $expect_filter_after . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->page_form( $inserted_args = array() )
	 *
	 * Method called internally to get the page form. This tests HTML and filters directly generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *   CMB2_Utils::do_void_action()
	 *   CMB2_Page_Display->page_form_post()
	 *   CMB2_Page_Display->save_button()
	 *
	 * Filters applied within this method:
	 *   'cmb2_options_form_id'
	 *   'cmb2_options_form_top'
	 *   'cmb2_options_form_bottom'
	 *
	 * Inserted args which can affect HTML:
	 *   'option_key'
	 *   'page'
	 *   'page_format'
	 *   'simple_action'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_page_form() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$insert_simple = array(
			'option_key'    => 'opttest',
			'page'          => 'pagetest',
			'page_format'   => 'simple',
			'simple_action' => 'display_simple_test_action',
		);
		$insert_post   = array(
			'option_key'    => 'opttest',
			'page'          => 'pagetest',
			'page_format'   => 'post',
			'simple_action' => '',
		);
		
		$admin_url = esc_url( admin_url( 'admin-post.php' ) );
		
		$expect_open          = '<form action="' . $admin_url . '" method="POST" id="cmb2-option-opttest" '
		                        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		$expect_open_filt_id  = '<form action="' . $admin_url . '" method="POST" id="filtered_id" '
		                        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		$expect_action_tag    = '<input type="hidden" name="action" value="opttest">';
		$expect_close         = '</form>';
		$expect_filter_top    = '<p>Top of form.</p>';
		$expect_filter_bottom = '<p>Bottom of form.</p>';
		$expect_simple_form   = '<div>Imaginary Form</div>';
		
		/*
		 * This action will insert dummy copy for the simple page form, which normally triggers an action
		 * set by CMB2 hooks within CMB2_Options_Hookup
		 */
		$simple = function () {
			
			echo '<div>Imaginary Form</div>';
		};
		add_action( 'display_simple_test_action', $simple );
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		/*
		 * Call simple format
		 */
		
		$save   = $this->invokeMethod( $DISPLAY, 'save_button', $insert_simple );
		$expect = $expect_open . $expect_action_tag . $expect_simple_form . $save . $expect_close;
		$test   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_simple );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Call post format
		 */
		
		$form   = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_post );
		$save   = $this->invokeMethod( $DISPLAY, 'save_button', $insert_post );
		$expect = $expect_open . $expect_action_tag . $form . $save . $expect_close;
		$test   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Call post format, filter ID
		 */
		
		$id = function () {
			
			return 'filtered_id';
		};
		add_filter( 'cmb2_options_form_id', $id );
		
		$expect = $expect_open_filt_id . $expect_action_tag . $form . $save . $expect_close;
		$test   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Call post format, filter top of form, id filter still active
		 */
		
		$top = function () {
			
			return '<p>Top of form.</p>';
		};
		add_filter( 'cmb2_options_form_top', $top );
		
		$expect = $expect_open_filt_id . $expect_filter_top . $expect_action_tag . $form . $save . $expect_close;
		$test   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Call post format, filter bottom of form, id and top filters still active
		 */
		
		$bot = function () {
			
			return '<p>Bottom of form.</p>';
		};
		add_filter( 'cmb2_options_form_bottom', $bot );
		
		$expect = $expect_open_filt_id . $expect_filter_top . $expect_action_tag . $form . $save
		          . $expect_filter_bottom . $expect_close;
		$test   = $this->invokeMethod( $DISPLAY, 'page_form', $insert_post );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->page_form_post( $inserted_args = array() )
	 *
	 * Method called internally to get the 'post' page form. This tests HTML and filters directly
	 * generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *   CMB2_Utils::do_void_action()
	 *   CMB2_Page_Display->page_form_post_nonces()
	 *   CMB2_Page_Display->->page_form_post_sidebar()
	 *
	 * Inserted args which can affect HTML:
	 *   'page_metaboxes'
	 *   'checks'
	 *   'page_columns'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_page_form_post() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$insert_1                 = array(
			'option_key'     => $opt,
			'page_id'        => $opt,
			'page_format'    => 'post',
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'page_columns'   => 1,
		);
		$insert_2                 = $insert_1;
		$insert_2['page_columns'] = 2;
		
		$e_f_a_t = function () {
			
			echo '<p>test efat</p>';
		};
		
		$metabox = function () {
			
			echo '<p>test metabox</p>';
		};
		
		add_meta_box( 'testnormal', 'Title', $metabox, 'pagetest', 'normal' );
		add_meta_box( 'testadvanced', 'Title', $metabox, 'pagetest', 'advanced' );
		add_meta_box( 'testcontext', 'Title', $metabox, 'pagetest', 'testcon' );
		
		ob_start();
		do_meta_boxes( $opt, 'normal', '' );
		$expected_boxes_normal = ob_get_clean();
		
		ob_start();
		do_meta_boxes( $opt, 'advanced', '' );
		$expected_boxes_advanced = ob_get_clean();
		
		ob_start();
		do_meta_boxes( $opt, 'testcon', '' );
		$expected_boxes_testcon = ob_get_clean();
		
		$expected_boxes_context = '<p>test efat</p>';
		$expected_1_open        = '<div id="poststuff"><div id="post-body" class="metabox-holder columns-1">';
		$expected_2_open        = '<div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
		$expected_1_main        = '<div id="postbox-container-1" class="postbox-container">';
		$expected_2_main        = '<div id="postbox-container-2" class="postbox-container">';
		$expected_close         = '</div></div>';
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		/*
		 * Call without context metaboxes, single column
		 */
		
		$test    = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_1 );
		$nonces  = $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert_1 );
		$sidebar = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert_1 );
		$expect  = $nonces . $expected_1_open . $sidebar . $expected_1_main . $expected_boxes_normal
		           . $expected_boxes_advanced . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Call without context metaboxes, two columns
		 */
		
		$test    = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_2 );
		$nonces  = $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert_2 );
		$sidebar = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert_2 );
		$expect  = $nonces . $expected_2_open . $sidebar . $expected_2_main
		           . $expected_boxes_normal . $expected_boxes_advanced . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Add context boxes
		 */
		
		add_action( 'edit_form_after_title', $e_f_a_t );
		
		$test    = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_2 );
		$nonces  = $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert_2 );
		$sidebar = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert_2 );
		$expect  = $nonces . $expected_boxes_context . $expected_2_open . $sidebar . $expected_2_main
		           . $expected_boxes_normal . $expected_boxes_advanced . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Alter metaboxes array, should suppress output of advanced box
		 * Note: This allows changing the actual WP context in relation to the method's call
		 */
		
		$insert_2['page_metaboxes']['advanced'] = 'testcon';
		
		$test    = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_2 );
		$nonces  = $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert_2 );
		$sidebar = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert_2 );
		$expect  = $nonces . $expected_boxes_context . $expected_2_open . $sidebar . $expected_2_main
		           . $expected_boxes_normal . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Alter checks array, should allow calling the box context we added above
		 */
		
		$insert_2['checks']['metaboxes'] = array( NULL, array( 'side', 'normal', 'testcon' ), );
		
		$test    = $this->invokeMethod( $DISPLAY, 'page_form_post', $insert_2 );
		$nonces  = $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert_2 );
		$sidebar = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert_2 );
		$expect  = $nonces . $expected_boxes_context . $expected_2_open . $sidebar . $expected_2_main
		           . $expected_boxes_normal . $expected_boxes_testcon . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->page_form_post_sidebar( $inserted_args = array() )
	 *
	 * Method called internally to get the optional sidebar in 'post' page format. This tests HTML and filters
	 * directly generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *   CMB2_Utils::do_void_action()
	 *
	 * Inserted args which can affect HTML:
	 *   'page_metaboxes'
	 *   'checks'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_page_form_post_sidebar() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$insert = array(
			'option_key'     => $opt,
			'page_id'        => $opt,
			'page_format'    => 'post',
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'checks'         => array(
				'context'   => array( 'edit_form_after_title', ),
				'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
			),
			'page_columns'   => 2,
		);
		
		$metabox = function () {
			
			echo '<p>test metabox</p>';
		};
		
		add_meta_box( 'testside', 'Title', $metabox, 'pagetest', 'side' );
		add_meta_box( 'testsidetwo', 'Title', $metabox, 'pagetest', 'sidetwo' );
		
		ob_start();
		do_meta_boxes( $opt, 'side', '' );
		$expected_boxes = ob_get_clean();
		
		ob_start();
		do_meta_boxes( $opt, 'sidetwo', '' );
		$expected_other = ob_get_clean();
		
		$expected_open  = '<div id="postbox-container-1" class="postbox-container">';
		$expected_close = '</div>';
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		/*
		 * Should return "regular" display
		 */
		
		$test   = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert );
		$expect = $expected_open . $expected_boxes . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Setting columns to 1 should return empty string
		 */
		$insert['page_columns'] = 1;
		
		$this->assertEmpty( $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert ) );
		
		/*
		 * Alter metaboxes array, should suppress output of side box
		 */
		$insert['page_columns'] = 2;
		$test                   = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert );
		$expect                 = $expected_open . $expected_boxes . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$insert['page_metaboxes']['side'] = 'sidetwo';
		$test                             = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert );
		$expect                           = $expected_open . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Alter checks array, should restore the side box
		 */
		
		$insert['checks']['metaboxes'] = array( NULL, array( 'sidetwo', 'normal', 'testcon' ), );
		$test                          = $this->invokeMethod( $DISPLAY, 'page_form_post_sidebar', $insert );
		$expect                        = $expected_open . $expected_other . $expected_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->page_form_post_nonces( $inserted_args = array() )
	 *
	 * Method called internally to get nonces for postbox use. This tests HTML and filters
	 * directly generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *
	 * Inserted args which can affect HTML:
	 *   'page_nonces'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_page_form_post_nonces() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$insert = array(
			'option_key'  => $opt,
			'page_id'     => $opt,
			'page_format' => 'post',
			'page_nonces' => TRUE,
		);
		
		$expect = wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE )
		          . wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE );
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		/*
		 * Because this uses wp_nonce_field(), we cannot get the same output to test against.
		 * By preg_replace the value, we can be fairly sure we got back the right code.
		 */
		
		$tag = '<input id="meta-box-order-nonce" name="meta-box-order-nonce" value="6b3dbead71" type="hidden">';
		$rep = '<input id="meta-box-order-nonce" name="meta-box-order-nonce" VALUE type="hidden">';
		
		$this->assertEquals( $rep, preg_replace( '~value=".+?"~', 'VALUE', $tag ) );
		
		$expect = preg_replace( '~value=".+?"~', 'VALUE', $expect );
		$test   = preg_replace( '~value=".+?"~', 'VALUE',
			$this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert ) );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Setting page_nonces to false should return empty string
		 */
		
		$insert['page_nonces'] = FALSE;
		
		$this->assertEmpty( $this->invokeMethod( $DISPLAY, 'page_form_post_nonces', $insert ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->save_button( $inserted_args = array() )
	 *
	 * Method called internally to get the 'save' and 'reset' buttons. This tests HTML and filters directly
	 * generated by this method.
	 *
	 * Can use these methods, tested within this test class, or within CMB2_Util test class:
	 *   CMB2_Page_Display->merge_inserted_args()
	 *
	 * Filters applied within this method:
	 *   'cmb2_options_page_save_html'
	 *
	 * Inserted args which can affect HTML:
	 *   'save_button'
	 *   'reset_button'
	 *   'button_wrap'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_save_button() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$insert = array(
			'option_key'   => $opt,
			'page_id'      => $opt,
			'page_format'  => 'post',
			'save_button'  => '',
			'reset_button' => '',
			'button_wrap'  => FALSE,
		);
		
		$expect_wrap_open  = '<p class="cmb-submit-wrap clear">';
		$expect_wrap_close = '</p>';
		$expect_save       = get_submit_button( esc_attr( 'Save Test' ), 'primary', 'submit-cmb', FALSE );
		$expect_reset      = get_submit_button( esc_attr( 'Reset Test' ), 'secondary', 'reset-cmb', FALSE );
		$expect_filter     = '<p>Filtered away.</p>';
		
		/*
		 * Calling with empty 'save' and 'reset' buttons should return empty string
		 */
		
		$this->assertEmpty( $this->invokeMethod( $DISPLAY, 'save_button', $insert ) );
		
		/*
		 * Calling with save button should return save button
		 */
		
		$insert['save_button'] = 'Save Test';
		$expect                = $expect_save;
		$test                  = $this->invokeMethod( $DISPLAY, 'save_button', $insert );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Calling with reset button should return reset button
		 */
		
		$insert['save_button']  = '';
		$insert['reset_button'] = 'Reset Test';
		$expect                 = $expect_reset;
		$test                   = $this->invokeMethod( $DISPLAY, 'save_button', $insert );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Calling with both should return both
		 */
		
		$insert['save_button']  = 'Save Test';
		$insert['reset_button'] = 'Reset Test';
		$expect                 = $expect_reset . $expect_save;
		$test                   = $this->invokeMethod( $DISPLAY, 'save_button', $insert );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Calling with wrap = true should wrap buttons
		 */
		
		$insert['button_wrap'] = TRUE;
		$expect                = $expect_wrap_open . $expect_reset . $expect_save . $expect_wrap_close;
		$test                  = $this->invokeMethod( $DISPLAY, 'save_button', $insert );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Adding a filter should return filter results
		 */
		
		$filt = function () {
			
			return '<p>Filtered away.</p>';
		};
		
		add_filter( 'cmb2_options_page_save_html', $filt );
		
		$expect = $expect_filter;
		$test   = $this->invokeMethod( $DISPLAY, 'save_button', $insert );
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->render() is public accessor for class.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_render() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( $cmbids[0] );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		$cfg['enqueue_js'] = FALSE;
		$cfg['cmb_styles'] = FALSE;
		$cmb2              = $this->get_cmb2( $cfg );
		$opt               = $this->get_option_key( $cmb2 );
		
		$this->assertFalse( $cmb2->prop( 'enqueue_js' ) );
		$this->assertFalse( $cmb2->prop( 'cmb_styles' ) );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$expect_open_simple = '<div class="wrap cmb2-options-page options-cmb2_test_option_0">';
		$expect_close       = '</div>';
		$expect_title       = '<h1 class="wp-heading-inline">Test Box</h1>';
		
		/*
		 * Called without settings_errors, it should produce same output as ->page_html(), tested above.
		 */
		
		$test   = $DISPLAY->render();
		$form   = $this->invokeMethod( $DISPLAY, 'page_form' );
		$expect = $expect_open_simple . $expect_title . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * It should add any settings errors
		 */

		add_settings_error( $opt . '-notices', 'cmb2', 'test', 'updated' );
		
		$test   = $DISPLAY->render();
		$form   = $this->invokeMethod( $DISPLAY, 'page_form' );
		$notice = '<div id=\'setting-error-cmb2\' class=\'updated settings-error notice is-dismissible\'>'
		          . '<p><strong>test</strong></p></div>';
		$expect = $notice . $expect_open_simple . $expect_title . $form . $expect_close;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Display->maybe_output_settings_notices()
	 *
	 * Checks to see if either the shared property 'disable_settings_errors' is true or the global
	 * $parent_page is NOT options-general.php before adding settings notices.
	 *
	 * Note that the previous test added a settings error to this option key.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_display
	 */
	public function test_protected_CMB2PageDisplay_maybe_output_settings_notices() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( $cmbids[0] );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		$cfg['disable_settings_errors'] = FALSE;
		
		$cmb2 = $this->get_cmb2( $cfg );
		$opt  = $this->get_option_key( $cmb2 );
		
		$this->assertFalse( $cmb2->prop( 'disable_settings_errors' ) );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$notice = '<div id=\'setting-error-cmb2\' class=\'updated settings-error notice is-dismissible\'>'
		          . '<p><strong>test</strong></p></div>';
		
		/*
		 * Should return a notice
		 */
		
		$test   = $this->invokeMethod( $DISPLAY, 'maybe_output_settings_notices' );
		$expect = $notice;
		
		$this->assertHTMLstringsAreEqual( $expect, $test );
		
		/*
		 * Should not return a notice if $parent_page is set to 'options-general.php'
		 */
		
		$GLOBALS['parent_file'] = 'options-general.php';
		
		$test = $this->invokeMethod( $DISPLAY, 'maybe_output_settings_notices' );
		
		$this->assertEmpty( $test );
		
		$this->clear_test_properties();
		
		/*
		 * Should not return notice if shared value is true
		 */
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( $cmbids[0] );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		$cfg['disable_settings_errors'] = TRUE;
		
		$cmb2 = $this->get_cmb2( $cfg );
		$opt  = $this->get_option_key( $cmb2 );

		$this->assertTrue( $cmb2->prop( 'disable_settings_errors' ) );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$HOOKUP->page->init();
		
		$DISPLAY = new CMB2_Page_Display( $HOOKUP->page );
		
		$GLOBALS['parent_file'] = 'something.php';
		
		add_settings_error( $opt . '-notices', 'cmb2', 'test', 'updated' );
		
		$test = $this->invokeMethod( $DISPLAY, 'maybe_output_settings_notices' );
		$this->assertEmpty( $test );
		
		$this->clear_test_properties();
	}
}