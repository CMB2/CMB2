<?php

/**
 * Class Test_CMB2_Pages
 *
 * Tests: \CMB2_Pages
 *
 * Method/Action                Tested by:
 * ------------------------------------------------------------------------------------------------------------
 * public add()                 test_CMBOptionsPages_add()                                    1
 * "                            test_invalid_CMBOptionsPages_add()                            1
 * public get()                 test_CMBOptionsPages_get_and_get_all()                        7
 * public get_all()             "                                                             -
 * public get_by_options_key()  test_CMBOptionsPages_get_by_options_key()                     6
 * public remove()              test_CMBOptionsPages_remove()                                 7
 * public clear()               test_CMBOptionsPages_clear()                                  3
 * ------------------------------------------------------------------------------------------------------------
 * 6 Methods                    6 Tests                                          Assertions: 25
 *
 * @since 2.XXX
 */
class Test_CMB2_Pages extends Test_CMB2_Options_Base {
	
	public function setUp() {
		
		parent::setUp();
		
		// remove any existing hookups
		CMB2_Pages::clear();
	}
	
	public function tearDown() {
		
		parent::tearDown();
	}
	
	/**
	 * Add a CMB2_Page_Hookup instance.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_pages
	 */
	public function test_CMBOptionsPages_add() {
	
		$page_hookup = $this->get_cmb2_page( 'test0', 'opt0' );
		
		/*
		 * Adding a page hookup object should return the page ID as confirmation
		 */
		
		$test = CMB2_Pages::add( $page_hookup );
		
		$this->assertEquals( 'test0', $test );
		
		unset( $page_hookup );
		$this->clear_test_properties();
	}
	
	/**
	 * @expectedException \TypeError
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group invalid
	 * @group cmb2_pages
	 */
	public function test_invalid_CMBOptionsPages_add() {
		
		/** @noinspection PhpParamsInspection */
		CMB2_Pages::add( 'test' );
	}
	
	
	/**
	 * Get a CMB2_Page_Hookup instance.
	 * Get all CMB2_Options_PageHookup instances.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_pages
	 */
	public function test_CMBOptionsPages_get_and_get_all() {
	
		$page_hookup1 = $this->get_cmb2_page( $page_id = 'test1', $opt = 'opt1' );
		$page_hookup2 = $this->get_cmb2_page( $page_id = 'test2', $opt = 'opt1' );
		
		$test1 = CMB2_Pages::add( $page_hookup1 );
		$test2 = CMB2_Pages::add( $page_hookup2 );
	
		$this->assertEquals( 'test1', $test1 );
		$this->assertEquals( 'test2', $test2 );
	
		/*
		 * Get for a legit key should return the exact object which was set
		 */
		
		$get = CMB2_Pages::get( 'test1' );
		
		$this->assertInstanceOf( 'CMB2_Page', $get );
		$this->assertTrue( $page_hookup1 === $get );
		
		/*
		 * Since setup clears this class, the count here is two.
		 */
		
		$getall = CMB2_Pages::get_all();
		
		$this->assertCount( 2, $getall );
		$this->assertContainsOnlyInstancesOf( 'CMB2_Page', $getall );
		$this->assertEquals( array( 'test1', 'test2' ), array_keys( $getall ) );
		
		unset( $page_hookup1, $page_hookup2 );
		$this->clear_test_properties();
	}
	
	/**
	 * By sending an options key, you can get all pages which save to that option key.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_pages
	 */
	public function test_CMBOptionsPages_get_by_options_key() {
	
		$page_hookup1 = $this->get_cmb2_page( $page_id = 'test3', $opt = 'opt1' );
		$page_hookup2 = $this->get_cmb2_page( $page_id = 'test4', $opt = 'opt1' );
		$page_hookup3 = $this->get_cmb2_page( $page_id = 'test5', $opt = 'opt2' );
		
		$test1 = CMB2_Pages::add( $page_hookup1 );
		$test2 = CMB2_Pages::add( $page_hookup2 );
		$test3 = CMB2_Pages::add( $page_hookup3 );
		
		$this->assertEquals( 'test3', $test1 );
		$this->assertEquals( 'test4', $test2 );
		$this->assertEquals( 'test5', $test3 );

		$getbykey = CMB2_Pages::get_by_options_key( 'opt1' );
		
		$this->assertCount( 2, $getbykey );
		$this->assertContainsOnlyInstancesOf( 'CMB2_Page', $getbykey );
		$this->assertEquals( array( 'test3', 'test4' ), array_keys( $getbykey ) );
		
		unset( $page_hookup1, $page_hookup2, $page_hookup3 );
		$this->clear_test_properties();
	}
	
	/**
	 * Remove an instance
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_pages
	 */
	public function test_CMBOptionsPages_remove() {
	
		$page_hookup1 = $this->get_cmb2_page( $page_id = 'test6', $opt = 'opt1' );
		$page_hookup2 = $this->get_cmb2_page( $page_id = 'test7', $opt = 'opt1' );
		
		$test1 = CMB2_Pages::add( $page_hookup1 );
		$test2 = CMB2_Pages::add( $page_hookup2 );
		
		$this->assertEquals( 'test6', $test1 );
		$this->assertEquals( 'test7', $test2 );
		
		$bye = CMB2_Pages::remove( 'test6' );
		
		// Returns true on successful remove
		$this->assertTrue( $bye );
		
		$getall = CMB2_Pages::get_all();
		
		$this->assertCount( 1, $getall );
		$this->assertContainsOnlyInstancesOf( 'CMB2_Page', $getall );
		$this->assertEquals( array( 'test7' ), array_keys( $getall ) );
		
		$bye = CMB2_Pages::remove( 'junk' );
		
		// Returns false on unsuccessful remove
		$this->assertFalse( $bye );
		
		unset( $page_hookup1, $page_hookup2 );
		$this->clear_test_properties();
	}
	
	/**
	 * Clear zeros out the pages array.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_pages
	 */
	public function test_CMBOptionsPages_clear() {
		
		$page_hookup1 = $this->get_cmb2_page( $page_id = 'test6', $opt = 'opt1' );
		$page_hookup2 = $this->get_cmb2_page( $page_id = 'test7', $opt = 'opt1' );
		
		$test1 = CMB2_Pages::add( $page_hookup1 );
		$test2 = CMB2_Pages::add( $page_hookup2 );
		
		$this->assertEquals( 'test6', $test1 );
		$this->assertEquals( 'test7', $test2 );
		
		CMB2_Pages::clear();
		
		$test = CMB2_Pages::get_all();
		
		unset( $page_hookup1, $page_hookup2 );
		$this->assertEmpty( $test );
	}
}