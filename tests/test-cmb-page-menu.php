<?php

/**
 * Class Test_CMB2_Page_Menu
 *
 * Tests: \CMB2_Page_Menu
 *
 * Method/Action                                  Tested by:
 * --------------------------------------------------------------------------------------------------------------
 * public __construct()                           test_CMB2PageMenu_construct()                         1
 * "                                              test_invalid_CMB2PageMenu()                           1
 * public add_to_menu()                           test_CMB2PageMenu_add_to_menu()                       2
 * protected add_to_admin_menu()                  test_CMB2PageMenu_add_to_admin_menu()                 3
 * protected menu_parameters()                    test_CMB2PageMenu_menu_parameters()                   4
 * magic __get()                                  "                                                     -
 * apply filter 'cmb2_options_page_menu_params'   "                                                     -
 * --------------------------------------------------------------------------------------------------------------
 * 5 Tested                                       5 Tests                                  Assertions: 11
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Menu extends Test_CMB2_Options_Base {
	
	
	public function setUp() {
		
		parent::setUp();
	}
	
	public function tearDown() {
		
		parent::tearDown();
	}
	
	/**
	 * @expectedException \TypeError
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group method_public
	 * @group cmb2_page_menu
	 */
	public function test_invalid_CMB2PageMenu() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page_Menu();
		
		$this->clear_test_properties();
	}
	
	/**
	 * Construct the class
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group method_public
	 * @group cmb2_page_menu
	 */
	public function test_CMB2PageMenu_construct() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$MENU = new CMB2_Page_Menu( $HOOKUP->page );
		
		$this->assertInstanceOf( 'CMB2_Page_Menu', $MENU );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Menu->add_to_menu()
	 * Public accessor to this class. Returns an array containing information about the menu item added.
	 * You must set a menu item, even if it is hidden, for WP to access the page.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page_menu
	 */
	public function test_CMB2PageMenu_add_to_menu() {
		
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
		$MENU = new CMB2_Page_Menu( $HOOKUP->page );
		
		/*
		 * Regular call returns array
		 */
		
		$expect = array(
			'type'      => 'menu',
			'params'    => array(
				'action'         => array( $HOOKUP->page, 'render' ),
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
		);
		$test   = $MENU->add_to_menu();
		$this->assertEquals( $expect, $test );
		
		/*
		 * Because there is no current user in this test environment, asking for a submenu page
		 * will return empty array
		 */
		
		$filter = function ( $params ) {
			
			$params['parent_slug'] = 'edit.php';
			
			return $params;
		};
		add_filter( 'cmb2_options_page_menu_params', $filter );
		
		$expect = array();
		$test   = $MENU->add_to_menu();
		$this->assertEquals( $expect, $test );
		
		remove_filter( 'cmb2_options_page_menu_params', $filter );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Menu->menu_parameters( $add = array() )
	 * Gets default menu parameters. Argument is available for test purposes, called without within program.
	 * Params can be filtered.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_menu
	 */
	public function test_CMB2PageMenu_menu_parameters() {
		
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
		$MENU = new CMB2_Page_Menu( $HOOKUP->page );
		
		$default = array(
			'action'         => array( $HOOKUP->page, 'render' ),
			'capability'     => 'manage_options',
			'hide_menu'      => FALSE,
			'icon_url'       => '',
			'menu_first_sub' => NULL,
			'menu_slug'      => $this->test_option_key . '0',
			'menu_title'     => 'Test Box',
			'parent_slug'    => '',
			'position'       => NULL,
			'title'          => 'Test Box',
		);
		
		/*
		 * Asking for parameters should return an array
		 */
		
		$expect = $default;
		$test   = $this->invokeMethod( $MENU, 'menu_parameters' );
		$this->assertSame( $expect, $test );
		
		/*
		 * Inserting a param should see it replaced, but type must match.
		 */
		
		$expect             = $default;
		$expect['position'] = 300;
		$add                = array(
			'position'  => 300,
			'hide_menu' => 'test' // hide_menu must be a bool
		);
		$test               = $this->invokeMethod( $MENU, 'menu_parameters', $add );
		$this->assertSame( $expect, $test );
		
		/*
		 * Params can be filtered, but same as above, types must match, extra keys discarded
		 */
		
		$filt = function ( $params, $menu_instance ) use ( $HOOKUP ) {
			
			if ( $HOOKUP->page == $menu_instance->page ) {
				$params['position'] = 555;
				$params['junk']     = 'test';
			}
			
			return $params;
		};
		add_filter( 'cmb2_options_page_menu_params', $filt, 10, 2 );
		
		$expect             = $default;
		$expect['position'] = 555;
		$test               = $this->invokeMethod( $MENU, 'menu_parameters' );
		$this->assertSame( $expect, $test );
		
		remove_filter( 'cmb2_options_page_menu_params', $filt, 10 );
		
		/*
		 * Returning a non-array via filter will return defaults
		 */
		
		$filt2 = function ( $params, $menu_instance ) use ( $HOOKUP ) {
			
			if ( $HOOKUP->page == $menu_instance->page ) {
				$params = 'junk';
			}
			
			return $params;
		};
		add_filter( 'cmb2_options_page_menu_params', $filt2, 11, 2 );
		
		$expect = $default;
		$test   = $this->invokeMethod( $MENU, 'menu_parameters' );
		$this->assertSame( $expect, $test );
		
		remove_filter( 'cmb2_options_page_menu_params', $filt2, 11 );
		
		$this->clear_test_properties();
	}
	
	/**
	 * $CMB2_Page_Menu->add_to_admin_menu( $params )
	 * Adds page using add_menu_page or add_submenu_page. If 'hide_menu' is true, parent_slug is set to null
	 * and add_submenu_page is invoked. Returns Wordpress page hook or false if could not add either.
	 *
	 * Parameters have been vetted before this method is called. WP does not seem to do any real error checking
	 * so junk params are not tested here due to unreliable return.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_menu
	 */
	public function test_CMB2PageMenu_add_to_admin_menu() {
		
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
		$MENU = new CMB2_Page_Menu( $HOOKUP->page );
		
		$params = array(
			'action'         => array( $HOOKUP->page, 'render' ),
			'capability'     => 'manage_options',
			'hide_menu'      => FALSE,
			'icon_url'       => '',
			'menu_first_sub' => NULL,
			'menu_slug'      => $this->test_option_key . '0',
			'menu_title'     => 'Test Box',
			'parent_slug'    => '',
			'position'       => NULL,
			'title'          => 'Test Box',
		);
		
		/*
		 * Add a top menu page
		 */
		
		$expect = 'toplevel_page_cmb2_test_option_0';
		$test   = $test = $this->invokeMethod( $MENU, 'add_to_admin_menu', $params );
		
		$this->assertEquals( $expect, $test );
		
		/*
		 * Add a submenu page. Needs an admin user to be able to add the menu
		 */
		
		$user_id = self::factory()->user->create( array( 'role' => 'administrator', ) );
		wp_set_current_user( $user_id );
		
		$params = array(
			'action'         => 'phpversion',
			'capability'     => 'manage_options',
			'hide_menu'      => FALSE,
			'icon_url'       => '',
			'menu_first_sub' => NULL,
			'menu_slug'      => 'submenuslug',
			'menu_title'     => 'Test Box 2',
			'parent_slug'    => 'edit.php',
			'position'       => NULL,
			'title'          => 'Test Box 2',
		);
		
		$expect = 'admin_page_submenuslug';
		$test   = $test = $this->invokeMethod( $MENU, 'add_to_admin_menu', $params );
		
		$this->assertEquals( $expect, $test );
		
		/*
		 * Add a hidden page
		 */
		
		$params = array(
			'action'         => 'phpversion',
			'capability'     => 'manage_options',
			'hide_menu'      => TRUE,
			'icon_url'       => '',
			'menu_first_sub' => NULL,
			'menu_slug'      => 'hiddenmenuslug',
			'menu_title'     => 'Test Box 3',
			'parent_slug'    => '',
			'position'       => NULL,
			'title'          => 'Test Box 3',
		);
		
		$expect = 'admin_page_hiddenmenuslug';
		$test   = $test = $this->invokeMethod( $MENU, 'add_to_admin_menu', $params );
		
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
}