<?php

/**
 * Class Test_CMB2_Options_Base
 *
 * Provides helper methods, and tests those helper methods.
 * All (at)group: internalhelpers
 *
 * Method/Action                     Tested by:
 * -----------------------------------------------------------------------------------------------------------
 * public get_hookup()               test_internal_get_cmb2_page()                 8
 * public set_boxes()                test_internal_set_boxes()                    32
 * public get_cmb2()                 test_internal_get_cmb2()                      5
 * public cmb2_box_config()          test_internal_cmb2_box_config()              59
 * public parse_box_keys()           test_internal_parse_box_keys()                9
 * public array_element_is_string()  test_internal_array_element_is_string()       7
 * public clear_test_properties()    test_internal_clear_test_properties()         4
 * public get_option_key()           test_internal_get_option_key()                2
 * -----------------------------------------------------------------------------------------------------------
 * 8 Tested                          8 Tests                                     126 Assertions
 *
 * @since 2.XXX
 */
class Test_CMB2_Options_Base extends Test_CMB2 {
	
	/**
	 * Set in setUp(), used as base string for options
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $test_option_key = '';
	
	/**
	 * Holds Test_CMB2_Object boxes, keys are box IDs
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $test_boxes = array();
	
	/**
	 * Set by test methods, holds 'menu_slug' value
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $test_menu_slug = '';
	
	/**
	 * Parameters either checked or used by CMB2_Options_Hookup. The values below are the CMB2 defaults. These
	 * are updated when the class is called by comparing them to a new instance of Test_CMB2_Object.
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $default_box_params = array();
	
	protected $invoked_filters = array();
	
	/**
	 * Set class properties. $this->test_boxes is set anew on each test that requires boxes
	 *
	 * @since 2.XXX
	 */
	public function setUp() {
		
		parent::setUp();
		
		$this->default_box_params =  array(
			'object_types'    => array( 'options-page' ),
			'hookup'          => FALSE,
			'admin_menu_hook' => 'admin_menu',
			'menu_slug'       => '',
			'parent_slug'     => NULL,
			'capability'      => NULL,
			'display_cb'      => NULL,
			'icon_url'        => NULL,
			'page_columns'    => NULL,
			'page_format'     => NULL,
			'page_title'      => NULL,
			'position'        => NULL,
			'reset_action'    => NULL,
			'reset_button'    => NULL,
			'save_button'     => NULL,
		);
		
		$this->test_option_key = 'cmb2_test_option_';
		
		$this->invoked_filters = array(
			'cmb2_options_hookup_hooks',
			'cmb2_options_page_before',
			'cmb2_options_page_after' ,
			'cmb2_options_form_id',
			'cmb2_options_form_top',
			'cmb2_options_form_bottom',
			'cmb2_options_page_save_html',
			'cmb2_options_pagehooks',
			'cmb2_options_page_menu_params',
			'cmb2_options_page_title',
			'cmb2_options_menu_title',
			'cmb2_options_shared_properties',
		);
	}
	
	/**
	 * Parent tearDown call
	 *
	 * @since 2.XXX
	 */
	public function tearDown() {
		
		parent::tearDown();
	}
	
	// TEST HELPER METHODS
	
	/**
	 * Tests helper method: $this->clear_test_properties()
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_clear_test_properties() {
		
		/*
		 * This should set the class properties
		 */
		
		$this->test_menu_slug = 'test';
		$this->set_boxes( 'test' );
		
		$this->assertEquals( 'test', $this->test_menu_slug );
		
		/*
		 * After being called, $this->test_menu_slug and $this->test_boxes should be empty
		 */
		
		$this->clear_test_properties();
		
		$this->assertFalse( CMB2_Boxes::get( 'test' ) );
		$this->assertEquals( '', $this->test_menu_slug );
		$this->assertEquals( array(), $this->test_boxes );
	}
	
	/**
	 * Tests helper method: $this->get_option_key()
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_get_option_key() {
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		
		/*
		 * Asking without index parameter should return option key
		 */
		
		$test   = $this->get_option_key( $cmb2 );
		$expect = $this->test_option_key . '0';
		
		$this->assertEquals( $expect, $test );
		
		/*
		 * Asking for an index which doesn't exist should return false
		 */
		
		$test = $this->get_option_key( $cmb2, 1 );
		
		$this->assertFalse( $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * Test helper method: $this->array_element_is_string()
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_array_element_is_string() {
		
		/*
		 * null should be false
		 */
		
		$test = NULL;
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * boolean should be false
		 */
		
		$test = TRUE;
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * array should be false
		 */
		
		$test = array();
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * object should be false
		 */
		
		$test = new stdClass();
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * empty string should be false
		 */
		
		$test = '';
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * int should be false
		 */
		
		$test = 77;
		$this->assertFalse( $this->array_element_is_string( $test ) );
		
		/*
		 * string should be true
		 */
		
		$test = 'howdy';
		$this->assertTrue( $this->array_element_is_string( $test ) );
	}
	
	/**
	 * Test helper method: $this->parse_box_keys()
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_parse_box_keys() {
		
		/*
		 * null should return empty array
		 */
		
		$test   = NULL;
		$expect = array();
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * object should return empty array
		 */
		
		$test   = new stdClass();
		$expect = array();
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * empty string should return empty array
		 */
		
		$test   = '';
		$expect = array();
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * string without spaces should return array with that string at [0]
		 */
		
		$test   = 'test';
		$expect = array( 'test' );
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * string with spaces should return array exploded at spaces
		 */
		
		$test   = 'test test2';
		$expect = array( 'test', 'test2' );
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * string with extra spaces between keys should not return empty elements
		 */
		
		$test   = ' test     test2 test3  test4';
		$expect = array( 'test', 'test2', 'test3', 'test4' );
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * empty array should return empty array
		 */
		
		$test   = array();
		$expect = array();
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * array of strings should return array
		 */
		
		$test   = array( 'test', 'test2', 'test3', 'test4' );
		$expect = array( 'test', 'test2', 'test3', 'test4' );
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
		
		/*
		 * mixed values array should return only strings
		 */
		
		$test   = array( 'test', TRUE, 185, 'test2', new stdClass() );
		$expect = array( 'test', 'test2' );
		$this->assertEquals( $expect, $this->parse_box_keys( $test ) );
	}
	
	/**
	 * Test helper method: cmb2_box_config()
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_cmb2_box_config() {
		
		/*
		 * no value, return array: length 1, id='default'
		 */
		
		$test = $this->cmb2_box_config();
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 1, count( $test ) );
		$this->assertArrayHasKey( 'default', $test );
		$this->assertInternalType( 'array', $test['default'] );
		$this->assertArrayHasKey( 'id', $test['default'] );
		$this->assertEquals( 'default', $test['default']['id'] );
		$this->assertEquals( '', $test['default']['menu_slug'] );
		
		/*
		 * string without spaces, return array: length 1, id=string
		 */
		
		$test = $this->cmb2_box_config( 'howdy' );
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 1, count( $test ) );
		$this->assertArrayHasKey( 'howdy', $test );
		$this->assertInternalType( 'array', $test['howdy'] );
		$this->assertArrayHasKey( 'id', $test['howdy'] );
		$this->assertEquals( 'howdy', $test['howdy']['id'] );
		
		/*
		 * string with spaces, return array: count of strings separated by spaces, id=those strings
		 */
		
		$test = $this->cmb2_box_config( 'howdy doody' );
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 2, count( $test ) );
		$this->assertArrayHasKey( 'howdy', $test );
		$this->assertArrayHasKey( 'doody', $test );
		$this->assertInternalType( 'array', $test['howdy'] );
		$this->assertInternalType( 'array', $test['doody'] );
		$this->assertArrayHasKey( 'id', $test['howdy'] );
		$this->assertArrayHasKey( 'id', $test['doody'] );
		$this->assertEquals( 'howdy', $test['howdy']['id'] );
		$this->assertEquals( 'doody', $test['doody']['id'] );
		
		/*
		 * empty array, return array: length 1, id='default'
		 */
		
		$test = $this->cmb2_box_config( array() );
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 1, count( $test ) );
		$this->assertArrayHasKey( 'default', $test );
		$this->assertInternalType( 'array', $test['default'] );
		$this->assertArrayHasKey( 'id', $test['default'] );
		$this->assertEquals( 'default', $test['default']['id'] );
		
		/*
		 * array, one string, return array: length 1, id=array item
		 */
		
		$test = $this->cmb2_box_config( array( 'hi' ) );
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 1, count( $test ) );
		$this->assertArrayHasKey( 'hi', $test );
		$this->assertInternalType( 'array', $test['hi'] );
		$this->assertArrayHasKey( 'id', $test['hi'] );
		$this->assertEquals( 'hi', $test['hi']['id'] );
		
		/*
		 * array, strings, return array: count of items, ids=array items
		 */
		
		$test = $this->cmb2_box_config( array( 'howdy', 'doody' ) );
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 2, count( $test ) );
		$this->assertArrayHasKey( 'howdy', $test );
		$this->assertArrayHasKey( 'doody', $test );
		$this->assertInternalType( 'array', $test['howdy'] );
		$this->assertInternalType( 'array', $test['doody'] );
		$this->assertArrayHasKey( 'id', $test['howdy'] );
		$this->assertArrayHasKey( 'id', $test['doody'] );
		$this->assertEquals( 'howdy', $test['howdy']['id'] );
		$this->assertEquals( 'doody', $test['doody']['id'] );
		
		/*
		 * returned array should have 'id', 'title', 'fields' + defaults as array keys
		 */
		
		$test   = $this->cmb2_box_config();
		$test   = array_keys( $test['default'] );
		$expect = array_merge( array(
			'id',
			'title',
			'fields',
			'option_key',
		), array_keys( $this->default_box_params ) );
		
		$this->assertEquals( $test, $expect );
		
		/*
		 * returned array 'field' should be an array containing one field config array
		 */
		
		$test = $this->cmb2_box_config();
		$test = $test['default']['fields'];
		
		$this->assertInternalType( 'array', $test );
		$this->assertEquals( 1, count( $test ) );
		$this->assertArrayHasKey( 'testfield0', $test );
		$this->assertInternalType( 'array', $test['testfield0'] );
		$this->assertArrayHasKey( 'id', $test['testfield0'] );
		$this->assertArrayHasKey( 'name', $test['testfield0'] );
		$this->assertArrayHasKey( 'type', $test['testfield0'] );
		$this->assertEquals( 'testfield0', $test['testfield0']['id'] );
		$this->assertEquals( 'Test Field', $test['testfield0']['name'] );
		$this->assertEquals( 'text', $test['testfield0']['type'] );
		
		/*
		 * Sending a non-number to the second parameter should return 0
		 */
		
		$test = $this->cmb2_box_config( 'test', 'test' );
		$test = $test['test']['option_key'];
		
		$this->assertEquals( $this->test_option_key . '0', $test );
		
		/*
		 * Sending a number to the second parameter should return that number
		 */
		
		$test = $this->cmb2_box_config( 'test', 7 );
		$test = $test['test']['option_key'];
		
		$this->assertEquals( $this->test_option_key . 7, $test );
		
		/*
		 * If $this->test_menu_slug is not empty, it should be set in box config
		 */
		
		$this->test_menu_slug = 'menuslug';
		$test                 = $this->cmb2_box_config();
		$test                 = $test['default']['menu_slug'];
		
		$this->assertEquals( $this->test_menu_slug, $test );
		
		// reset properties
		$this->clear_test_properties();
	}
	
	/**
	 * Test helper method: get_cmb2();
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_get_cmb2() {
		
		/*
		 * Sending string returns false
		 */
		
		$this->assertFalse( $this->get_cmb2( 'test' ) );
		
		/*
		 * Sending empty array returns false
		 */
		
		$this->assertFalse( $this->get_cmb2( array() ) );
		
		
		$boxes = $this->cmb2_box_config();
		$box   = $this->get_cmb2( $boxes['default'] );
		
		/*
		 * Sending a box config array returns instance of CMB2
		 */
		
		$this->assertInstanceOf( 'Test_CMB2_Object', $box );
		
		/*
		 * Ensure box has correct id
		 */
		
		$this->assertEquals( 'default', $box->prop( 'id' ) );
		
		/*
		 * Ensure box has hookup set to false
		 */
		
		$this->assertFalse( $box->prop( 'hookup' ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * Test helper method: $this->set_boxes().
	 *
	 * - $this->set_boxes returns true if number of boxes added equals count of keys sent
	 * - $this->set_boxes uses $this->cmb2_box_config using first two parameters sent
	 * - $this->set_boxes uses $this->get_cmb2 with results of config call
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_set_boxes() {
		
		/*
		 * Sending no parameters should create one box with default key and options 0, in reset array
		 */
		
		$test = $this->set_boxes();
		
		$this->assertTrue( $test );
		$this->assertEquals( 1, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'default', $this->test_boxes );
		$this->assertInstanceOf( 'Test_CMB2_Object', $this->test_boxes['default'] );
		
		/*
		 * Sending 'reset' = false should preserve existing array
		 */
		
		$test = $this->set_boxes( 'another_box', 0, FALSE );
		
		$this->assertTrue( $test );
		$this->assertEquals( 2, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'default', $this->test_boxes );
		$this->assertArrayHasKey( 'another_box', $this->test_boxes );
		
		/*
		 * Sending 'reset' = true should empty existing array first (the default if not sent is true)
		 */
		
		$test = $this->set_boxes( 'test', 0, TRUE );
		
		$this->assertTrue( $test );
		$this->assertEquals( 1, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'test', $this->test_boxes );
		
		/*
		 * Sending two keys should create two boxes
		 */
		
		$test = $this->set_boxes( 'box1 box2' );
		
		$this->assertTrue( $test );
		$this->assertEquals( 2, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'box1', $this->test_boxes );
		$this->assertArrayHasKey( 'box2', $this->test_boxes );
		
		/*
		 * Sending two additional keys with a different option and reset = false :
		 * $this->test_boxes contains four boxes, two with each option key
		 */
		
		$test    = $this->set_boxes( 'box3 box4', 1, FALSE );
		$optbox1 = $this->test_boxes['box1']->prop( 'option_key' );
		$optbox2 = $this->test_boxes['box2']->prop( 'option_key' );
		$optbox3 = $this->test_boxes['box3']->prop( 'option_key' );
		$optbox4 = $this->test_boxes['box4']->prop( 'option_key' );
		$opt1    = array( $this->test_option_key . '0' );
		$opt2    = array( $this->test_option_key . '1' );
		
		$this->assertTrue( $test );
		$this->assertEquals( 4, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'box1', $this->test_boxes );
		$this->assertArrayHasKey( 'box2', $this->test_boxes );
		$this->assertArrayHasKey( 'box3', $this->test_boxes );
		$this->assertArrayHasKey( 'box4', $this->test_boxes );
		$this->assertEquals( $opt1, $optbox1 );
		$this->assertEquals( $opt1, $optbox2 );
		$this->assertEquals( $opt2, $optbox3 );
		$this->assertEquals( $opt2, $optbox4 );
		
		/*
		 * Sending the same key twice should produce only one box in $this->test_boxes
		 */
		
		$test = $this->set_boxes( 'box1 box1' );
		
		$this->assertTrue( $test );
		$this->assertEquals( 1, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'box1', $this->test_boxes );
		
		/*
		 * Sending an array of keys should produce two boxes
		 */
		
		$test = $this->set_boxes( array( 'box1', 'box2' ) );
		
		$this->assertTrue( $test );
		$this->assertEquals( 2, count( $this->test_boxes ) );
		$this->assertArrayHasKey( 'box1', $this->test_boxes );
		$this->assertArrayHasKey( 'box2', $this->test_boxes );
		
		// reset properties
		$this->clear_test_properties();
	}
	
	/**
	 * Test helper method: get_hookup()
	 * Gets a CMB2_Page_Hookup object matching parameters sent
	 *
	 * @since 2.XXX
	 * @group internalhelpers
	 */
	public function test_internal_get_cmb2_page() {
		
		/*
		 * Sending no parameters should get an object with the current class properties
		 */
		$hookup = $this->get_cmb2_page();
		
		$this->assertInstanceOf( 'CMB2_Page', $hookup );
		$this->assertEquals( 'admin_menu', $hookup->wp_menu_hook );
		$this->assertEquals( $this->test_option_key, $hookup->page_id );
		$this->assertEquals( $this->test_option_key, $hookup->option_key );
		
		unset( $hookup );
		
		/*
		 * Sending parameters should get an object matching those properties
		 */
		$hookup = $this->get_cmb2_page( 'page', 'optkey', 'network_admin_menu' );
		
		$this->assertInstanceOf( 'CMB2_Page', $hookup );
		$this->assertEquals( 'page', $hookup->page_id );
		$this->assertEquals( 'optkey', $hookup->option_key );
		$this->assertEquals( 'network_admin_menu', $hookup->wp_menu_hook );
		
		unset( $hookup );
	}
	
	// HELPERS
	
	/**
	 * Internal helper: Looks at $this->test_boxes and adds CMB2_Options_page_Hookup objects as needed
	 *
	 * @since 2.XXX
	 * @param string $page_id
	 * @param string $opt
	 * @param string $hook
	 * @return \CMB2_Page
	 */
	public function get_cmb2_page( $page_id = '', $opt = '', $hook = 'admin_menu' ) {
		
		$opt     = empty( $opt )  ? $this->test_option_key : $opt;
		$page_id = empty( $page_id ) ? $opt : $page_id;
		
		return new CMB2_Page( $page_id, $opt, $hook );
	}
	
	/**
	 * Internal helper: Sets the boxes property to be array of CMB2 box objects.
	 * Returns whether the number of keys sent matches the number of boxes created.
	 *
	 * @since 2.XXX
	 *
	 * @param string|array|null $keys  list of keys to set
	 * @param int               $opt   option key
	 * @param bool              $reset empty the $this->boxes array
	 *
	 * @return bool
	 */
	public function set_boxes( $keys = NULL, $opt = 0, $reset = TRUE ) {
		
		$added = 0;
		
		// reset the boxes array is asked to do so
		if ( $reset ) {
			$this->test_boxes = array();
		}
		
		// get configuration arrays
		$cfgs = $this->cmb2_box_config( $keys, $opt );
		
		foreach ( $cfgs as $key => $cfg ) {
			
			$newbox = $this->get_cmb2( $cfg );
			
			if ( $newbox instanceof Test_CMB2_Object ) {
				$added ++;
				$this->test_boxes[ $key ] = $newbox;
			}
		}
		
		return $added == count( $cfgs );
	}
	
	/**
	 * Internal helper: Get a CMB2 object which has hookup set to false.
	 *
	 * @since 2.XXX
	 *
	 * @param array $box
	 *
	 * @return mixed
	 */
	public function get_cmb2( $box = array() ) {
		
		return ! empty( $box ) && is_array( $box ) ? new Test_CMB2_Object( $box ) : FALSE;
	}
	
	/**
	 * Internal helper: Returns a CMB2 metabox configuration array. Can be passed a space-separated string or an array.
	 * Returns 'default' box if no key is passed or a key cannot be found--beware!
	 *
	 * Boxes have 'hookup' set to false.
	 *
	 * @since 2.XXX
	 *
	 * @param array|string|null $keys Box id or ids to add
	 * @param int               $opt  An integer to append to the default option string, allowing more than one set
	 *
	 * @return array|mixed
	 */
	public function cmb2_box_config( $keys = NULL, $opt = 0 ) {
		
		// make sure $opt is numeric and not larger than
		$opt = ! is_numeric( $opt ) ? '0' : (string) $opt;
		
		$field = array(
			'id'   => 'testfield',
			'name' => 'Test Field',
			'type' => 'text',
		);
		
		$default = array(
			'id'         => 'default',
			'title'      => 'Test Box',
			'fields'     => array(),
			'option_key' => $this->test_option_key . $opt,
		);
		
		$return = array();
		
		$keys = empty( $keys ) ? 'default' : $keys;
		$keys = $this->parse_box_keys( $keys );
		
		foreach ( $keys as $x => $key ) {
			
			$fie       = $field;
			$fie['id'] = $fie['id'] . $x;
			
			$return[ $key ]                         = $default;
			$return[ $key ]['id']                   = $key;
			$return[ $key ]['fields'][ $fie['id'] ] = $fie;
			
			$return[ $key ] = $return[ $key ] + $this->default_box_params;
			
			if ( ! empty( $this->test_menu_slug ) ) {
				$return[ $key ]['menu_slug'] = $this->test_menu_slug;
			}
		}
		
		return $return;
	}
	
	/**
	 * Internal helper: Returns an array of keys, or an empty array if no keys are sent
	 *
	 * @since 2.XXX
	 *
	 * @param string|array|null $keys
	 *
	 * @return array
	 */
	public function parse_box_keys( $keys = NULL ) {
		
		$return = ! is_string( $keys ) && ! is_array( $keys ) ? array() : $keys;
		
		if ( is_string( $return ) ) {
			$return = explode( ' ', $return );
		}
		
		return array_values( array_filter( $return, array( $this, 'array_element_is_string' ) ) );
	}
	
	/**
	 * Callback for array_filter: Tests whether array elements are strings, using is_string()
	 *
	 * @since 2.XXX
	 *
	 * @param $el
	 *
	 * @return bool
	 */
	public function array_element_is_string( $el ) {
		
		return is_string( $el ) && ! empty( $el );
	}
	
	/**
	 * Clears test properties which can be set by methods in this class, and removes CMB2 boxes
	 *
	 * @since 2.XXX
	 */
	public function clear_test_properties() {
		
		$boxes = CMB2_Boxes::get_all();
		
		foreach ( $boxes as $key => $box ) {
			CMB2_Boxes::remove( $key );
		}
		
		$pages = CMB2_Pages::get_all();
		
		foreach ( $pages as $key => $page ) {
			CMB2_Pages::remove( $key );
		}
		
		$this->test_menu_slug = '';
		$this->test_boxes     = array();
	}
	
	/**
	 * Option page CMB2 boxes store option keys as array, this returns index asked for from that prop
	 *
	 * @since 2.XXX
	 *
	 * @param \Test_CMB2_Object $cmb2
	 * @param int               $index
	 *
	 * @return bool|string
	 */
	public function get_option_key( \Test_CMB2_Object $cmb2, $index = 0 ) {
		
		$option_keys = $cmb2->prop( 'option_key' );
		
		return empty( $option_keys[ $index ] ) ? FALSE : $option_keys[ $index ];
	}
}