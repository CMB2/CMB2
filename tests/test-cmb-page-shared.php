<?php

/**
 * Class Test_CMB2_Page_Shared
 *
 * Tests: \CMB2_Page_Shared
 *
 * Method/Action                                   Tested by:
 * -----------------------------------------------------------------------------------------------------------------
 * public __construct()                            test_invalid_CMB2PageShared()                        1
 * "                                               test_CMB2PageShared_construct()                      1
 * public return_shared_properties()               test_CMB2PageShared_return_shared_properties()       1
 * protected find_page_columns()                   test_CMB2PageShared_find_page_columns()             11
 * protected get_page_prop()                       test_CMB2PageShared_get_page_prop()                 11
 * protected get_shared_props()                    test_CMB2PageShared_get_shared_props()              11
 * protected merge_shared_props()                  test_CMB2PageShared_merge_shared_props()             8
 * apply filter 'cmb2_options_page_title'          test_CMB2PageShared_get_shared_props()               -
 * apply filter 'cmb2_options_menu_title'          "                                                    -
 * apply filter 'cmb2_options_shared_properties'   "                                                    -
 * -----------------------------------------------------------------------------------------------------------------
 * 9 Tested                                        8 Tests                                 Assertions: 44
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Shared extends Test_CMB2_Options_Base {
	
	
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
	 * @group cmb2_page_shared
	 */
	public function test_invalid_CMB2PageShared() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page_Shared();
		
		$this->clear_test_properties();
	}
	
	/**
	 * Construct the class
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group method_public
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_construct() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		$this->assertInstanceOf( 'CMB2_Page_Shared', $SHARED );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Shared->find_page_columns( $cols )
	 * Determines how many columns to add to a post-style page. It is only called if page format is 'post'
	 * internally, so that value is not checked.
	 *
	 * @since 2.XXX
	 * @group protected_methods
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_find_page_columns() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( $cmbids[0] );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		$cfg['page_format'] = 'post';
		$cmb2               = $this->get_cmb2( $cfg );
		$opt                = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		/*
		 * Passing 1 or 2 to the function should return 1 or 2
		 */
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 1 );
		$this->assertEquals( $expect, $test );
		
		$expect = 2;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 2 );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Passing any value other than auto should return the intval, or 1 if intval is 0 or > 2
		 */
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', '1' );
		$this->assertEquals( $expect, $test );
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 234 );
		$this->assertEquals( $expect, $test );
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 'ten' );
		$this->assertEquals( $expect, $test );
		
		$expect = 2;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', '2' );
		$this->assertEquals( $expect, $test );
		
		$expect = 2;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', '2 things' );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Passing auto should return '1' for our default box
		 */
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 'auto' );
		$this->assertEquals( $expect, $test );
		
		/*
		 * With no boxes with context 'side' and input set to 'auto', should return 1
		 */
		
		$this->clear_test_properties();
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[1], $cmbids[2] ) );
		$cfg1 = $cfgs[ $cmbids[1] ];
		$cfg2 = $cfgs[ $cmbids[2] ];
		
		$cfg1['page_format'] = 'post';
		$cmb1                = $this->get_cmb2( $cfg1 );
		$opt                 = $this->get_option_key( $cmb1 );
		$cfg2['page_format'] = 'post';
		$cmb2                = $this->get_cmb2( $cfg2 );
		
		$HOOKUP1 = new CMB2_Options_Hookup( $cmb1, $opt );
		$HOOKUP1->hooks();
		$HOOKUP1->page->set_options_hookup( $HOOKUP1 );
		
		$HOOKUP2 = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP2->hooks();
		$HOOKUP2->page->set_options_hookup( $HOOKUP2 );
		
		// page object should be the same in both hookups
		$this->assertEquals( $HOOKUP1->page, $HOOKUP2->page );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP1->page );
		
		$expect = 1;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 'auto' );
		$this->assertEquals( $expect, $test );
		
		/*
		 * With a box set to 'side' context and 'auto' passed, should return 2
		 */
		
		$this->clear_test_properties();
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[3], $cmbids[4] ) );
		$cfg1 = $cfgs[ $cmbids[3] ];
		$cfg2 = $cfgs[ $cmbids[4] ];
		
		$cfg1['page_format'] = 'post';
		$cmb1                = $this->get_cmb2( $cfg1 );
		$opt                 = $this->get_option_key( $cmb1 );
		$cfg2['page_format'] = 'post';
		$cfg2['context']     = 'side';
		$cmb2                = $this->get_cmb2( $cfg2 );
		
		$HOOKUP1 = new CMB2_Options_Hookup( $cmb1, $opt );
		$HOOKUP1->hooks();
		$HOOKUP1->page->set_options_hookup( $HOOKUP1 );
		
		$HOOKUP2 = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP2->hooks();
		$HOOKUP2->page->set_options_hookup( $HOOKUP2 );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP1->page );
		
		$expect = 2;
		$test   = $this->invokeMethod( $SHARED, 'find_page_columns', 'auto' );
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Shared->find_page_columns( $property, $fallback = NULL, $empty_string_ok = TRUE )
	 * Gets the cmb2->prop() for $property from all hookups.
	 *
	 * The last non-null value encountered as it tranverses the hookups is used; we do not pass a fallback
	 * to cmb2->prop() to prevent it from being set.
	 *
	 * If the result of the tranversal is null, and $fallback is not null, we return the fallback.
	 *
	 * A special case exists where CMB2->prop() returns an empty string '' and the flag $empty_string_ok is false;
	 * in that case, $fallback is returned. This is a way of ensuring some properties (Save button, etc) never have an
	 * empty string, but can still be FALSE.
	 *
	 * @since 2.XXX
	 * @group protected_methods
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_get_page_prop() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0], $cmbids[1], $cmbids[2] ) );
		$cfg1 = $cfgs[ $cmbids[0] ];
		$cfg2 = $cfgs[ $cmbids[1] ];
		$cfg3 = $cfgs[ $cmbids[2] ];
		
		// test of get last
		$cfg1['page_format'] = 'simple';
		$cfg2['page_format'] = 'simple';
		$cfg3['page_format'] = 'post';
		
		// test of one box having value
		unset( $cfg1['page_columns'] );
		unset( $cfg2['page_columns'] );
		$cfg3['page_columns'] = 2;
		
		// test of all fields unset
		unset( $cfg1['position'] );
		unset( $cfg2['position'] );
		unset( $cfg3['position'] );
		
		// test of all fields null
		$cfg1['reset_action'] = NULL;
		$cfg2['reset_action'] = NULL;
		$cfg3['reset_action'] = NULL;
		
		// test of fields all set to empty string
		$cfg1['reset_button'] = '';
		$cfg2['reset_button'] = '';
		$cfg3['reset_button'] = '';
		
		// one field not set to empty string
		$cfg1['save_button'] = '';
		$cfg2['save_button'] = '';
		$cfg3['save_button'] = 'test';
		
		// A field set to false should return false
		$cfg1['enqueue_js'] = FALSE;
		$cfg2['enqueue_js'] = NULL;
		$cfg3['enqueue_js'] = NULL;
		
		$cmb1 = $this->get_cmb2( $cfg1 );
		$cmb2 = $this->get_cmb2( $cfg2 );
		$cmb3 = $this->get_cmb2( $cfg3 );
		$opt  = $this->get_option_key( $cmb1 );
		
		$HOOKUP1 = new CMB2_Options_Hookup( $cmb1, $opt );
		$HOOKUP1->hooks();
		$HOOKUP1->page->set_options_hookup( $HOOKUP1 );
		
		$HOOKUP2 = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP2->hooks();
		$HOOKUP2->page->set_options_hookup( $HOOKUP2 );
		
		$HOOKUP3 = new CMB2_Options_Hookup( $cmb3, $opt );
		$HOOKUP3->hooks();
		$HOOKUP3->page->set_options_hookup( $HOOKUP3 );
		
		// page object should be the same in all three hookups
		$this->assertEquals( $HOOKUP1->page, $HOOKUP2->page );
		$this->assertEquals( $HOOKUP1->page, $HOOKUP3->page );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP1->page );
		
		/*
		 * A property not set on a hookup and not given a fallback should return null
		 */
		
		$test = $this->invokeMethod( $SHARED, 'get_page_prop', 'somerandomproperty' );
		$this->assertNull( $test );
		
		$test = $this->invokeMethod( $SHARED, 'get_page_prop', 'position' );
		$this->assertNull( $test );
		
		$test = $this->invokeMethod( $SHARED, 'get_page_prop', 'reset_action' );
		$this->assertNull( $test );
		
		/*
		 * A prop not set on a hookup but with a fallback should return the fallback
		 */
		
		$expect = 'howdy';
		$test   = $this->invokeMethod( $SHARED, 'get_page_prop', 'position', 'howdy' );
		$this->assertEquals( $expect, $test );
		
		/*
		 * A prop set on a box should return the box value, and the last box value set
		 */
		
		$expect = 2;
		$test   = $this->invokeMethod( $SHARED, 'get_page_prop', 'page_columns' );
		$this->assertEquals( $expect, $test );
		
		$expect = 'post';
		$test   = $this->invokeMethod( $SHARED, 'get_page_prop', 'page_format' );
		$this->assertEquals( $expect, $test );
		
		$test = $this->invokeMethod( $SHARED, 'get_page_prop', 'enqueue_js' );
		$this->assertFalse( $test );
		
		/*
		 * A prop which is set to an empty string on a box without the empty strings flag being set should
		 * return an empty string
		 */
		
		$expect = '';
		$test   = $this->invokeMethod( $SHARED, 'get_page_prop', 'reset_button', 'shouldnotbereturned' );
		$this->assertEquals( $expect, $test );
		
		/*
		 * A prop which is set to an empty string with the empty strings flag not set should return the fallback
		 */
		
		$expect = 'shouldbereturned';
		$test   = $this->invokeMethod( $SHARED, 'get_page_prop', 'reset_button', 'shouldbereturned', FALSE );
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Shared->get_shared_props( $passed = array() )
	 * Takes a default array of shared props and checks all hookups for the value using get_page_prop().
	 * Applies filters to the page and menu titles.
	 * Allows the "final" list of shared props to be filtered.
	 *
	 * @since 2.XXX
	 * @group protected_methods
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_get_shared_props() {
		
		$defaults = array(
			'capability'     => 'manage_options',
			'cmb_styles'     => TRUE,
			'display_cb'     => FALSE,
			'enqueue_js'     => TRUE,
			'hide_menu'      => FALSE,
			'icon_url'       => '',
			'menu_title'     => '',
			'menu_first_sub' => NULL,
			'parent_slug'    => '',
			'page_columns'   => 'auto',
			'page_format'    => 'simple',
			'position'       => NULL,
			'reset_button'   => '',
			'reset_action'   => 'default',
			'save_button'    => 'Save',
			'title'          => 'Test Box',
		);
		
		$expected_no_params = array(
			'capability'     => 'manage_options',
			'cmb_styles'     => TRUE,
			'display_cb'     => FALSE,
			'enqueue_js'     => TRUE,
			'hide_menu'      => FALSE,
			'icon_url'       => '',
			'menu_title'     => 'Test Box',
			'menu_first_sub' => NULL,
			'parent_slug'    => '',
			'page_columns'   => 1,
			'page_format'    => 'simple',
			'position'       => NULL,
			'reset_button'   => '',
			'reset_action'   => 'default',
			'save_button'    => 'Save',
			'title'          => 'Test Box',
		);
		
		$unset_cfg_keys = array(
			'id',
			'title',
			'fields',
			'option_key',
			'object_types',
			'hookup',
			'admin_menu_hook',
			'menu_slug',
			'page_title',
		);
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		// unset the values in default array above, except for title, so we can test return of default array
		foreach ( array_keys( $defaults ) as $key ) {
			if ( $key == 'title' ) {
				continue;
			}
			unset( $cfg[ $key ] );
		}
		
		$this->assertEquals( $unset_cfg_keys, array_keys( $cfg ) );
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		/*
		 * Should return default array if no viable properties are set on boxes
		 */
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props' );
		$this->assertSame( $expected_no_params, $test );
		
		/*
		 * Should return false if a passed in value is not an array, or page->shared is set and passed array is empty
		 */
		
		$this->assertFalse( $this->invokeMethod( $SHARED, 'get_shared_props', 'string' ) );
		$HOOKUP->page->shared = $test;
		$this->assertFalse( $this->invokeMethod( $SHARED, 'get_shared_props', array() ) );
		
		$this->clear_test_properties();
		
		/*
		 * Should not set improper var types even if set on boxes
		 */
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[1] ) );
		$cfg  = $cfgs[ $cmbids[1] ];
		
		$cfg['enqueue_js']   = 'this should not be a string';
		$cfg['page_columns'] = 'junk value becomes one';
		$cfg['position']     = '123';
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		/*
		 * Should replace the values in the array with values set on box (enqueue_js cannot be string)
		 * Should replace junk page_columns value with 1 or 2
		 * Should replace non integer value on position with integer
		 */
		
		$expect             = $expected_no_params;
		$expect['position'] = 123;
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props' );
		$this->assertSame( $expect, $test );
		
		/*
		 * Should apply filter to page title, and menu title should reflect this
		 */
		
		$title_func = function () {
			
			return 'New Title';
		};
		add_filter( 'cmb2_options_page_title', $title_func );
		
		$expect['menu_title'] = $expect['title'] = 'New Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props' );
		$this->assertSame( $expect, $test );
		
		/*
		 * Should apply filter to menu title
		 */
		
		$menu_func = function () {
			
			return 'Menu Title';
		};
		add_filter( 'cmb2_options_menu_title', $menu_func );
		
		$expect['menu_title'] = 'Menu Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props' );
		$this->assertSame( $expect, $test );
		
		/*
		 * If passed properties should replace matching values
		 */
		
		$expect['menu_title'] = 'Passed Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props', array( 'menu_title' => 'Passed Title' ) );
		$this->assertSame( $expect, $test );
		
		/*
		 * Should reject bad var types on passed in array
		 */
		
		$expect['menu_title'] = 'Menu Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props', array( 'menu_title' => 12345 ) );
		$this->assertSame( $expect, $test );
		
		/*
		 * Should filter the shared values array
		 */
		
		$filter_shared = function ( $shared, $shared_instance ) use ( $SHARED ) {
			
			if ( $shared_instance === $SHARED ) {
				
				$shared['menu_title'] = 'Filtered Title';
			}
			
			return $shared;
		};
		add_filter( 'cmb2_options_shared_properties', $filter_shared, 10, 2 );
		
		$expect['menu_title'] = 'Filtered Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props', array( 'menu_title' => 12345 ) );
		$this->assertSame( $expect, $test );
		
		/*
		 * Should reject filtered values which are not of proper type
		 */
		
		$filter_shared_again = function ( $shared, $shared_instance ) use ( $SHARED ) {
			
			if ( $shared_instance === $SHARED ) {
				
				$shared['menu_title'] = 123456;
			}
			
			return $shared;
		};
		add_filter( 'cmb2_options_shared_properties', $filter_shared_again, 11, 2 );
		
		$expect['menu_title'] = 'Menu Title';
		
		$test = $this->invokeMethod( $SHARED, 'get_shared_props', array( 'menu_title' => 12345 ) );
		$this->assertSame( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Shared->merge_shared_props( $props, $passed )
	 * This merges shared properties injected into class, checking that their type is allowed and array keys are
	 * present in the original array.
	 *
	 * Method does not type-check the lead array, as it is passed a defaults array which is not exposed to being
	 * altered.
	 *
	 * @since 2.XXX
	 * @group protected_methods
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_merge_shared_props() {
		
		// arrays are not an allowed type
		$base = array(
			'capability'     => array(),
			'cmb_styles'     => array(),
			'display_cb'     => array(),
			'enqueue_js'     => array(),
			'hide_menu'      => array(),
			'icon_url'       => array(),
			'menu_title'     => array(),
			'menu_first_sub' => array(),
			'parent_slug'    => array(),
			'page_columns'   => array(),
			'page_format'    => array(),
			'position'       => array(),
			'reset_button'   => array(),
			'reset_action'   => array(),
			'save_button'    => array(),
			'title'          => array(),
		);
		
		$obj = new stdClass();
		
		$expected_null                   = $base;
		$expected_null['menu_first_sub'] = NULL;
		$expected_null['parent_slug']    = NULL;
		$expected_null['position']       = NULL;
		
		$try_null = $base;
		foreach ( $try_null as $key => $value ) {
			$try_null[ $key ] = NULL;
		}
		
		$expected_numeric                 = $base;
		$expected_numeric['position']     = 1;
		$expected_numeric['page_columns'] = 1;
		
		$try_numeric = $base;
		foreach ( $try_null as $key => $value ) {
			$try_numeric[ $key ] = 1;
		}
		
		$expected_object               = $base;
		$expected_object['display_cb'] = $obj;
		
		$try_object = $base;
		foreach ( $try_object as $key => $value ) {
			$try_object[ $key ] = $obj;
		}
		
		$expected_bool                = $base;
		$expected_bool['cmb_styles']  = TRUE;
		$expected_bool['display_cb']  = TRUE;
		$expected_bool['enqueue_js']  = TRUE;
		$expected_bool['hide_menu']   = TRUE;
		$expected_bool['save_button'] = TRUE;
		
		$try_bool = $base;
		foreach ( $try_bool as $key => $value ) {
			$try_bool[ $key ] = TRUE;
		}
		
		$expected_string                   = $base;
		$expected_string['capability']     = 'test';
		$expected_string['icon_url']       = 'test';
		$expected_string['menu_title']     = 'test';
		$expected_string['menu_first_sub'] = 'test';
		$expected_string['parent_slug']    = 'test';
		$expected_string['page_format']    = 'test';
		$expected_string['reset_button']   = 'test';
		$expected_string['reset_action']   = 'test';
		$expected_string['save_button']    = 'test';
		$expected_string['title']          = 'test';
		
		$try_string = $base;
		foreach ( $try_string as $key => $value ) {
			$try_string[ $key ] = 'test';
		}
		
		$expected_empty_string                   = $base;
		$expected_empty_string['capability']     = '';
		$expected_empty_string['icon_url']       = '';
		$expected_empty_string['menu_title']     = '';
		$expected_empty_string['menu_first_sub'] = '';
		$expected_empty_string['parent_slug']    = '';
		$expected_empty_string['reset_button']   = '';
		
		$try_empty_string = $base;
		foreach ( $try_empty_string as $key => $value ) {
			$try_empty_string[ $key ] = '';
		}
		
		$expected_extra = $base;
		
		$try_extra           = $base;
		$try_extra['extra']  = 'test';
		$try_extra['extra2'] = 'test';
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		/*
		 * Sending an empty array in the second position will return first array
		 */
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, array() );
		$this->assertSame( $base, $test );
		
		/*
		 * for each allowed var type, send a following array with all values set to the var type.
		 * Returned array should be the base (all empty arrays) except those keys where the type is allowed.
		 */
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_null );
		$this->assertSame( $expected_null, $test );
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_numeric );
		$this->assertSame( $expected_numeric, $test );
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_object );
		$this->assertSame( $expected_object, $test );
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_bool );
		$this->assertSame( $expected_bool, $test );
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_string );
		$this->assertSame( $expected_string, $test );
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_empty_string );
		$this->assertSame( $expected_empty_string, $test );
		
		/*
		 * Extra keys should be deleted
		 */
		
		$test = $this->invokeMethod( $SHARED, 'merge_shared_props', $base, $try_extra );
		$this->assertSame( $expected_extra, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Shared->return_shared_properties()
	 * Public accessor to get_shared_props().
	 *
	 * @since 2.XXX
	 * @group public_methods
	 * @group cmb2_page_shared
	 */
	public function test_CMB2PageShared_return_shared_properties() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SHARED = new CMB2_Page_Shared( $HOOKUP->page );
		
		$expect = $this->invokeMethod( $SHARED, 'get_shared_props' );
		$test   = $SHARED->return_shared_properties();
		
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
}