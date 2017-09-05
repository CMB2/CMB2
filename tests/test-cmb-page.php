<?php

/**
 * Class Test_CMB2_Page
 *
 * Tests: CMB2_Page
 *
 * Method/Action                       Tested by:
 * ------------------------------------------------------------------------------------------------------------
 * public __construct()                test_CMB2OptionsHookup_construct_magic()                       3
 * "                                   test_invalid_CMB2Page_no_params()                              1
 * "                                   test_invalid_CMB2Page_one_param()                              1
 * "                                   test_invalid_CMB2Page_two_param()                              1
 * public set_options_hookup()         test_CMB2Page_set_options_hookup()                             3
 * "                                   test_invalid_CMB2Page_set_options_hookup()                     1
 * public init()                       test_CMB2Page_init()                                           4
 * public add_metaboxes()              test_CMB2Page_add_metaboxes()                                  3
 * public add_postbox_script()         test_CMB2Page_add_postbox_script()                             3
 * public add_postbox_toggle()         test_CMB2Page_add_postbox_toggle()                             2
 * public add_registered_setting()     test_CMB2Page_add_registered_setting()                         2
 * public add_to_admin_menu()          test_CMB2Page_add_to_admin_menu()                              3
 * public add_update_notice()          test_CMB2Page_add_update_notice()                              2
 * public render()                     test_CMB2Page_render()                                         2
 * public save()                       test_CMB2Page_save()                                           1
 * protected is_setting_registered()   test_CMB2Page_is_setting_registered()                          2
 * protected is_updated()              test_CMB2Page_is_updated()                                     4
 * protected maybe_scripts()           test_CMB2Page_maybe_scripts()                                  2
 * private _class()                    test_CMB2Page_class()                                          3
 * "                                   test_invalid_CMB2Page_class()                                  1
 * magic __get()                       test_CMB2OptionsHookup_construct_magic()                       -
 * magic __set()                       test_CMB2OptionsHookup_construct_magic()                       -
 * "                                   test_invalid_CMB2Page_set()                                    1
 * magic __isset()                     test_CMB2OptionsHookup_construct_magic()                       -
 * ------------------------------------------------------------------------------------------------------------
 * 17 Tested                           21 Tests                                          Assertions: 45
 *
 * @since 2.XXX
 */
class Test_CMB2_Page extends Test_CMB2_Options_Base {
	
	
	public function setUp() {
		
		parent::setUp();
	}
	
	public function tearDown() {
		
		parent::tearDown();
	}
	
	/**
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_no_params() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page();
	}
	
	/**
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_one_param() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page( 'test' );
	}
	
	/**
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_two_param() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page( 'test', 'test' );
	}
	
	/**
	 * Gets instance of CMB2_Page, and tests the __get, __set, and __isset magic methods
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group cmb2_page
	 */
	public function test_CMB2OptionsHookup_construct_magic() {
		
		$page_id      = $this->test_option_key . '0';
		$option_key   = $this->test_option_key . '0';
		$wp_menu_hook = 'admin_menu';
		
		/*
		 * Passing valid parameters should return instance
		 */
		
		$PAGE = new CMB2_Page( $page_id, $option_key, $wp_menu_hook );
		
		$this->assertInstanceOf( 'CMB2_Page', $PAGE );
		$this->assertEquals( $page_id, $PAGE->page_id );
		
		// Should be able to set certain properties, will throw exception, tested below
		$PAGE->page_hook = 'test';
		$this->assertTrue( isset( $PAGE->page_id ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * @expectedException \Exception
	 *
	 * __set will throw an exception if anything other than 'hookups', 'shared', and 'page_hook' is set
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_set() {
		
		$page_id      = $this->test_option_key . '0';
		$option_key   = $this->test_option_key . '0';
		$wp_menu_hook = 'admin_menu';
		
		$PAGE = new CMB2_Page( $page_id, $option_key, $wp_menu_hook );
		
		$PAGE->test = 'oops';
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->init()
	 *
	 * All methods used by this are tested elsewhere.
	 *
	 * Sets $this->hooked
	 * Sets $this->shared via CMB2_Page_Shared->return_shared_properties()
	 * Calls ->maybe_scripts() to see if scripts need to be added
	 * Calls CMB2_Page_Hooks->hooks() to set early hooks
	 *
	 * Returns true if run and false if it has already been run.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_init() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		$PAGE->set_options_hookup( $HOOKUP );
		
		$this->assertEmpty( $PAGE->hooked );
		$this->assertTrue( $PAGE->init() );
		$this->assertTrue( $PAGE->hooked );
		$this->assertFalse( $PAGE->init() );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->set_options_hookup( CMB2_Options_Hookup $hookup )
	 *
	 * Used to inject CMB2_Options_Hookup objects into this page. The Hookup objects contain a metabox and
	 * useful other information. When that class is invoked, it will add itself to the correct page using this
	 * method.
	 *
	 * Test of invalid type to function is below.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_set_options_hookup() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		
		/*
		 * Adding a page hookup should work and return same instance
		 */
		
		$this->assertEmpty( $PAGE->hookups );
		
		$PAGE->set_options_hookup( $HOOKUP );
		$this->assertCount( 1, $PAGE->hookups );
		
		$page_hookups = $PAGE->hookups;
		$this->assertSame( $HOOKUP, $page_hookups[ $cmbids[0] ] );
		
		$this->clear_test_properties();
	}
	
	/**
	 * @expectedException \TypeError
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_set_options_hookup() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		/** @noinspection PhpParamsInspection */
		$PAGE->set_options_hookup( 'test' );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->_class( $name )
	 *
	 * Used to invoke CMB2_Page_* classes. Will check the $classes property to see if an instance is present;
	 * if not, will get a new instance, injected $this into that class.
	 *
	 * Will throw an exception if asked for a class which doesn't exist, tested below.
	 *
	 * @since 2.XXX
	 * @group method_private
	 * @group cmb2_page
	 */
	public function test_CMB2Page_class() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		$this->assertNull( $PAGE->classes['Save'] );
		
		$this->invokeMethod( $PAGE, '_class', 'Save' );
		$this->assertInstanceOf( 'CMB2_Page_Save', $PAGE->classes['Save'] );
		
		$SAVE = $PAGE->classes['Save'];
		$this->invokeMethod( $PAGE, '_class', 'Save' );
		$this->assertSame( $SAVE, $PAGE->classes['Save'] );
		
		$this->clear_test_properties();
	}
	
	/**
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_private
	 * @group cmb2_page
	 */
	public function test_invalid_CMB2Page_class() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		$this->invokeMethod( $PAGE, '_class', 'Unknown' );
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->maybe_scripts()
	 * Checks shared properties 'enqueue_js' and 'cmb_styles' and triggers their addition if called for
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page
	 */
	public function test_CMB2Page_maybe_scripts() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		$PAGE->set_options_hookup( $HOOKUP );
		
		$PAGE->shared = array( 'cmb_styles' => TRUE, 'enqueue_js' => TRUE );
		$expect       = array( 'js' => TRUE, 'css' => TRUE );
		$test         = $this->invokeMethod( $PAGE, 'maybe_scripts' );
		$this->assertEquals( $expect, $test );
		
		$PAGE->shared = array( 'cmb_styles' => FALSE, 'enqueue_js' => FALSE );
		$expect       = array( 'js' => FALSE, 'css' => FALSE );
		$test         = $this->invokeMethod( $PAGE, 'maybe_scripts' );
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->is_updated()
	 * Checks to see if the options page has been updated by examining _GET vars: 'updated' just
	 * needs to be present, but 'page' must match internal 'page_id'
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page
	 */
	public function test_CMB2Page_is_updated() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		$this->assertEquals( 'test', $PAGE->page_id );
		
		$_GET['updated'] = 'true';
		$_GET['page']    = 'test';
		
		$this->assertEquals( 'true', $this->invokeMethod( $PAGE, 'is_updated' ) );
		
		$_GET['updated'] = 'true';
		$_GET['page']    = 'something';
		
		$this->assertFalse( $this->invokeMethod( $PAGE, 'is_updated' ) );
		
		unset( $_GET['updated'] );
		$_GET['page'] = 'test';
		
		$this->assertFalse( $this->invokeMethod( $PAGE, 'is_updated' ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->is_setting_registered( $option_key = '' )
	 * Checks to see if a setting has been registered with WP.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page
	 */
	public function test_CMB2Page_is_setting_registered() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		$this->assertFalse( $this->invokeMethod( $PAGE, 'is_setting_registered', 'testsetting' ) );
		
		register_setting( 'cmb2', 'testsetting' );
		
		$this->assertTrue( $this->invokeMethod( $PAGE, 'is_setting_registered', 'testsetting' ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->save( $redirect = true )
	 * Pass-through to CMB2_Page_Save->save_options(), which is tested elsewhere. This test demonstrates this
	 * method is invoking save_options()
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_save() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		$expect = array(
			'http://example.org/wp-admin/?updated=false',
			array(),
		);
		$test   = $PAGE->save( FALSE );
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->render( $redirect = true )
	 * Checks if display_cb was set and if it was callable, and uses results. Otherwise, passes through to
	 * CMB2_Page_Display->render()
	 *
	 * The output of Page_Display is tested elsewhere, this test will simply assert we got a string back.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_render() {
		
		/*
		 * Regular render. Asserting an independent Page_Display->render() equals output of Page->render()
		 */
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		$cmb2 = $this->get_cmb2( $cfg );
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		$PAGE->set_options_hookup( $HOOKUP );
		$PAGE->init();
		$DISPLAY = new CMB2_Page_Display( $PAGE );
		
		$this->assertEquals( $PAGE->render( FALSE ), $DISPLAY->render() );
		
		/*
		 * Try callback
		 */
		
		$callback = function () {
			
			return 'Not very good page.';
		};
		
		$cfgs              = $this->cmb2_box_config( array( $cmbids[1] ) );
		$cfg               = $cfgs[ $cmbids[1] ];
		$cfg['display_cb'] = $callback;
		$cmb2              = $this->get_cmb2( $cfg );
		$opt               = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		$PAGE->set_options_hookup( $HOOKUP );
		$PAGE->init();
		
		$this->assertEquals( 'Not very good page.', $PAGE->render( FALSE ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_update_notice()
	 * Uses ->is_updated() to see if page is updated, and if so, add appropriate setting.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_update_notice() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		$_GET['page']    = 'test';
		
		$this->assertEquals( 'test', $PAGE->page_id );
		
		$_GET['updated'] = 'true';
		$PAGE->add_update_notice();
		
		$_GET['updated'] = 'false';
		$PAGE->add_update_notice();
		
		$_GET['updated'] = 'reset';
		$PAGE->add_update_notice();
		
		ob_start();
		settings_errors( 'test-notices' );
		$notice = ob_get_clean();
		
		$expect =  "<div id='setting-error-cmb2' class='updated settings-error notice is-dismissible'>\n"
		          . "<p><strong>Settings updated.</strong></p></div> \n" .
		           "<div id='setting-error-cmb2' class='notice-warning settings-error notice is-dismissible'>\n"
		          . "<p><strong>Nothing to update.</strong></p></div> \n" .
		           "<div id='setting-error-cmb2' class='notice-warning settings-error notice is-dismissible'>\n"
		          . "<p><strong>Options reset to defaults.</strong></p></div> \n";

		$this->assertHTMLstringsAreEqual( $expect, $notice );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_to_admin_menu()
	 * Pass-through to CMB2_Page_Menu->add_to_menu()
	 *
	 * For test purposes, will be passed back an array. Note that the add_to_menu() method is tested elsewhere.
	 * page_hook is set within this method, and late hooks are called.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_to_admin_menu() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		
		$PAGE->set_options_hookup( $HOOKUP );
		$PAGE->init();
		
		$this->assertEmpty( $PAGE->page_hook );
		
		$expect = array(
			'type'      => 'menu',
			'params'    => array(
				'action'         => array( $PAGE, 'render' ),
				'capability'     => 'manage_options',
				'hide_menu'      => FALSE,
				'icon_url'       => '',
				'menu_first_sub' => NULL,
				'menu_slug'      => $this->test_option_key . '0',
				'menu_title'     => 'Test Box',
				'parent_slug'    => '',
				'position'       => NULL,
				'title'          => 'Test Box',
			),
			'page_hook' => 'toplevel_page_cmb2_test_option_0',
			'hooks' => array(
				array( 'load-toplevel_page_cmb2_test_option_0' => 'add_metaboxes' ),
				array( 'admin_print_styles-toplevel_page_cmb2_test_option_0' => 'add_cmb_css_to_head' ),
			),
		);
		
		$this->assertEquals( $expect, $PAGE->add_to_admin_menu() );
		$this->assertEquals( 'toplevel_page_cmb2_test_option_0', $PAGE->page_hook );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_registered_setting()
	 *
	 * Uses ->is_setting_registered(). Returns true if setting is registered, false if setting is already set.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_registered_setting() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		// adding a setting the first time will return true
		$this->assertTrue( $PAGE->add_registered_setting( 'testset' ) );

		// trying to add it again will return false
		$this->assertFalse( $PAGE->add_registered_setting( 'testset' ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_postbox_toggle()
	 *
	 * Adds a line of JS to footer of post-style pages.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_postbox_toggle() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		ob_start();
		$bool = $PAGE->add_postbox_toggle();
		$html = ob_get_clean();
		
		$expect = '<script id="cmb2-page-toggle">jQuery(document).ready(function()'
		          . '{postboxes.add_postbox_toggles("postbox-container");});</script>';
		
		$this->assertHTMLstringsAreEqual( $expect, $html );
		$this->assertTrue( $bool );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_postbox_script()
	 *
	 * Enqueues WP 'postbox' script
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_postbox_script() {
		
		$PAGE = new CMB2_Page( 'test', 'test', 'admin_menu' );
		
		$this->assertFalse( wp_script_is( 'postbox', 'enqueued' ) );
		$this->assertTrue( $PAGE->add_postbox_script() );
		$this->assertTrue( wp_script_is( 'postbox', 'enqueued' ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page->add_metaboxes()
	 *
	 * CMB2_Options_Hookup adds an action for each box that uses add_meta_box().
	 * That action is triggered for all boxes in this page by this method.
	 *
	 * It is only carried out on pages with a page_format = post;
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page
	 */
	public function test_CMB2Page_add_metaboxes() {
	
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		$cfg['page_format'] = 'post';
		$cmb2 = $this->get_cmb2( $cfg );
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$PAGE   = new CMB2_Page( $opt, $opt, 'admin_menu' );
		$PAGE->set_options_hookup( $HOOKUP );
		$HOOKUP->hooks();
		$PAGE->init();
		
		global $wp_meta_boxes;
		
		$this->assertArrayNotHasKey( $opt, $wp_meta_boxes );
		$this->assertTrue( $PAGE->add_metaboxes() );
		$this->assertNotEmpty( $wp_meta_boxes[$opt]['normal'] );
		
		$this->clear_test_properties();
	}
}