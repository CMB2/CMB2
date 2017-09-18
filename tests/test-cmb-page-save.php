<?php

/**
 * Class Test_CMB2_Page_Save
 *
 * Tests: \CMB2_Page_Save
 *
 * Method/Action                            Tested by:
 * -------------------------------------------------------------------------------------------------------------
 * public __construct()                     test_invalid_CMB2PageSave()                          1
 * "                                        test_CMB2PageSave_construct()                        1
 * public save_options()                    test_CMB2PageSave_save_options()                     5
 * protected can_save()                     test_CMB2PageSave_can_save()                         5
 * protected field_values_to_default()      test_CMB2PageSave_field_values_to_default()          2
 * -------------------------------------------------------------------------------------------------------------
 * 4 Tested                                 5 Tests                                 Assertions: 14
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Save extends Test_CMB2_Options_Base {
	
	
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
	 * @group cmb2_page_save
	 */
	public function test_invalid_CMB2PageSave() {
		
		/** @noinspection PhpParamsInspection */
		new CMB2_Page_Save();
		
		$this->clear_test_properties();
	}
	
	/**
	 * Construct the class
	 *
	 * @since 2.XXX
	 * @group method_magic
	 * @group method_public
	 * @group cmb2_page_save
	 */
	public function test_CMB2PageSave_construct() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$this->set_boxes( $cmbids[0] );
		$cmb2 = $this->test_boxes[ $cmbids[0] ];
		$opt  = $this->get_option_key( $cmb2 );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb2, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$MENU = new CMB2_Page_Save( $HOOKUP->page );
		
		$this->assertInstanceOf( 'CMB2_Page_Save', $MENU );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Save->save_options( $redirect = true )
	 *
	 * Saves submitted form values to WP. If redirect is set to false for testing purposes, it will return an
	 * array consisting of what happened ('reset', true, false) and the cmb_box_id for each box which was saved.
	 *
	 * Method does not have access to the actual values saved.
	 *
	 * Uses can_save(), tested below.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group cmb2_page_save
	 */
	public function test_CMB2PageSave_save_options() {
	
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		$cfg['fields'] = array(
			'field1' => array(
				'id' => 'field1',
				'title' => 'Field 1',
				'type' => 'text',
				'default' => 'Field 1 Default',
			),
			'field2' => array(
				'id' => 'field2',
				'title' => 'Field 2',
				'type' => 'text',
			),
			'field3' => array(
				'id' => 'field3',
				'title' => 'Field 3',
				'type' => 'text',
				'default' => 'Field 3 Default',
			),
		);
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
	
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$cmb_nonce = $HOOKUP->cmb->nonce();
		$nonce_field = wp_nonce_field( $cmb_nonce, $cmb_nonce, false, false );
		preg_match( '~value=[",\'](.+?)[",\']~', $nonce_field, $matches );
		$nonce_value = $matches[1];
		
		$_POST[ $cmb_nonce ] = $nonce_value;
		$_POST[ 'action' ] = $opt;
		$_POST[ 'submit-cmb' ] = 'Save';
		
		$_POST['field1'] = 'lorem ipsum';
		$_POST['field2'] = 'dolor nonummy';
		$_POST['field3'] = 'se precesis';
		
		/*
		 * Regular form submit; the value will be false because core CMB2 was_updated() will not evaluate
		 * in this environment. However, this does demonstrate this function is getting past its internal checks.
		 */
		
		$expect = array(
			'http://example.org/wp-admin/?updated=false',
			array(
				array(
					false,
					$cmbids[0]
				),
			),
		);
		$test = $this->invokeMethod( $SAVE, 'save_options', false );
		$this->assertEquals( $expect, $test );
		
		/*
		 * Reset form submit
		 */
		
		unset( $_POST[ 'submit-cmb' ] );
		$_POST[ 'reset-cmb' ] = 'Reset';
		$expect = array(
			'http://example.org/wp-admin/?updated=reset',
			array(
				array(
					'reset',
					$cmbids[0]
				),
			),
		);
		$test = $this->invokeMethod( $SAVE, 'save_options', false );
		$this->assertEquals( $expect, $test );
		
		/*
		 * The following will abort this method:
		 * - $_POST['submit-cmb'] or $_POST['reset-cmb'] not set
		 * - $_POST['action'] not set
		 * - $_POST['action'] not equal to option_key
		 */
		
		unset( $_POST[ 'reset-cmb' ] );
		$expect = array(
			'http://example.org/wp-admin/?updated=false',
			array(),
		);
		$test = $this->invokeMethod( $SAVE, 'save_options', false );
		$this->assertEquals( $expect, $test );
		
		$_POST['submit-cmb'] = 'Save';
		$_POST['action'] = 'junk';

		$test = $this->invokeMethod( $SAVE, 'save_options', false );
		$this->assertEquals( $expect, $test );
		
		unset( $_POST['action'] );
		
		$test = $this->invokeMethod( $SAVE, 'save_options', false );
		$this->assertEquals( $expect, $test );
		
		unset( $_POST );
		$this->clear_test_properties();
	}
	
	
	/**
	 * CMB2_Page_Save->field_values_to_default( $hookup )
	 * If the reset button was pressed, this method either returns field values to their default or
	 * "zeros" them out.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_save
	 */
	public function  test_CMB2PageSave_field_values_to_default() {
		
		/*
		 * The default reset_action is 'default', which turns posted fields back to their field default
		 */
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		$cfg['fields'] = array(
			'field1' => array(
				'id' => 'field1',
				'title' => 'Field 1',
				'type' => 'text',
				'default' => 'Field 1 Default',
			),
			'field2' => array(
				'id' => 'field2',
				'title' => 'Field 2',
				'type' => 'text',
			),
			'field3' => array(
				'id' => 'field3',
				'title' => 'Field 3',
				'type' => 'text',
				'default' => 'Field 3 Default',
			),
		);
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$_POST['field1'] = 'lorem ipsum';
		$_POST['field2'] = 'dolor nonummy';
		$_POST['field3'] = 'se precesis';
		
		$expect = array(
			'field1' => 'Field 1 Default',
			'field2' => '',
			'field3' => 'Field 3 Default',
		);
		
		$this->invokeMethod( $SAVE, 'field_values_to_default', $HOOKUP );
		
		$this->assertEquals( $expect, $_POST );
		
		$this->clear_test_properties();
		
		/*
		 * You can set the action to 'remove', which deletes the value entirely from the post field
		 */
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		$cfg['fields'] = array(
			'field1' => array(
				'id' => 'field1',
				'title' => 'Field 1',
				'type' => 'text',
				'default' => 'Field 1 Default',
			),
			'field2' => array(
				'id' => 'field2',
				'title' => 'Field 2',
				'type' => 'text',
			),
			'field3' => array(
				'id' => 'field3',
				'title' => 'Field 3',
				'type' => 'text',
				'default' => 'Field 3 Default',
			),
		);
		$cfg['reset_action'] = 'remove';
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$_POST['field1'] = 'lorem ipsum';
		$_POST['field2'] = 'dolor nonummy';
		$_POST['field3'] = 'se precesis';
		
		$expect = array(
			'field1' => '',
			'field2' => '',
			'field3' => '',
		);
		
		$this->invokeMethod( $SAVE, 'field_values_to_default', $HOOKUP );
		
		$this->assertEquals( $expect, $_POST );
		
		$this->clear_test_properties();
	}
	
	/**
	 * CMB2_Page_Save->can_save( CMB2_Options_Hookup $hookup )
	 * Checks if a field can be saved. A clone of core CMB2_hookup->can_save(), except allows an
	 * arbitrary box to be checked.
	 *
	 * Important: DOING_AUTOSAVE check cannot be tested.
	 *            The multisite test is also not performed.
	 *
	 * @since 2.XXX
	 * @group method_protected
	 * @group cmb2_page_save
	 */
	public function test_CMB2PageSave_can_save() {
		
		$cmbids = array(
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
			'test' . rand( 10000, 99999 ),
		);
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[0] ) );
		$cfg  = $cfgs[ $cmbids[0] ];
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$cmb_nonce = $HOOKUP->cmb->nonce();
		$nonce_field = wp_nonce_field( $cmb_nonce, $cmb_nonce, false, false );
		preg_match( '~value=[",\'](.+?)[",\']~', $nonce_field, $matches );
		$nonce_value = $matches[1];
		
		/*
		 * Make sure nonce is being checked
		 */
		
		$_POST[ $cmb_nonce ] = '77897883289392838943';
		$this->assertFalse( $this->invokeMethod( $SAVE, 'can_save', $HOOKUP ) );
		
		$_POST[ $cmb_nonce ] = $nonce_value;
		$this->assertTrue( $this->invokeMethod( $SAVE, 'can_save', $HOOKUP ) );
		
		/*
		 * Setting a filter should see test fail
		 */
		
		$filt = function() {
			return false;
		};
		add_filter( 'cmb2_can_save', $filt );
		
		$this->assertFalse( $this->invokeMethod( $SAVE, 'can_save', $HOOKUP ) );
		
		remove_filter( 'cmb2_can_save', $filt );
		
		$this->clear_test_properties();
		
		/*
		 * By setting 'save_fields' to false, this should fail.
		 */
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[1] ) );
		$cfg  = $cfgs[ $cmbids[1] ];
		
		$cfg['save_fields'] = false;
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$this->assertFalse( $this->invokeMethod( $SAVE, 'can_save', $HOOKUP ) );
		
		$this->clear_test_properties();
		
		/*
		 * By removing 'options-page' from the box_types(), this should fail.
		 * Note during regular use, it should not be possible for 'options-page'
		 * to be missing!
		 */
		
		$cfgs = $this->cmb2_box_config( array( $cmbids[2] ) );
		$cfg  = $cfgs[ $cmbids[2] ];
		
		$cfg['object_types'] = array( 'random' );
		
		$cmb = $this->get_cmb2( $cfg );
		$opt = $this->get_option_key( $cmb );
		
		$HOOKUP = new CMB2_Options_Hookup( $cmb, $opt );
		$HOOKUP->hooks();
		$HOOKUP->page->set_options_hookup( $HOOKUP );
		
		$SAVE = new CMB2_Page_Save( $HOOKUP->page );
		
		$this->assertFalse( $this->invokeMethod( $SAVE, 'can_save', $HOOKUP ) );
		
		$this->clear_test_properties();
	}
}