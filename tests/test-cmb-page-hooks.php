<?php

/**
 * Class Test_CMB2_Page_Hooks
 *
 * Tests: \CMB2_Page_Hooks
 *
 * Method/Action                                Tested by:
 * -----------------------------------------------------------------------------------------------------------------
 * public __construct()                         test_invalid_CMB2PageHooks()                                 1
 * "                                            test_CMB2PageHooks_construct()                               2
 * public hooks()                               test_CMB2PageHooks_hooks()                                   8
 * protected get_hooks()                        test_CMB2PageHooks_get_hooks()                               2
 * protected hooks_array()                      test_CMB2PageHooks_hooks_array()                             7
 * magic __get()                                test_CMB2PageHooks_construct()                               -
 * apply filter 'cmb2_options_pagehooks'        test_CMB2PageHooks_hooks_array()                             -
 * add action {MENUHOOK}                        test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_to_admin_menu()
 * add action {MENUHOOK}                        test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_update_notice()
 * add action {MENUHOOK}                        test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_registered_setting()
 * add action 'admin_enqueue_scripts'           test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_postbox_script()
 * add action 'admin_print_footer_scripts'      test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_postbox_toggle()
 * add action 'admin_post_{OPTKEY}'             test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->save()
 * add action 'load-{PAGEHOOK}'                 test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_Page->add_metaboxes()
 * add action 'admin_print_styles-{PAGEHOOK}'   test_CMB2PageHooks_hooks()                                   -
 *    calls CMB2_hookup->enqueue_cmb_css()
 * -----------------------------------------------------------------------------------------------------------------
 * 12 Tested                                    5 Tests                                         Assertions: 20
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Hooks extends Test_CMB2_Options_Base {
	
	
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
	 * @group method_construct
	 * @group method_public
	 * @group cmb2_page_hooks
	 */
	public function test_invalid_CMB2PageHooks() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page_Hooks();
		
		$this->clear_test_properties();
	}
	
	/**
	 * Construct the class
	 *
	 * @since 2.XXX
	 * @group method_construct
	 * @group method_public
	 * @group cmb2_page_hooks
	 */
	public function test_CMB2PageHooks_construct() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$HOOKS = new CMB2_Page_Hooks( $HOOKUP->page );
		$this->assertInstanceOf( 'CMB2_Page_Hooks', $HOOKS );
		
		$test = $HOOKS->page;
		$this->assertInstanceOf( 'CMB2_Page', $test );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Hooks->hooks()
	 * Public interface with this class. Uses get_hooks() and hooks_array(), tested below, to get hooks.
	 * Will test the hooks set in this context.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page_hooks
	 */
	public function test_CMB2PageHooks_hooks() {
		
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
		
		$HOOKS = new CMB2_Page_Hooks( $HOOKUP->page );
		
		$expect = array(
			array( 'admin_menu' => 'menu_hook' ),
			array( 'admin_menu' => 'updated' ),
			array( 'admin_menu' => 'register_setting' ),
			array( 'admin_post_' . $opt => 'save_options' ),
		);
		
		$test = $HOOKS->hooks();
		
		$this->assertEquals( $expect, $test );
		
		$this->assertNotFalse( has_filter( 'admin_menu', array( $HOOKUP->page, 'add_to_admin_menu' ) ) );
		$this->assertNotFalse( has_filter( 'admin_menu', array( $HOOKUP->page, 'add_update_notice' ) ) );
		$this->assertNotFalse( has_filter( 'admin_menu', array( $HOOKUP->page, 'add_registered_setting' ) ) );
		$this->assertNotFalse( has_filter( 'admin_post_' . $opt, array( $HOOKUP->page, 'save' ) ) );
		
		$HOOKUP->page->page_hook = 'testhook';
		
		$expect = array(
			array( 'load-testhook' => 'add_metaboxes' ),
			array( 'admin_print_styles-testhook' => 'add_cmb_css_to_head' ),
		);
		
		$test = $HOOKS->hooks();
		
		$this->assertEquals( $expect, $test );
		
		$this->assertNotFalse( has_filter( 'load-testhook', array( $HOOKUP->page, 'add_metaboxes' ) ) );
		$this->assertNotFalse( has_filter( 'admin_print_styles-testhook', array( 'CMB2_hookup', 'enqueue_cmb_css' ) ) );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Hooks->hooks_array( $hooks, $tokens = array() )
	 * Normalizes the page hooks array, including replacing tokens, if present.
	 * Applies filter to hooks
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_hooks
	 */
	public function test_CMB2PageHooks_hooks_array() {
	
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$HOOKS = new CMB2_Page_Hooks( $HOOKUP->page );
		
		/*
		 * Hooks without ID should be removed
		 * Hooks with 'call' which is not callable should be removed
		 * Hooks where 'only_if' is set to false should be removed
		 */
	
		$hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
				'hook' => 'test_hook',
			),
			array(
				'call' => 'phpversion',
				'hook' => 'test_hook',
			),
			array(
				'id' => 'bad-no-call',
				'call' => 'something_not_callable',
				'hook' => 'test_hook',
			),
			array(
				'id' => 'bad-only_if',
				'call' => 'phpversion',
				'hook' => 'test_hook',
				'only_if' => false,
			),
		);
		
		$expect = array(
			array(
				'id' => 'ok_hook',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		);
		
	    $test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
		
	    /*
	     * Garbage parameters should be removed
	     */
	    
	    $hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
				'hook' => 'test_hook',
				'random' => 'something',
			),
		);
	    
	    $expect = array(
			array(
				'id' => 'ok_hook',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		);
	    
	    $test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
	    
	    /*
	     * Priority will return intval, or 10 if intval is 0
	     * Args will return intval, or 1 if intval is 0
	     * Type will be turned into 'action' if garbage
	     */
	    
	     $hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
				'hook' => 'test_hook',
				'priority' => '56',
				'args' => 'hello',
				'type' => 'filter',
			),
		     array(
				'id' => 'ok_hook2',
				'call' => 'phpversion',
				'hook' => 'test_hook',
				'priority' => 'hello',
				'args' => '3',
				'type' => 'garbage',
			),
		);
	    
	    $expect = array(
			array(
				'id' => 'ok_hook',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'filter',
				'priority' => 56,
				'args' => 1
			),
		    array(
				'id' => 'ok_hook2',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 3
			),
		);
	 
		$test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
	    
	    /*
	     * Within this class, the default hook is value of 'admin_menu_hook' on box config
	     */
	    
	    $hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
			),
		     array(
				'id' => 'ok_hook2',
				'call' => 'phpversion',
				'hook' => 'test_hook',
			),
		);
	    
	    $expect = array(
			array(
				'id' => 'ok_hook',
				'hook' => 'admin_menu',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		    array(
				'id' => 'ok_hook2',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		);
	    
	    $test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
		
	    /*
	     * If tokens are passed, they are substituted. Tokens have no particular format and are just str_replaced.
	     */
	    
	    $hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
				'hook' => '{SOMETHING}',
			),
		     array(
				'id' => 'ok_hook2',
				'call' => 'phpversion',
				'hook' => 'test_hook',
			),
		);
	    
	    $expect = array(
		    array(
				'id' => 'ok_hook',
				'hook' => '{SOMETHING}',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		    array(
				'id' => 'ok_hook2',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
	    );
	    
	    $test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
	    
	    $tokens = array(
	        '{SOMETHING}' => 'something_hook'
	    );
	    
	    $expect = array(
		    array(
				'id' => 'ok_hook',
				'hook' => 'something_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		    array(
				'id' => 'ok_hook2',
				'hook' => 'test_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
	    );
	    
		$test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array, $tokens );
	    $this->assertEquals( $expect, $test );
		
	    /*
	     * The hooks list is filterable, hooks added will be normalized per above
	     */
	  
	     $hook_array = array(
			array(
				'id' => 'ok_hook',
				'call' => 'phpversion',
			),
		     array(
				'id' => 'ok_hook2',
				'call' => 'phpversion',
				'hook' => 'test_hook',
			),
		);
	 
		$filter = function( $hooks ) {
			
			unset( $hooks[1] );
			$hooks[] = array(
				'id' => 'filter_1',
				'call' => 'phpversion',
				'hook' => 'filtered_hook',
			);
			return $hooks;
		};
	    
	    $expect = array(
			array(
				'id' => 'ok_hook',
				'hook' => 'admin_menu',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		    array(
				'id' => 'filter_1',
				'hook' => 'filtered_hook',
				'only_if' => true,
				'call' => 'phpversion',
				'type' => 'action',
				'priority' => 10,
				'args' => 1
			),
		);
	    
	    add_filter( 'cmb2_options_pagehooks', $filter );
	    
	    $test = $this->invokeMethod( $HOOKS, 'hooks_array', $hook_array );
	    $this->assertEquals( $expect, $test );
	    
	    remove_filter( 'cmb2_options_pagehooks', $filter );
	    
	    $this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Hooks->get_hooks()
	 * Returns a set of page hooks based on whether the property 'page_hook' has been set in CMB2_Page
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_hooks
	 */
	public function test_CMB2PageHooks_get_hooks() {
		
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
		
		$HOOKS = new CMB2_Page_Hooks( $HOOKUP->page );
		
		$expected_early = array(
			array(
				'id'   => 'menu_hook',
				'hook' => 'admin_menu',
				'call' => array( $HOOKUP->page, 'add_to_admin_menu' ),
			),
			array(
				'id'       => 'updated',
				'hook'     => 'admin_menu',
				'call'     => array( $HOOKUP->page, 'add_update_notice' ),
				'priority' => 11,
			),
			array(
				'id'   => 'register_setting',
				'hook' => 'admin_menu',
				'call' => array( $HOOKUP->page, 'add_registered_setting' ),
			),
			array(
				'id'      => 'postbox',
				'hook'    => 'admin_enqueue_scripts',
				'call'    => array( $HOOKUP->page, 'add_postbox_script' ),
				'only_if' => false,
			),
			array(
				'id'      => 'toggle',
				'hook'    => 'admin_print_footer_scripts',
				'call'    => array( $HOOKUP->page, 'add_postbox_toggle' ),
				'only_if' => false,
			),
			array(
				'id'   => 'save_options',
				'hook' => 'admin_post_' . $opt,
				'call' => array( $HOOKUP->page, 'save' ),
			),
		);
		$expected_late = array(
			array(
					'id'   => 'add_metaboxes',
					'hook' => 'load-testhook',
					'call' => array( $HOOKUP->page, 'add_metaboxes' ),
			),
			array(
				'id'      => 'add_cmb_css_to_head',
				'hook'    => 'admin_print_styles-testhook',
				'call'    => array( 'CMB2_hookup', 'enqueue_cmb_css' ),
				'only_if' => TRUE,
			),
		);
		
		$test = $this->invokeMethod( $HOOKS, 'get_hooks' );
	    $this->assertSame( $expected_early, $test );
	    
	    $HOOKUP->page->page_hook = 'testhook';
	    
	    $test = $this->invokeMethod( $HOOKS, 'get_hooks' );
	    $this->assertSame( $expected_late, $test );
		
		$this->clear_test_properties();
	}
}