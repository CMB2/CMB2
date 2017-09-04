<?php

require_once( 'cmb-tests-base.php' );

/**
 * Class Test_CMB2_Page_Utils
 *
 * Tests: \CMB2_Page_Utils
 *
 * Method/Action                              Tested by:
 * ------------------------------------------------------------------------------------------------------------
 * public prepare_hooks_array()               test_prepare_hooks_array()                    6
 * public add_wp_hooks_from_config_array()    test_add_wp_hooks_from_config_array()         4
 * public replace_tokens_in_array()           test_replace_tokens_in_array()                8
 * public do_void_action()                    test_do_void_action()                        20
 * public check_args()                        test_check_args()                            55
 * public array_replace_recursive_strict()    test_array_replace_recursive_strict()         8
 * ------------------------------------------------------------------------------------------------------------
 * 6 Tested                                   6 Tests                                     101 Assertions
 *
 * @since 2.XXX
 */
class Test_CMB2_Page_Utils extends Test_CMB2 {
	
	/**
	 * @var CMB2_Page_Utils
	 */
	protected static $CLASS;
	
	public function setUp() {
		parent::setUp();

		self::$CLASS = 'CMB2_Page_Utils';
	}

	public function tearDown() {
		parent::tearDown();
	}
	
	/**
	 * CMB_Utils::prepare_hooks_array( $hooks_array = [], $default_hook = '', $tokens = [] )
	 * Normalizes hooks arrays, including substituting tokens if sent.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_prepare_hooks_array() {

		$UTIL  = self::$CLASS;
		$CHECK = 'prepare_hooks_array';
		
		$token = array( '{PHP}' => 'phpversion' );
		$test = array(
			array(
				'id' => 'ok-full',
				'hook' => 'test_hook',
				'only_if' => true,
				'type' => 'action',
				'priority' => 10,
				'args' => 1,
				'call' => 'phpversion',
			),
			array(
				'id' => 'ok-partial',
				'hook' => 'test_hook',
				'call' => 'phpversion',
			),
			array(
				'id' => 'ok-partial-if-default-hook-set',
				'call' => 'phpversion',
			),
			array(
				'hook' => 'bad-no-id',
				'call' => 'phpversion',
			),
			array(
				'id' => 'bad-no-call',
				'hook' => 'test_hook',
			),
			array(
				'id' => 'bad-not-callable',
				'hook' => 'test_hook',
				'call' => 'some_random_test_thing_which_is_not_function',
			),
			array(
				'id' => 'bad-only_if-set-false',
				'hook' => 'test_hook',
				'call' => 'phpversion',
				'only_if' => false,
			),
			array(
				'id' => 'bad-type-not-filter-or-action',
				'hook' => 'test_hook',
				'call' => 'phpversion',
				'type' => 'blah',
			),
			array(
				'id' => 'ok-if-tokens-sent',
				'hook' => 'test_hook',
				'call' => '{PHP}',
			)
		);
		$expect = array(
			array(
				'id' => 'ok-full',
				'hook' => 'test_hook',
				'only_if' => true,
				'type' => 'action',
				'priority' => 10,
				'args' => 1,
				'call' => 'phpversion',
			),
			array(
				'id' => 'ok-partial',
				'hook' => 'test_hook',
				'only_if' => true,
				'type' => 'action',
				'priority' => 10,
				'args' => 1,
				'call' => 'phpversion',
			),
		);
		$expect_with_default = $expect;
		$expect_with_default[] = array(
			'id' => 'ok-partial-if-default-hook-set',
			'hook' => 'test_hook',
			'only_if' => true,
			'type' => 'action',
			'priority' => 10,
			'args' => 1,
			'call' => 'phpversion',
		);
		$expect_with_tokens = $expect;
		$expect_with_tokens[] = array(
			'id' => 'ok-if-tokens-sent',
			'hook' => 'test_hook',
			'only_if' => true,
			'type' => 'action',
			'priority' => 10,
			'args' => 1,
			'call' => 'phpversion',
		);
		
		/*
		 * Return false:
		 * - Calling method without parameters
		 * - Calling method with empty first parameter
		 * - Calling method with first parameter not an array
		 */
		$this->assertFalse( $UTIL::$CHECK() );
		$this->assertFalse( $UTIL::$CHECK( array() ) );
		$this->assertFalse( $UTIL::$CHECK( 'test' ) );
		
		// $test --> $expect
		$this->assertEquals( $expect, $UTIL::$CHECK( $test ) );
		
		// $test, 'test_hook' --> $expect_with_default
		$this->assertEquals( $expect_with_default, $UTIL::$CHECK( $test, 'test_hook' ) );
		
		// $test, '', $token --> $expect_with_token
		$this->assertEquals( $expect_with_tokens, $UTIL::$CHECK( $test, '', $token ) );
	}
	
	/**
	 * CMB_Utils::add_wp_hooks_from_config_array( $hooks_array = [], $default_hook = '', $tokens = [] )
	 *
	 * Adds WP hooks set by hooks array. Uses CMB_Utils::prepare_hooks_array() to normalize hooks.
	 * Essentially a void function; returns an array of hooks set to enable testing/checks.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_add_wp_hooks_from_config_array() {
		
		$UTIL  = self::$CLASS;
		$CHECK = 'add_wp_hooks_from_config_array';
		
		$token = array( '{PHP}' => 'phpversion' );
		$test = array(
			array(
				'id' => 'ok-full',
				'hook' => 'test_hook',
				'only_if' => true,
				'type' => 'action',
				'priority' => 10,
				'args' => 1,
				'call' => 'phpversion',
			),
			array(
				'id' => 'ok-partial',
				'hook' => 'test_hook',
				'call' => 'phpversion',
			),
			array(
				'id' => 'ok-partial-if-default-hook-set',
				'call' => 'phpversion',
			),
			array(
				'hook' => 'bad-no-id',
				'call' => 'phpversion',
			),
			array(
				'id' => 'bad-no-call',
				'hook' => 'test_hook',
			),
			array(
				'id' => 'bad-not-callable',
				'hook' => 'test_hook',
				'call' => 'some_random_test_thing_which_is_not_function',
			),
			array(
				'id' => 'bad-only_if-set-false',
				'hook' => 'test_hook',
				'call' => 'phpversion',
				'only_if' => false,
			),
			array(
				'id' => 'bad-type-not-filter-or-action',
				'hook' => 'test_hook',
				'call' => 'phpversion',
				'type' => 'blah',
			),
			array(
				'id' => 'ok-if-tokens-sent',
				'hook' => 'test_hook',
				'call' => '{PHP}',
			),
		);
		
		$expect                = array( array( 'test_hook' => 'ok-full' ), array( 'test_hook' => 'ok-partial' ), );
		
		$expect_with_default   = $expect;
		$expect_with_default[] = array( 'test_hook' => 'ok-partial-if-default-hook-set' );
		
		$expect_with_tokens    = $expect;
		$expect_with_tokens[]  = array( 'test_hook' => 'ok-if-tokens-sent' );
		
		// Return false for empty hooks array
		$this->assertFalse( $UTIL::$CHECK( 'test' ) );
		
		// $test --> $expect
		$this->assertEquals(
			$expect,
			$UTIL::$CHECK( $test )
		);
		
		// $test, 'test_hook' --> $expect_with_default
		$this->assertEquals(
			$expect_with_default,
			$UTIL::$CHECK( $test, 'test_hook' )
		);
		
		// $test, '', $token --> $expect_with_token
		$this->assertEquals(
			$expect_with_tokens,
			$UTIL::$CHECK( $test, '', $token )
		);
	}
	
	/**
	 * CMB_Utils::replace_tokens_in_array( $array = [], $tokens = [], $keys = false )
	 *
	 * Recursive function to replace arbitrary tokens in submitted array. Can replace tokens in keys.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_replace_tokens_in_array() {
		
		$UTIL  = self::$CLASS;
		$CHECK = 'replace_tokens_in_array';
		
		$tokens = array(
			'{STRING}' => 'Test',
			'{INT}' => 12,
			'{BOOL}' => true,
			'{CALLABLE}' => array( 'CMB_Utils', 'replace_tokens_in_array' ),
			'{ARRAY}' => array( 1, 2, 3 ),
		);
		$test = array(
			'a' => 'STRING is not a token',
			'b' => '{STRING}',
			'c' => 'Middle of {STRING} test',
			'd' => '{INT}',
			'e' => 345,
			'f' => false,
			'g' => array( 'gg' => array( 'ggg' => '{STRING}' ) ),
			'h' => '{BOOL}',
			'i' => '{BOOL} bool within string',
			'j' => '{CALLABLE}',
			'k' => '{ARRAY}',
			'l' => '{ARRAY} non-scalar within string',
			'm' => '{STRING} and {STRING}',
			'n' => '{STRING} and {BOOL}',
			'{STRING}' => 'key check',
		);
		$expect = array(
			'a' => 'STRING is not a token',
			'b' => 'Test',
			'c' => 'Middle of Test test',
			'd' => '12',
			'e' => 345,
			'f' => false,
			'g' => array( 'gg' => array( 'ggg' => 'Test' ) ),
			'h' => true,
			'i' => true,
			'j' => array( 'CMB_Utils', 'replace_tokens_in_array' ),
			'k' => array( 1, 2, 3 ),
			'l' => array( 1, 2, 3 ),
			'm' => 'Test and Test',
			'n' => true,
			'{STRING}' => 'key check',
		);
		
		$expect_keys = $expect;
		unset( $expect_keys['{STRING}'] );
		$expect_keys['Test'] = 'key check';
		
		// Return empty array
		$this->assertEmpty( $UTIL::$CHECK() );
		$this->assertEmpty( $UTIL::$CHECK( array(), $tokens ) );
		
		// Return original untouched
		$this->assertEquals( $test,  $UTIL::$CHECK( $test ) );
		$this->assertEquals( 'test', $UTIL::$CHECK( 'test', $tokens ) );
		$this->assertEquals( $test,  $UTIL::$CHECK( $test, array() ) );
		$this->assertEquals( $test,  $UTIL::$CHECK( $test, 'tokens' ) );
		
		// Without examining keys: $test, $tokens --> $expect
		$this->assertEquals(
			$expect,
			$UTIL::$CHECK( $test, $tokens )
		);
		
		// Looking at keys: $test, $tokens, true --> $expect_keys
		$this->assertEquals(
			$expect_keys,
			$UTIL::$CHECK( $test, $tokens, true )
		);
	}
	
	/**
	 * CMB2_Utils::do_void_action( $args = [], $checks = [], $call = 'do_action' )
	 *
	 * Allows returning a value (usually a string) from a function which may echo its
	 * output. Uses call_user_func_array().
	 *
	 * Called with or without value checking:
	 *
	 * $args  = array( $var1, $var2 )      Arbitrary arguments array
	 * $check = array( $check1, $check2 )  Check to perform against matching argument positions. See function below.
	 * $call  = 'callable'                 Callable
	 *
	 * CMB2_Utils::do_void_action( $args, $checks, $call )  With checking
	 * CMB2_Utils::do_void_action( $args, $call )           Without checking
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_do_void_action() {
		
		$UTIL  = self::$CLASS;
		$CHECK = 'do_void_action';
		
		$string      = '1234567890';
		$add_echo    = 'abc';
		$add_ret     = 'zyx';
		$args        = array( $string );
		
		$call = function( $string, $echo = false ) use ( $add_echo, $add_ret ) {
			if ( $echo ) {
				echo $string . $add_echo;
			} else {
				return $string . $add_ret;
			}
		};
		
		// Empty string returned for no arguments:
		$this->assertEmpty( $UTIL::$CHECK() );
		
		// Empty string returned for these parameter 1 conditions: - param 1 empty  - param 1 not array
		$this->assertEmpty( $UTIL::$CHECK( $string ) );
		$this->assertEmpty( $UTIL::$CHECK( $string, $call ) );
		$this->assertEmpty( $UTIL::$CHECK( $string, array( $string ), $call ) );
		$this->assertEmpty( $UTIL::$CHECK( array() ) );
		$this->assertEmpty( $UTIL::$CHECK( array(), $call ) );
		$this->assertEmpty( $UTIL::$CHECK( array(), array( $string ), $call ) );
		
		// Empty string returned for these parameter 2 conditions: not callable and not array
		$this->assertEmpty( $UTIL::$CHECK( $args, 'bad' ) );
		
		// Empty string returned if neither second or third parameter are callable
		$this->assertCount( 1, $args );
		$this->assertEmpty( $UTIL::$CHECK( $args, 'bad', 'bad' ) );
		$this->assertEmpty( $UTIL::$CHECK( $args, array( $string ), 'bad' ) );
		
		// Because the default action  a void function, returns empty if successfully called without echo
		$this->assertEmpty( $UTIL::$CHECK( $args ) );
		
		// Returns original string + add_ret if not sent $echo param. 'do_action' is the method's default callable
		$this->assertEquals( $string . $add_ret, $UTIL::$CHECK( $args, $call ) );
		
		// Returns original string + add_echo if action returns instead of echoing
		$args[] = true;
		$this->assertCount( 2, $args );
		$this->assertEquals( $string . $add_echo, $UTIL::$CHECK( $args, $call ) );
		
		// Returns empty string if the check array arguments are bad
		$this->assertEmpty( $UTIL::$CHECK( $args, array( 'arbitrary' ), $call ) );
		
		// Returns original string + add_echo if checks are OK for argument position 1
		$this->assertEquals( $string . $add_echo, $UTIL::$CHECK( $args, array( $string ), $call ) );
		
		// Returns empty string if the check array arguments are bad for argument 2
		$this->assertEmpty( $UTIL::$CHECK( $args, array( null, false ), $call ) );
		
		// Returns original string + add_echo if checks are OK  for argument 2
		$this->assertEquals(
			$string . $add_echo,
			$UTIL::$CHECK( $args, array( null, true ), $call )
		);
		$this->assertEquals(
			$string . $add_echo,
			$UTIL::$CHECK( $args, array( $string, true ), $call )
		);
	}
	
	/**
	 * CMB2_Utils::check_args( $values = array(), $checks = array(), $skip = true )
	 *
	 * Tests the arguments array against allowable values. It uses type checking on the values, so truthy values
	 * will not evaluate as ok (ie, (int) 1 is not (bool) true ). $skip allows testing for null.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_check_args() {
		
		$UTIL  = self::$CLASS;
		$CHECK = 'check_args';
		
		$check = array( 'a' ); // check string
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( null ),                                true ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a' ),                                 true ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 'a', 'b' ) ),                   true ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'b' ),                                 true ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'b' ) ),                        true ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a' ),                                false ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 'a' ) ),                       false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( null ),                               false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'b' ),                                false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'b' ) ),                       false ) );
		
		$check = array( 4 ); // check int
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 4 )                                         ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 3, 4 ) )                             ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 5 )                                         ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( '4' )                                       ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 3, 5 ) )                             ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 3, '4' ) )                           ) );
		
		$check = array( true ); // check bool
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( true )                                      ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( true ) )                             ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( false )                                     ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'true' )                                    ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 1 )                                         ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( false ) )                            ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 1, 'true' ) )                        ) );
		
		$check = array( null ); // check null
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( null )                                      ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( null ) )                             ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 1, null ) )                          ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'null' )                                    ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( false, 0, 'null' ) )                 ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( null ),                               false ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( null ) ),                      false ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 1, null ) ),                   false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'null' ),                             false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'null' ) ),                    false ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( false, 0, 'null' ) ),          false ) );
		
		$check = array( array( 'a', 'b' ) ); // check array [ 'a', 'b' ]
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( array( 'a', 'b' ) ) )                ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'a', 'b' ) )                         ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'a', 'c' ) )                         ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( array( 'a', 'c' ), array( 'b' ) ) )  ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'a', 'b' )                                  ) );
		
		$chk_obj = (object) array( 'a' => 'test' );
		$bad_obj = (object) array( 'a' => 'test' );
		
		$check = array( $chk_obj ); // check object, should require same instance
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( $chk_obj )                                  ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( $bad_obj, $chk_obj ) )               ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( array( 'a' => 'test' ) )                    ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'test' )                                    ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( $bad_obj )                                  ) );
		
		$check = array( 'a', 'b' ); // check two separate values
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a' )                                       ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( null, 'b' )                                 ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( null, 'b' ),                          false ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( array( 'a', 'b' ), 'b' )                    ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a', array( 'a', 'b' ) )                    ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a', 'b' )                                  ) );
		
		$check = array( 'z' => 'test' ); // check string keyed array
		
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'z' => 'test' )                             ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'z' => array( 'hm', 'test' ) )              ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'test' )                                    ) );
		$this->assertTrue(  $UTIL::$CHECK( $check, array( 'a' => 'test' )                             ) );
		$this->assertFalse( $UTIL::$CHECK( $check, array( 'z' => 'hm' )                               ) );
	}
	
	/**
	 * Array replace, but with type-checking of vars enabled.
	 *
	 * @since 2.XXX
	 * @group method_public
	 * @group method_static
	 * @group cmb2_page_utils
	 */
	public function test_array_replace_recursive_strict() {
		
		$UTIL  = self::$CLASS;
		$CHECK = 'array_replace_recursive_strict';
		
		$array = array( 'a', 'b', 'c' );
		$replace = array( 1 => 'z' );
		$expect = array( 'a', 'z', 'c' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'a', 'b', 'c' );
		$replace = array( 1 => 'z', 3 => 'w' );
		$expect = array( 'a', 'z', 'c' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'a', 'b', 'c' );
		$replace = array( 1 => 5, );
		$expect = array( 'a', 'b', 'c' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'a', null, 'c' );
		$replace = array( 1 => 5, );
		$expect = array( 'a', 5, 'c' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'a', 'b', 'c' );
		$replace = array( 1 => 'z', 3 => 'w' );
		$expect = array( 'a', 'z', 'c', 'w' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace, true ) );
		
		$array = array( 'test' => 'a', 'another' => 'b', 'third' => 'c' );
		$replace = array( 'another' => 'z' );
		$expect = array( 'test' => 'a', 'another' => 'z', 'third' => 'c' );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'test' => 'a', 'another' => array( 'third' => 'c' ) );
		$replace = array( 'another' => array( 'third' => 'z' ) );
		$expect = array( 'test' => 'a', 'another' => array( 'third' => 'z' ) );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
		
		$array = array( 'test' => 'a', 'another' => array( 'third' => 'c' ) );
		$replace = array( 'third' => 'z' );
		$expect = array( 'test' => 'a', 'another' => array( 'third' => 'c' ) );
		
		$this->assertEquals( $expect, $UTIL::$CHECK( $array, $replace ) );
	}
}