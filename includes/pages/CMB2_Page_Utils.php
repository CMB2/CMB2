<?php

/**
 * CMB2 Page Utilities
 * Helper methods used by other classes.
 *
 * Uses: None
 * Applies CMB2 Filters: None
 *
 * Public methods:
 *     prepare_hooks_array()             Normalizes an array of 'hook' config arrays
 *     add_wp_hooks_from_config_array()  Adds hooks passed via array of hook config arrays
 *     replace_tokens_in_array()         Simple tokenizing engine, recursive
 *     do_void_action()                  Allows returning string from void functions or actions
 *     check_args()                      Checks passed arguments array against an array of OK values
 *     array_replace_recursive_strict()  array_replace_recursive() with type checking
 *
 * Public methods accessed via callback: None
 * Protected methods: None
 * Private methods: None
 * Magic methods: None
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Page_Utils {
	
	/**
	 * Ensures a hooks config array conforms to what self::add_wp_hooks() expects. Useful if a lot of hooks need to
	 * be added, or you want to allow filtering on the hook list before setting them.
	 *
	 * Will substitute tokens, see method documentation below.
	 *
	 * Sample hook configuration object.
	 *
	 * [
	 *   'id'       => NULL       Required; Bookkeeping only, used to return value for tests
	 *   'hook'     => NULL       Required; The hook, defaults to $default_hook if it is passed
	 *   'call'     => NULL       Required; Callable
	 *   'type'     => 'action'   Can also be 'filter'
	 *   'priority' => 10         WP priority
	 *   'args'     => 1          Number of arguments
	 *   'only_if'  => TRUE       If false, hook will not be added
	 * ]
	 *
	 * @since  2.XXX
	 * @param  array       $raw_hooks    Array of hook config arrays
	 * @param  string|null $default_hook Set via 'admin_menu_hook'
	 * @param  array       $tokens       Array of tokens, if substituting tokens.
	 * @return array|bool
	 */
	public static function prepare_hooks_array( $raw_hooks = array(), $default_hook = NULL, $tokens = array() ) {
		
		$hooks        = array();
		$default_hook = empty( $default_hook ) ? NULL : $default_hook;
		
		// Ensure return from filter is not empty and is an array, or no hook is set
		if ( empty( $raw_hooks ) || ! is_array( $raw_hooks ) ) {
			return FALSE;
		}
		
		// set defaults if not present
		foreach ( $raw_hooks as $h => $cfg ) {
			
			// replace tokens
			$cfg = ! empty( $tokens ) ? self::replace_tokens_in_array( $cfg, $tokens ) : $cfg;
			
			// set missing values to default values
			$hooks[ $h ]['id']       = ! empty( $cfg['id'] )        ? $cfg['id']             : NULL;
			$hooks[ $h ]['hook']     = ! empty( $cfg['hook'] )      ? $cfg['hook']           : $default_hook;
			$hooks[ $h ]['only_if']  = isset( $cfg['only_if'] )     ? $cfg['only_if']        : TRUE;
			$hooks[ $h ]['type']     = ! empty( $cfg['type'] )      ? (string) $cfg['type']  : 'action';
			$hooks[ $h ]['priority'] = ! empty( $cfg['priority'] )  ? (int) $cfg['priority'] : 10;
			$hooks[ $h ]['priority'] = $hooks[ $h ]['priority'] < 1 ? 10                     : $hooks[ $h ]['priority'];
			$hooks[ $h ]['args']     = ! empty( $cfg['args'] )      ? (int) $cfg['args']     : 1;
			$hooks[ $h ]['args']     = $hooks[ $h ]['args'] < 1     ? 1                      : $hooks[ $h ]['args'];
			$hooks[ $h ]['call']     = ! empty( $cfg['call'] )      ? $cfg['call']           : NULL;
			
			// checks of values, remove the hook from the array if anything is true
			if (
				$hooks[ $h ]['id'] === NULL
				|| ( $hooks[ $h ]['call'] === NULL || ! is_callable( $hooks[ $h ]['call'] ) )
				|| $hooks[ $h ]['hook'] === NULL
				|| ( ! in_array( $hooks[ $h ]['type'], array( 'action', 'filter' ) ) )
				|| ! $hooks[ $h ]['only_if']
			) {
				unset( $hooks[ $h ] );
			}
		}
		
		return array_values( $hooks );
	}
	
	/**
	 * Adds hooks to WP from array, per above method. Returns array which can be checked for actual set hooks.
	 * Sends arrays to above method to normalize them.
	 *
	 * @since  2.XXX
	 * @param  array       $hooks        Array of hook configuration arrays
	 * @param  string|null $default_hook Default hook which will be used if not in configured items
	 * @param  array       $tokens       Array of tokens to substitute
	 * @return array|bool
	 */
	public static function add_wp_hooks_from_config_array( $hooks = array(), $default_hook = NULL, $tokens = array() ) {
		
		$return = array();
		$hooks  = self::prepare_hooks_array( $hooks, $default_hook, $tokens );
		
		if ( ! is_array( $hooks ) || empty( $hooks ) ) {
			return FALSE;
		}
		
		foreach ( $hooks as $h ) {
			
			// set callable
			$wp_call = 'add_' . $h['type'];
			
			// call add_action() or add_filter()
			$wp_call( $h['hook'], $h['call'], $h['priority'], $h['args'] );
			
			// Add to the "yes, this was set" return value
			$return[] = array( $h['hook'] => $h['id'] );
		}
		
		return $return;
	}
	
	/**
	 * Substitutes tokens in arrays. Method is recursive, and can check keys. Note that this returns a copy
	 * of the original array.
	 *
	 * If the token value is not scalar and the token is found in the array value, the entire string
	 * will be replaced by the token, as such:
	 *
	 *   $tokens = [ '{MYTOKEN}' => array( 'hm' ),        '{ANOTHER}' => 'Howdy'              ]
	 *   $array  = [ 'key1'      => '{MYTOKEN} is neat',  'key2'      => '{ANOTHER} partner'  ]
	 *   $return = [ 'key1'      => array( 'hm' ),        'key2'      => 'Howdy partner'      ]
	 *
	 * @since  2.XXX
	 * @param  array $array  Array to check for tokens.
	 * @param  array $tokens Tokens. Key should be the token, value what should be subbed
	 * @param  bool  $keys   Whether to look for tokens in the array keys.
	 * @return array
	 */
	public static function replace_tokens_in_array( $array = array(), $tokens = array(), $keys = FALSE ) {
		
		if ( empty( $array ) || empty( $tokens ) || ! is_array( $array ) || ! is_array( $tokens ) ) {
			return $array;
		}
		
		$return = array();
		
		foreach ( $array as $a => $value ) {
			
			$ret_key = $a;
			$ret_val = $value;
			
			// this method only checks strings and arrays for tokens
			if ( is_string( $ret_val ) ) {
				
				foreach ( $tokens as $t => $token ) {
					
					// check the array key; only sub if array key is string and token is a string
					if ( $keys && is_string( $a ) && strpos( $a, $t ) !== FALSE && is_string( $token ) ) {
						$ret_key = str_replace( $t, $token, $a );
					}
					
					if ( is_string( $value ) && strpos( $value, $t ) !== FALSE ) {
						
						// non-scalar and bool values for token (callable, array, etc) are substituted whole
						if ( ! is_scalar( $token ) || is_bool( $token ) ) {
							$ret_val = $token;
						} else {
							$ret_val = str_replace( $t, (string) $token, $ret_val );
						}
					}
				}
				
			} else if ( is_array( $ret_val ) ) {
				$ret_val = self::replace_tokens_in_array( $ret_val, $tokens, $keys );
			}
			$return[ $ret_key ] = $ret_val;
		}
		
		return $return;
	}
	
	/**
	 * Allow arbitrary output functions to be called for actions which echo their return.
	 *
	 * @since  2.XXX
	 * @param  array  $args   Argument array expected as params of the function
	 * @param  string $call   Callable function, do_action, do_meta_boxes, etc.
	 * @param  array  $checks Array of checks to perform against arguments
	 * @return string         Formatted HTML
	 */
	public static function do_void_action( $args = array(), $checks = array(), $call = 'do_action' ) {
		
		$html = '';
		
		// allow passing of callable as second arg if checks are not included
		$call   = is_callable( $checks ) ? $checks : $call;
		$checks = is_callable( $checks ) ? array() : $checks;
		
		if (
			empty( $call )
			|| ! is_callable( $call )
			|| empty( $args )
			|| ! is_array( $args )
			|| ! is_array( $checks )
			|| ! self::check_args( $args, $checks )
		) {
			return $html;
		}
		
		ob_start();
		$returned = call_user_func_array( $call, $args );
		$html     = ob_get_clean();
		
		$html = empty( $returned ) || ! is_string( $returned ) ? $html : $returned;
		
		return $html;
	}
	
	/**
	 * Checks arguments array against checks array to see if the argument should be allowed. Works with string
	 * and numeric indexed arrays. Array keys are normalized to string keys.
	 *
	 * You can send an array of possible ok values for each argument:
	 *   $args = [ 'myarg' => 'a' ]
	 *   $check = [ 'myarg' => [ 'a', 'b', 'c' ] ]
	 * And method will return true if the argument is in the check array.
	 *
	 * This uses strict type checking, '3' !== 3, 1 !== true, objects must be the same instance, etc.
	 *
	 * You can use null in the check array to have this method skip the argument if sending "plain" numeric arrays.
	 * Turn this off by setting $skip to false.
	 *
	 *   $args   = [ true, 3 ]
	 *
	 *   $checks = []                         true, nothing to check
	 *   $checks = [ true ]                   true, second argument not checked
	 *   $checks = [ true, null ]             true if $skip === true, false if $skip === false
	 *   $checks = [ 1 ]                      false, fails strict type checking
	 *   $checks = [ [ true ] ]               true - array when checking scalar value assumed to contain 'ok' values
	 *   $checks = [ null, 3 ]                true if $skip, false if ! $skip
	 *   $checks = [ false, 3 ]               false
	 *   $checks = [ true, "3" ]              false - type is checked
	 *   $checks = [ true, [ 2, 3 ] ]         true
	 *   $checks = [ true, [ 2, "3" ] ]       false
	 *
	 * @since  2.XXX
	 * @param  array $args   arguments array
	 * @param  array $checks checks whose keys match the arguments to be checked
	 * @param  bool  $skip   true: will skip check if check value is null
	 * @return bool
	 */
	public static function check_args( $args = array(), $checks = array(), $skip = TRUE ) {
		
		$ok = TRUE;
		
		if ( empty( $checks ) || ! is_array( $args ) || ! is_array( $checks ) || ! is_bool( $skip ) ) {
			return $ok;
		}
		
		// normalize by converting all keys to strings, eliminates possibilty of loose type checking on keys
		$n_args = array();
		$n_chks = array();
		
		foreach ( $args as $key => $val ) {
			$n_args[ 'zzz' . $key ] = $val;
		}
		foreach ( $checks as $key => $val ) {
			$n_chks[ 'zzz' . $key ] = $val;
		}
		
		// allow for null values in arrays
		$defined        = get_defined_vars();
		$defined_checks = $defined['n_chks'];
		
		foreach ( $n_args as $key => $value ) {
			
			// if at any time this is set to false, the rest of the tests are skipped
			$fail = TRUE;
			
			// does the argument key exist in the check array?
			$fail = $fail && array_key_exists( $key, $defined_checks );
			
			// if $skip is true, is the check value not null, or is $skip set to false?
			$fail = $fail && ( $skip ? ! is_null( $n_chks[ $key ] ) : TRUE );
			
			if ( $fail && is_array( $n_chks[ $key ] ) ) {
				
				// check if arg value exists in array of possible values, strict type checking
				$fail = ! in_array( $value, $n_chks[ $key ], TRUE );
				
			} else if ( $fail ) {
				
				// keep the fail flag set if types don't match
				$fail = gettype( $value ) !== gettype( $n_chks[ $key ] );
				
				// if fail flag was turned off, make sure values match
				$fail = $fail ? $fail : $value !== $n_chks[ $key ];
			}
			
			if ( $fail ) {
				$ok = FALSE;
				break;
			}
		}
		
		return $ok;
	}
	
	/**
	 * Does array_replace_recursive, but with typematching set to strict.
	 * Only exception is $array root values of null, which can be replaced if the $replace type doesn't match.
	 * $newkeys set to false by default to prevent new keys from being introduced to $array
	 *
	 * @since  2.XXX
	 * @param  array $array   Base array
	 * @param  array $replace Replacement values
	 * @param  bool  $newkeys Allow new keys to be added to base array
	 * @return array
	 */
	public static function array_replace_recursive_strict( $array = array(), $replace = array(), $newkeys = FALSE ) {
		
		// no point in going any further
		if ( empty( $array ) || empty( $replace ) || ! is_array( $array ) || ! is_array( $replace ) ) {
			return $array;
		}
		
		// if $newkeys is false, only use values from $replace present in $array
		$replace = $newkeys ? $replace : array_intersect_key( $replace, $array );
		
		foreach ( $replace as $rkey => $rvalue ) {
			
			// will not replace the value if the var types don't match and the $array value is not null
			if (
				array_key_exists( $rkey, $array )
				&& ! is_null( $array[ $rkey ] )
				&& gettype( $rvalue ) !== gettype( $array[ $rkey ] )
			) {
				continue;
			}
			
			$array[ $rkey ] = is_array( $rvalue ) ?
				self::array_replace_recursive_strict( $array[ $rkey ], $rvalue, $newkeys ) :
				$rvalue;
		}
		
		return $array;
	}
}