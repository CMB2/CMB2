<?php

/**
 * Class Test_CMB2_Options_Hookup
 *
 * Tests: CMB2_Options_Hookup
 *
 * Method/Action                               Tested by:
 * ------------------------------------------------------------------------------------------------------------
 * public __construct()                          test_invalid_CMB2OptionsHookup_no_params()         1
 * "                                             test_invalid_CMB2OptionsHookup_one_param()         1
 * "                                             test_invalid_CMB2OptionsHookup_bad_cmb2_object()   1
 * "                                             test_CMB2OptionsHookup_construct()                 3
 * public hooks()                                test_CMB2OptionsHookup_hooks()                     3
 * public add_options_metabox() #1               test_CMB2OptionsHookup_add_options_metabox()       2
 * public get_metabox()                          test_CMB2OptionsHookup_get_metabox()               2
 * public network_get_override() #2              test_CMB2OptionsHookup_network_get_override()      2
 * public network_update_override() #3           test_CMB2OptionsHookup_network_update_override()   4
 * protected get_page_hookup()                   test_CMB2OptionsHookup_get_page_hookup()           5
 * protected get_hooks()                         test_CMB2OptionsHookup_get_hooks()                 1
 * protected get_page_id()                       test_CMB2OptionsHookup_get_page_id()               2
 * magic __get()                                 test_CMB2OptionsHookup_get()                       2
 * magic __set()                                 test_CMB2OptionsHookup_set()                       5
 * "                                             test_invalid_CMB2OptionsHookup_set()               1
 * apply filter 'cmb2_options_hookup_hooks'      test_CMB2OptionsHookup_hooks()                     -
 * action 'wp_loaded'                            test_CMB2OptionsHookup_hooks_are_set()             4
 *        calls CMB2_Page->init()
 * action 'add_meta_boxes_{PAGEID}' #1           "                                                  -
 * action 'edit_form_top'                        "                                                  -
 *        calls  parent::add_context_metaboxes()
 * filter 'cmb2_override_option_get_{OPT}' #2    "                                                  -
 * filter 'cmb2_override_option_save_{OPT}' #3   "                                                  -
 * action 'cmb2_options_simple_page'             "                                                  -
 *        calls CMB2->show_form()
 * filter 'postbox_classes_{PAGEID}_{CMBID}'     "                                                  -
 *        calls parent::add_postbox_classes()
 * ------------------------------------------------------------------------------------------------------------
 * 19 Tested                                      17 Tests                             Assertions: 39
 *
 * @since 2.XXX
 */
class Test_CMB2_Options_Hookup extends Test_CMB2_Options_Base {
	
	public function setUp() {
		
		parent::setUp();
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
	 * @group cmb2_options_hookup
	 */
	public function test_invalid_CMB2OptionsHookup_no_params() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Options_Hookup();
	}
	
	/**
	 * @expectedException \PHPUnit\Framework\Error\Warning
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_options_hookup
	 */
	public function test_invalid_CMB2OptionsHookup_one_param() {
		
		$cmb2 = new Test_CMB2_Object( array( 'id' => 'test' ) );
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Options_Hookup( $cmb2 );
	}
	
	/**
	 * @expectedException \TypeError
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group invalid
	 * @group cmb2_options_hookup
	 */
	public function test_invalid_CMB2OptionsHookup_bad_cmb2_object() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Options_Hookup( 'test', 'test' );
	}
	
	/**
	 * Gets instance of CMB2_Options_Hookup and tests for properties set in constructor
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_construct() {
		
		$boxid  = 'test';
		$this->set_boxes( $boxid );
		
		$cmb2         = $this->test_boxes[ $boxid ];
		$opt          = $this->get_option_key( $cmb2 );
		$optkey_value = $this->test_option_key . '0';
		
		/*
		 * Passing valid parameters for first two parameters should return instance, with $this->cmb
		 * and $this->option_key set. $this->option_key is type-forced to string.
		 */
		
		$test = new CMB2_Options_Hookup( $cmb2, $opt );
		
		$this->assertInstanceOf( 'CMB2_Options_Hookup', $test );
		$this->assertInstanceOf( 'Test_CMB2_Object', $test->cmb );
		$this->assertEquals( $optkey_value, $test->option_key );
		
		$this->clear_test_properties();
	}
	
	/**
	 * Test overloading of set
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_set() {
		
		$boxid  = 'test';
		$this->set_boxes( $boxid );
		
		$cmb2         = $this->test_boxes[ $boxid ];
		$opt          = $this->get_option_key( $cmb2 );
		$optkey_value = $this->test_option_key . '0';
		
		/*
		 * For test purposes, $this->page_id, $this->page can be set via overloading
		 */
		
		$send_hookup = $this->get_cmb2_page();
		$send_page_id = $optkey_value;
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		
		$hookup->page_id = $send_page_id;
		$hookup->page    = $send_hookup;
		
		$this->assertInstanceOf( 'CMB2_Options_Hookup', $hookup );
		$this->assertInstanceOf( 'Test_CMB2_Object', $hookup->cmb );
		$this->assertInstanceOf( 'CMB2_Page', $hookup->page );
		$this->assertEquals( $optkey_value, $hookup->option_key );
		$this->assertEquals( $optkey_value, $hookup->page_id );
		
		$this->clear_test_properties();
	}
	
	/**
	 * @expectedException \Exception
	 *
	 * @since 2.XXX
	 * @group invalid
	 * @group method_magic
	 * @group cmb2_options_hookup
	 */
	public function test_invalid_CMB2OptionsHookup_set() {
		
		$boxid  = 'test';
		$this->set_boxes( $boxid );
		
		$cmb2         = $this->test_boxes[ $boxid ];
		$opt          = $this->get_option_key( $cmb2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		$hookup->bad = 'testing';
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->__get() accesses class properties.
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_get() {
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		
		/*
		 * Asking for an actual class property should return a value
		 */
		$this->assertEquals( $opt, $hookup->page_id );
		
		/*
		 * Asking for a random property should return null
		 */
		$this->assertNull( $hookup->something );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->add_options_metabox()
	 * calls add_meta_box using the ->cmb instance it contains.
	 * Note this uses cmb->show_on() as a test, failing if that method returns false, which is not tested here.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_add_options_metabox() {
	
		$cfgs = $this->cmb2_box_config( 'test' );
		$cfg = $cfgs['test'];
		
		/*
		 * If the title is removed, this should return false
		 */
		
		$cfg['title'] = '';
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->test_option_key;
		
		$hookup = new CMB2_Options_Hookup( $cmb, $opt );
		
		$this->assertFalse( $hookup->add_options_metabox() );
		
		unset( $hookup );
		
		/*
		 * Otherwise, should return the argument array sent to add_meta_box()
		 */
		
		$cfg['title'] = 'box title';
		$cmb = $this->get_cmb2( $cfg );
		
		$hookup = new CMB2_Options_Hookup( $cmb, $opt );
		
		$expect = array(
			'test',
			'box title',
			array( $hookup, 'get_metabox' ),
			$opt,
			$cmb->prop( 'context' ),
			$cmb->prop( 'priority' )
		);
		
		$this->assertEquals( $expect, $hookup->add_options_metabox() );
	
		unset( $hookup );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->get_metabox()
	 * calls $this->cmb->show_form( 0, $this->object_type ) and echoes or returns
	 * the results.
	 *
	 * The actual HTML returned is tested in the core tests, so this test will assert that the returned content
	 * is HTML by stripping tags from the returned value.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_get_metabox() {
	
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );

		$test     = $hookup->get_metabox( FALSE );
		$test     = $this->normalize_string( $test );
		$stripped = strip_tags( $test );
		
		/*
		 * By stripping the HTML tags, the only non-tag content will be the field name.
		 */
		
		$this->assertNotEquals( $stripped, $test );
		$this->assertEquals( 'Test Field', $stripped );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->get_page_hookup( $wp_menu_hook )
	 * gets an instance of CMB2_Page. It queries CMB2_Pages to see if one already exists
	 * for the page being asked for, and if not, gets a new instance.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_get_page_hookup() {
	
		$this->set_boxes( 'test test2' );
		$cmb2  = $this->test_boxes['test'];
		$cmb2a = $this->test_boxes['test2'];
		$opt   = $this->get_option_key( $cmb2 );
		
		/*
		 * First box should return an instance of CMB2_Page
		 */
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		$page   = $this->invokeMethod( $hookup, 'get_page_hookup', 'admin_menu' );
		
		$this->assertInstanceOf( 'CMB2_Page', $page );
		$this->assertEquals( 'admin_menu', $page->wp_menu_hook );
		$this->assertEquals( $opt, $page->page_id );
		$this->assertEquals( $opt, $page->option_key );
		
		/*
		 * Second box should return the same instance as the first box
		 */
		
		$hookup2 = new CMB2_Options_Hookup( $cmb2a, $opt );
		$page2   = $this->invokeMethod( $hookup2, 'get_page_hookup', 'admin_menu' );
		
		$this->assertTrue( $page === $page2 );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->hooks()
	 * Adds hooks specific to this metabox to wordpress.
	 *
	 * Includes calls to methods tested elsewhere:
	 * - CMB_Utils::prepare_hooks_array
	 * - CMB2_Utils::add_wp_hooks_from_config_array
	 * - CMB2_Options_Hookup->get_options_page_and_add_hookup
	 *
	 * Also allows use of this filter on the hooks array, tested below:
	 * - cmb2_options_page_hooks
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_hooks() {
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		/*
		 * Not filtered list should return the expected results. They are an array of arrays, each sub-array
		 * containing a key of the hook and a value of the 'id' parameter of the hooks array.
		 */
		
		$expect = array(
			array( 'wp_loaded' => 'admin_page_init' ),
			array( 'add_meta_boxes_cmb2_test_option_0' => 'add_meta_boxes' ),
			array( 'postbox_classes_cmb2_test_option_0_test' => 'postbox_classes' ),
		);
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		$hooks  = $hookup->hooks();
		
		$this->assertEquals( $expect, $hooks );
		
		unset( $hookup, $hooks );
		
		/*
		 * Filtering the list should change the return value to reflect the change. We'll change the 'wp_loaded'
		 * hook to 'admin_menu'.
		 */
		
		$expect = array(
			array( 'admin_menu' => 'admin_page_init' ),
			array( 'add_meta_boxes_cmb2_test_option_0' => 'add_meta_boxes' ),
			array( 'postbox_classes_cmb2_test_option_0_test' => 'postbox_classes' ),
		);
		
		add_filter( 'cmb2_options_hookup_hooks', function( $hooks, $instance ) use ( $cmb2, $opt ) {
			
			if ( $instance->cmb->cmb_id == $cmb2->cmb_id && $instance->page_id == $opt ) {
				foreach( $hooks as $key => $value ) {
					if ( $value['id'] == 'admin_page_init' ) {
						$hooks[ $key ]['hook'] = 'admin_menu';
						break;
					}
				}
			}
			
			return $hooks;
			
		}, 10, 2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		$hooks  = $hookup->hooks();
		
		$this->assertEquals( $expect, $hooks );
		
		unset( $hookup, $hooks );
		
		/*
		 * Messing up the filter list should return false (ie, no hooks were set). Because filters called as
		 * closures cannot be removed, the filter will be called with a lower priority here.
		 */
		
		add_filter( 'cmb2_options_hookup_hooks', function() use ( $cmb2, $opt ) {
			
			return 'oops';
			
		}, 11, 2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		$hooks  = $hookup->hooks();
		
		$this->assertFalse( $hooks );
		
		unset( $hookup, $hooks );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->network_get_override()
	 * Queries the site option as opposed to the regular option to get a value. This method uses the stored internal
	 * value of the option_key to query WP.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_network_get_override() {
		
		$test_string = 'cmb2_no_override_option_get';
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		// note that in a non-multisite environment, these both save to the same location
		update_option( $opt, 'regular' );
		update_site_option( $opt, 'network' );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		
		/*
		 * Asking for the option should return value
		 */
		
		$test = $hookup->network_get_override( $test_string );
		
		$this->assertEquals( 'network', $test );
		
		/*
		 * Asking for the option with the wrong test value should return false
		 */
		
		$test = $hookup->network_get_override( 'hm' );
		
		$this->assertFalse( $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->network_update_override()
	 * Sets site option to value passed if the test key matches
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_network_update_override() {
		
		$test_string = 'cmb2_no_override_option_save';
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		$hookup = new CMB2_Options_Hookup( $cmb2, $opt );
		
		/*
		 * Setting option should return true
		 */
		
		$test = $hookup->network_update_override( $test_string, 'testing' );
		
		$this->assertTrue( $test );
		$this->assertEquals( 'testing', get_site_option( $opt ) );
		
		/*
		 * Setting option with wrong key should return false
		 */
		
		$test = $hookup->network_update_override( 'hm', 'another test' );
		
		$this->assertFalse( $test );
		$this->assertEquals( 'testing', get_site_option( $opt ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->get_hooks( $wp_menu_hook )
	 * Returns an array of hooks to be added to WP, essentially the default set before parsing.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_get_hooks() {
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$page = $this->invokeMethod( $HOOKUP, 'get_page_hookup', 'admin_menu' );
		$HOOKUP->page = $page;
		$test = $this->invokeMethod( $HOOKUP, 'get_hooks', 'admin_menu' );
		
		$expected = array(
			array(
				'id'       => 'admin_page_init',
				'call'     => array( $page, 'init' ),
				'hook'     => 'wp_loaded',
				'priority' => 4,
			),
			array(
				'id'      => 'add_meta_boxes',
				'call'    => array( $HOOKUP, 'add_options_metabox' ),
				'hook'    => 'add_meta_boxes_' . $opt,
				'only_if' => true,
			),
			array(
				'id'      => 'add_context_metaboxes',
				'call'    => array( $HOOKUP, 'add_context_metaboxes' ),
				'hook'    => 'edit_form_top',
				'only_if' => false,
			),
			array(
				'id'      => 'cmb2_override_option_get',
				'call'    => array( $HOOKUP, 'network_get_override' ),
				'hook'    => 'cmb2_override_option_get_' . $opt,
				'only_if' => false,
				'type'    => 'filter',
				'args'    => 2,
			),
			array(
				'id'      => 'cmb2_override_option_save',
				'call'    => array( $HOOKUP, 'network_update_override' ),
				'hook'    => 'cmb2_override_option_save_' . $opt,
				'only_if' => false,
				'type'    => 'filter',
				'args'    => 2,
			),
			array(
				'id'      => 'cmb2_options_simple_page',
				'call'    => array( $cmb2, 'show_form' ),
				'hook'    => 'cmb2_options_simple_page',
				'only_if' => false,
				'args'    => 2,
			),
			array(
				'id'   => 'postbox_classes',
				'call' => array( $HOOKUP, 'postbox_classes' ),
				'hook' => 'postbox_classes_' . $opt . '_test',
				'type' => 'filter',
			),
		);
		
		$this->assertEquals( $expected, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Options_Hookup->get_page_id()
	 * Returns the page ID--either option_key or menu_slug
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_get_page_id() {
		
		/*
		 * Without a menu slug, the option key will be the page ID
		 */
		
		$cfgs = $this->cmb2_box_config( 'test test2' );
		$cfg = $cfgs['test'];
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->test_option_key;
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		
		$expect = $opt;
		$test = $this->invokeMethod( $HOOKUP, 'get_page_id' );
		
		$this->assertEquals( $expect, $test );
		
		/*
		 * Setting the menu_slug should see that returned as the page ID
		 */
		
		$cfg2 = $cfgs['test2'];
		$cfg2['menu_slug'] = 'testslug';
		
		$cmb = $this->get_cmb2( $cfg2 );
		$opt = $this->test_option_key;
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		
		$expect = 'testslug';
		$test = $this->invokeMethod( $HOOKUP, 'get_page_id' );
		
		$this->assertEquals( $expect, $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * Check to see if the hooks were actually set
	 *
	 * @since 2.XXX
	 * @group apply_filter
	 * @group cmb2_options_hookup
	 */
	public function test_CMB2OptionsHookup_hooks_are_set() {
		
		$this->set_boxes( 'test' );
		$cmb2 = $this->test_boxes['test'];
		$opt  = $this->get_option_key( $cmb2 );
		
		$expect = array(
			array( 'wp_loaded' => 'admin_page_init' ),
			array( 'add_meta_boxes_cmb2_test_option_0' => 'add_meta_boxes' ),
			array( 'postbox_classes_cmb2_test_option_0_test' => 'postbox_classes' ),
		);
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$test = $HOOKUP->hooks();
		
		$this->assertEquals( $expect, $test );
		$this->assertNotFalse(
			has_filter( 'wp_loaded', array( $HOOKUP->page, 'init' ) ) );
		$this->assertNotFalse(
			has_filter( 'add_meta_boxes_cmb2_test_option_0', array( $HOOKUP, 'add_options_metabox' ) ) );
		$this->assertNotFalse(
			has_filter( 'postbox_classes_cmb2_test_option_0_test', array( $HOOKUP, 'postbox_classes' ) ) );
		
		$this->clear_test_properties();
	}
}