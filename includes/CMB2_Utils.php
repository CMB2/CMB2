<?php
/**
 * CMB2 Utilities
 *
 * @since  1.1.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Utils {

	/**
	 * The WordPress ABSPATH constant.
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected static $ABSPATH = ABSPATH;

	/**
	 * The url which is used to load local resources.
	 *
	 * @var   string
	 * @since 2.0.0
	 */
	protected static $url = '';

	/**
	 * Utility method that attempts to get an attachment's ID by it's url
	 *
	 * @since  1.0.0
	 * @param  string $img_url Attachment url
	 * @return int|false            Attachment ID or false
	 */
	public static function image_id_from_url( $img_url ) {
		$attachment_id = 0;
		$dir = wp_upload_dir();

		// Is URL in uploads directory?
		if ( false === strpos( $img_url, $dir['baseurl'] . '/' ) ) {
			return false;
		}

		$file = basename( $img_url );

		$query_args = array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'fields'      => 'ids',
			'meta_query'  => array(
				array(
					'value'   => $file,
					'compare' => 'LIKE',
					'key'     => '_wp_attachment_metadata',
				),
			),
		);

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {

			foreach ( $query->posts as $post_id ) {
				$meta = wp_get_attachment_metadata( $post_id );
				$original_file       = basename( $meta['file'] );
				$cropped_image_files = isset( $meta['sizes'] ) ? wp_list_pluck( $meta['sizes'], 'file' ) : array();
				if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
					$attachment_id = $post_id;
					break;
				}
			}
		}

		return 0 === $attachment_id ? false : $attachment_id;
	}

	/**
	 * Utility method to get a combined list of default and custom registered image sizes
	 *
	 * @since  2.XXX Set $image_sizes initially in proper scope
	 * @since  2.2.4
	 * @link   http://core.trac.wordpress.org/ticket/18947
	 * @global array $_wp_additional_image_sizes
	 * @return array The image sizes
	 */
	static function get_available_image_sizes() {
		global $_wp_additional_image_sizes;

		$image_sizes = array();
		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );
		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ] = array(
				'height' => intval( get_option( "{$size}_size_h" ) ),
				'width'  => intval( get_option( "{$size}_size_w" ) ),
				'crop'   => get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false,
			);
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		return $image_sizes;
	}

	/**
	 * Utility method to return the closest named size from an array of values
	 *
	 * Based off of WordPress's image_get_intermediate_size()
	 * If the size matches an existing size then it will be used. If there is no
	 * direct match, then the nearest image size larger than the specified size
	 * will be used. If nothing is found, then the function will return false.
	 * Uses get_available_image_sizes() to get all available sizes.
	 *
	 * @since  2.2.4
	 * @param  array|string $size Image size. Accepts an array of width and height (in that order)
	 * @return false|string       Named image size e.g. 'thumbnail'
	 */
	public static function get_named_size( $size ) {
		$data = array();

		// Find the best match when '$size' is an array.
		if ( is_array( $size ) ) {
			$image_sizes = self::get_available_image_sizes();
			$candidates = array();

			foreach ( $image_sizes as $_size => $data ) {

				// If there's an exact match to an existing image size, short circuit.
				if ( $data['width'] == $size[0] && $data['height'] == $size[1] ) {
					$candidates[ $data['width'] * $data['height'] ] = array( $_size, $data );
					break;
				}

				// If it's not an exact match, consider larger sizes with the same aspect ratio.
				if ( $data['width'] >= $size[0] && $data['height'] >= $size[1] ) {

					/*
					 * To test for varying crops, we constrain the dimensions of the larger image
					 * to the dimensions of the smaller image and see if they match.
					 */
					if ( $data['width'] > $size[0] ) {
						$constrained_size = wp_constrain_dimensions( $data['width'], $data['height'], $size[0] );
						$expected_size = array( $size[0], $size[1] );
					} else {
						$constrained_size = wp_constrain_dimensions( $size[0], $size[1], $data['width'] );
						$expected_size = array( $data['width'], $data['height'] );
					}

					// If the image dimensions are within 1px of the expected size, we consider it a match.
					$matched = ( abs( $constrained_size[0] - $expected_size[0] ) <= 1 && abs( $constrained_size[1] - $expected_size[1] ) <= 1 );

					if ( $matched ) {
						$candidates[ $data['width'] * $data['height'] ] = array( $_size, $data );
					}
				}
			}

			if ( ! empty( $candidates ) ) {
				// Sort the array by size if we have more than one candidate.
				if ( 1 < count( $candidates ) ) {
					ksort( $candidates );
				}

				$data = array_shift( $candidates );
				$data = $data[0];
			} elseif ( ! empty( $image_sizes['thumbnail'] ) && $image_sizes['thumbnail']['width'] >= $size[0] && $image_sizes['thumbnail']['width'] >= $size[1] ) {
				/*
				 * When the size requested is smaller than the thumbnail dimensions, we
				 * fall back to the thumbnail size.
				 */
				$data = 'thumbnail';
			} else {
				return false;
			}
		} elseif ( ! empty( $image_sizes[ $size ] ) ) {
			$data = $size;
		}// End if().

		// If we still don't have a match at this point, return false.
		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Utility method that returns time string offset by timezone
	 *
	 * @since  1.0.0
	 * @param  string $tzstring Time string
	 * @return string           Offset time string
	 */
	public static function timezone_offset( $tzstring ) {
		$tz_offset = 0;

		if ( ! empty( $tzstring ) && is_string( $tzstring ) ) {
			if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
				$tzstring = str_replace( array( ':15', ':30', ':45' ), array( '.25', '.5', '.75' ), $tzstring );
				return intval( floatval( substr( $tzstring, 3 ) ) * HOUR_IN_SECONDS );
			}

			try {
				$date_time_zone_selected = new DateTimeZone( $tzstring );
				$tz_offset = timezone_offset_get( $date_time_zone_selected, date_create() );
			} catch ( Exception $e ) {
				self::log_if_debug( __METHOD__, __LINE__, $e->getMessage() );
			}
		}

		return $tz_offset;
	}

	/**
	 * Utility method that returns a timezone string representing the default timezone for the site.
	 *
	 * Roughly copied from WordPress, as get_option('timezone_string') will return
	 * an empty string if no value has been set on the options page.
	 * A timezone string is required by the wp_timezone_choice() used by the
	 * select_timezone field.
	 *
	 * @since  1.0.0
	 * @return string Timezone string
	 */
	public static function timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
			$tzstring = '';
		}

		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
			if ( 0 == $current_offset ) {
				$tzstring = 'UTC+0';
			} elseif ( $current_offset < 0 ) {
				$tzstring = 'UTC' . $current_offset;
			} else {
				$tzstring = 'UTC+' . $current_offset;
			}
		}

		return $tzstring;
	}

	/**
	 * Returns a timestamp, first checking if value already is a timestamp.
	 *
	 * @since  2.0.0
	 * @param  string|int $string Possible timestamp string
	 * @return int   	            Time stamp
	 */
	public static function make_valid_time_stamp( $string ) {
		if ( ! $string ) {
			return 0;
		}

		return self::is_valid_time_stamp( $string )
			? (int) $string :
			strtotime( (string) $string );
	}

	/**
	 * Determine if a value is a valid timestamp
	 *
	 * @since  2.0.0
	 * @param  mixed $timestamp Value to check
	 * @return boolean           Whether value is a valid timestamp
	 */
	public static function is_valid_time_stamp( $timestamp ) {
		return (string) (int) $timestamp === (string) $timestamp
			&& $timestamp <= PHP_INT_MAX
			&& $timestamp >= ~PHP_INT_MAX;
	}

	/**
	 * Checks if a value is 'empty'. Still accepts 0.
	 *
	 * @since  2.0.0
	 * @param  mixed $value Value to check
	 * @return bool         True or false
	 */
	public static function isempty( $value ) {
		return null === $value || '' === $value || false === $value || array() === $value;
	}

	/**
	 * Checks if a value is not 'empty'. 0 doesn't count as empty.
	 *
	 * @since  2.2.2
	 * @param  mixed $value Value to check
	 * @return bool         True or false
	 */
	public static function notempty( $value ) {
		return null !== $value && '' !== $value && false !== $value && array() !== $value;
	}

	/**
	 * Filters out empty values (not including 0).
	 *
	 * @since  2.XXX Updated this doc block to reflect return type of array
	 * @since  2.2.2
	 * @param  mixed $value Value to check
	 * @return array
	 */
	public static function filter_empty( $value ) {
		return array_filter( $value, array( __CLASS__, 'notempty' ) );
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @since  2.0.2
	 * @param  array &$array   Array to modify. Is passed by reference, and no return is needed.
	 * @param  array $new      New array to insert
	 * @param  int   $position Position in the main array to insert the new array
	 */
	public static function array_insert( &$array, $new, $position ) {
		$before = array_slice( $array, 0, $position - 1 );
		$after  = array_diff_key( $array, $before );
		$array  = array_merge( $before, $new, $after );
	}
	
	/**
	 * Defines the url which is used to load local resources.
	 * This may need to be filtered for local Window installations.
	 * If resources do not load, please check the wiki for details.
	 *
	 * @since  2.XXX Added param to docblock
	 * @since  1.0.1
	 * @param  string $path
	 * @return string URL to CMB2 resources
	 */
	public static function url( $path = '' ) {
		if ( self::$url ) {
			return self::$url . $path;
		}

		$cmb2_url = self::get_url_from_dir( cmb2_dir() );

		/**
		 * Filter the CMB location url
		 *
		 * @param string $cmb2_url Currently registered url
		 */
		self::$url = trailingslashit( apply_filters( 'cmb2_meta_box_url', $cmb2_url, CMB2_VERSION ) );

		return self::$url . $path;
	}

	/**
	 * Converts a system path to a URL
	 *
	 * @since  2.2.2
	 * @param  string $dir Directory path to convert.
	 * @return string      Converted URL.
	 */
	public static function get_url_from_dir( $dir ) {
		$dir = self::normalize_path( $dir );

		// Let's test if We are in the plugins or mu-plugins dir.
		$test_dir = trailingslashit( $dir ) . 'unneeded.php';
		if (
			0 === strpos( $test_dir, self::normalize_path( WPMU_PLUGIN_DIR ) )
			|| 0 === strpos( $test_dir, self::normalize_path( WP_PLUGIN_DIR ) )
		) {
			// Ok, then use plugins_url, as it is more reliable.
			return trailingslashit( plugins_url( '', $test_dir ) );
		}

		// Ok, now let's test if we are in the theme dir.
		$theme_root = self::normalize_path( get_theme_root() );
		if ( 0 === strpos( $dir, $theme_root ) ) {
			// Ok, then use get_theme_root_uri.
			return set_url_scheme(
				trailingslashit(
					str_replace(
						untrailingslashit( $theme_root ),
						untrailingslashit( get_theme_root_uri() ),
						$dir
					)
				)
			);
		}

		// Check to see if it's anywhere in the root directory
		$site_dir = self::normalize_path( self::$ABSPATH );
		$site_url = trailingslashit( is_multisite() ? network_site_url() : site_url() );

		$url = str_replace(
			array( $site_dir, WP_PLUGIN_DIR ),
			array( $site_url, WP_PLUGIN_URL ),
			$dir
		);

		return set_url_scheme( $url );
	}

	/**
	 * `wp_normalize_path` wrapper for back-compat. Normalize a filesystem path.
	 *
	 * On windows systems, replaces backslashes with forward slashes
	 * and forces upper-case drive letters.
	 * Allows for two leading slashes for Windows network shares, but
	 * ensures that all other duplicate slashes are reduced to a single.
	 *
	 * @since 2.2.0
	 *
	 * @param string $path Path to normalize.
	 * @return string Normalized path.
	 */
	protected static function normalize_path( $path ) {
		if ( function_exists( 'wp_normalize_path' ) ) {
			return wp_normalize_path( $path );
		}

		// Replace newer WP's version of wp_normalize_path.
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		if ( ':' === substr( $path, 1, 1 ) ) {
			$path = ucfirst( $path );
		}

		return $path;
	}

	/**
	 * Get timestamp from text date
	 *
	 * @since  2.2.0
	 * @param  string $value       Date value
	 * @param  string $date_format Expected date format
	 * @return mixed               Unix timestamp representing the date.
	 */
	public static function get_timestamp_from_value( $value, $date_format ) {
		$date_object = date_create_from_format( $date_format, $value );
		return $date_object ? $date_object->setTime( 0, 0, 0 )->getTimeStamp() : strtotime( $value );
	}

	/**
	 * Takes a php date() format string and returns a string formatted to suit for the date/time pickers
	 * It will work with only with the following subset ot date() options:
	 *
	 *  d, j, z, m, n, y, and Y.
	 *
	 * A slight effort is made to deal with escaped characters.
	 *
	 * Other options are ignored, because they would either bring compatibility problems between PHP and JS, or
	 * bring even more translation troubles.
	 *
	 * @since 2.2.0
	 * @param string $format php date format
	 * @return string reformatted string
	 */
	public static function php_to_js_dateformat( $format ) {

		// order is relevant here, since the replacement will be done sequentially.
		$supported_options = array(
			'd' => 'dd',  // Day, leading 0
			'j' => 'd',   // Day, no 0
			'z' => 'o',   // Day of the year, no leading zeroes,
			// 'D' => 'D',   // Day name short, not sure how it'll work with translations
			// 'l' => 'DD',  // Day name full, idem before
			'm' => 'mm',  // Month of the year, leading 0
			'n' => 'm',   // Month of the year, no leading 0
			// 'M' => 'M',   // Month, Short name
			// 'F' => 'MM',  // Month, full name,
			'y' => 'y',   // Year, two digit
			'Y' => 'yy',  // Year, full
			'H' => 'HH',  // Hour with leading 0 (24 hour)
			'G' => 'H',   // Hour with no leading 0 (24 hour)
			'h' => 'hh',  // Hour with leading 0 (12 hour)
			'g' => 'h',   // Hour with no leading 0 (12 hour),
			'i' => 'mm',  // Minute with leading 0,
			's' => 'ss',  // Second with leading 0,
			'a' => 'tt',  // am/pm
			'A' => 'TT',// AM/PM
		);

		foreach ( $supported_options as $php => $js ) {
			// replaces every instance of a supported option, but skips escaped characters
			$format = preg_replace( "~(?<!\\\\)$php~", $js, $format );
		}

		$format = preg_replace_callback( '~(?:\\\.)+~', array( __CLASS__, 'wrap_escaped_chars' ), $format );

		return $format;
	}

	/**
	 * Helper function for CMB_Utils->php_to_js_dateformat, because php 5.2 was retarded.
	 *
	 * @since  2.XXX Updated this docblock to reflect param type
	 * @since  2.2.0
	 * @param  string $value Value to wrap/escape
	 * @return string Modified value
	 */
	public static function wrap_escaped_chars( $value ) {
		return '&#39;' . str_replace( '\\', '', $value[0] ) . '&#39;';
	}

	/**
	 * Send to debug.log if WP_DEBUG is defined and true
	 *
	 * @since  2.2.0
	 *
	 * @param  string $function Function name
	 * @param  int    $line     Line number
	 * @param  mixed  $msg      Message to output
	 * @param  mixed  $debug    Variable to print_r
	 */
	public static function log_if_debug( $function, $line, $msg, $debug = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "In $function, $line:" . print_r( $msg, true ) . ( $debug ? print_r( $debug, true ) : '' ) );
		}
	}

	/**
	 * Determine a file's extension
	 *
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return string|false       File extension or false
	 */
	public static function get_file_ext( $file ) {
		$parsed = parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * Get the file name from a url
	 *
	 * @since  2.0.0
	 * @param  string $value File url or path
	 * @return string        File name
	 */
	public static function get_file_name_from_path( $value ) {
		$parts = explode( '/', $value );
		return is_array( $parts ) ? end( $parts ) : $value;
	}

	/**
	 * Check if WP version is at least $version.
	 *
	 * @since  2.2.2
	 * @param  string $version WP version string to compare.
	 * @return bool             Result of comparison check.
	 */
	public static function wp_at_least( $version ) {
		return version_compare( get_bloginfo( 'version' ), $version, '>=' );
	}

	/**
	 * Combines attributes into a string for a form element.
	 *
	 * @since  2.XXX               Removed second parameter from is_data_attribute(), method calls for one
	 * @since  1.1.0
	 * @param  array $attrs        Attributes to concatenate.
	 * @param  array $attr_exclude Attributes that should NOT be concatenated.
	 * @return string               String of attributes for form element.
	 */
	public static function concat_attrs( $attrs, $attr_exclude = array() ) {
		$attr_exclude[] = 'rendered';
		$attributes = '';
		foreach ( $attrs as $attr => $val ) {
			$excluded = in_array( $attr, (array) $attr_exclude, true );
			$empty    = false === $val && 'value' !== $attr;
			if ( ! $excluded && ! $empty ) {
				// if data attribute, use single quote wraps, else double
				$quotes = self::is_data_attribute( $attr ) ? "'" : '"';
				$attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, $quotes );
			}
		}
		return $attributes;
	}

	/**
	 * Check if given attribute is a data attribute.
	 *
	 * @since  2.2.5
	 *
	 * @param  string  $att HTML attribute
	 *
	 * @return boolean
	 */
	public static function is_data_attribute( $att ) {
		return 0 === stripos( $att, 'data-' );
	}

	/**
	 * Ensures value is an array.
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed $value   Value to ensure is array.
	 * @param  array $default Default array. Defaults to empty array.
	 *
	 * @return array          The array.
	 */
	public static function ensure_array( $value, $default = array() ) {
		if ( empty( $value ) ) {
			return $default;
		}

		if ( is_array( $value ) || is_object( $value ) ) {
			return (array) $value;
		}

		// Not sure anything would be non-scalar that is not an array or object?
		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		return (array) $value;
	}
	
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
	public static function prepare_hooks_array( $raw_hooks = array(), $default_hook = null, $tokens = array() ) {
		
		$hooks        = array();
		$default_hook = empty( $default_hook ) ? null : $default_hook;
		
		// Ensure return from filter is not empty and is an array, or no hook is set
		if ( empty( $raw_hooks ) || ! is_array( $raw_hooks ) ) {
			return FALSE;
		}
		
		// set defaults if not present
		foreach ( $raw_hooks as $h => $cfg ) {
			
			// replace tokens
			$cfg = ! empty( $tokens ) ? self::replace_tokens_in_array( $cfg, $tokens ) : $cfg;
			
			// set missing values to default values
			$hooks[ $h ]['id']       = ! empty( $cfg['id'] )       ? $cfg['id']             : NULL;
			$hooks[ $h ]['hook']     = ! empty( $cfg['hook'] )     ? $cfg['hook']           : $default_hook;
			$hooks[ $h ]['only_if']  =   isset( $cfg['only_if'] )  ? $cfg['only_if']        : true;
			$hooks[ $h ]['type']     = ! empty( $cfg['type'] )     ? (string) $cfg['type']  : 'action';
			$hooks[ $h ]['priority'] = ! empty( $cfg['priority'] ) ? (int) $cfg['priority'] : 10;
			$hooks[ $h ]['args']     = ! empty( $cfg['args'] )     ? (int) $cfg['args']     : 1;
			$hooks[ $h ]['call']     = ! empty( $cfg['call'] )     ? $cfg['call']           : null;
			
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
	 * @param  array       $hooks         Array of hook configuration arrays
	 * @param  string|null $default_hook  Default hook which will be used if not in configured items
	 * @param  array       $tokens        Array of tokens to substitute
	 * @return array|bool
	 */
	public static function add_wp_hooks_from_config_array( $hooks = array(), $default_hook = null, $tokens = array() ) {
		
		$return = array();
		$hooks  = self::prepare_hooks_array( $hooks, $default_hook, $tokens );
		
		if ( ! is_array( $hooks ) || empty( $hooks ) ) {
			return false;
		}
		
		foreach ( $hooks as $h ) {
			
			// set callable
			$wp_call = 'add_' . $h['type'];
			
			// call add_action() or add_filter()
			$wp_call( $h['hook'], $h['call'], $h['priority'], $h['args'] );
			
			// Add to the "yes, this was set" return value
			$return[] = array(  $h['hook']  => $h['id'] );
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
	 * @param  array $array   Array to check for tokens.
	 * @param  array $tokens  Tokens. Key should be the token, value what should be subbed
	 * @param  bool  $keys    Whether to look for tokens in the array keys.
	 * @return array
	 */
	public static function replace_tokens_in_array( $array = array(), $tokens = array(), $keys = false ) {
		
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
	 * This uses strict type checking, '3' !== 3, 1 !== true, etc.
	 *
	 * You can use null in the check array to have this method skip the argument if sending "plain" numeric arrays.
	 * Turn this off by setting $skip to false.
	 *
	 * $args   = [ true, 3 ]
	 *
	 * $checks = []                         true, nothing to check
	 * $checks = [ true ]                   true, second argument not checked
	 * $checks = [ true, null ]             true if $skip === true, false if $skip === false
	 * $checks = [ 1 ]                      false, fails strict type checking
	 * $checks = [ [ true ] ]               true - array check scalar value for multiple 'ok' values
	 * $checks = [ null, 3 ]                true if $skip, false if ! $skip
	 * $checks = [ false, 3 ]               false
	 * $checks = [ true, "3" ]              false - type is checked
	 * $checks = [ true, [ 2, 3 ] ]         true
	 * $checks = [ true, [ 2, "3" ] ]       false
	 *
	 * @since  2.XXX
	 * @param  array $args    arguments array
	 * @param  array $checks  checks whose keys match the arguments to be checked
	 * @param  bool  $skip    true: will skip check if
	 * @return bool
	 */
	public static function check_args( $args = array(), $checks = array(), $skip = true ) {
		
		$ok = true;
		
		if ( empty( $checks ) || ! is_array( $args ) || ! is_array( $checks ) || ! is_bool( $skip ) ) {
			return $ok;
		}
		
		// convert all keys to strings to eliminate possibilty of loose type checking
		$n_args = array();
		$n_chks = array();
		
		foreach( $args as $key => $val ) {
			$n_args[ 'zzz' . $key ] = $val;
		}
		foreach( $checks as $key => $val ) {
			$n_chks[ 'zzz' . $key ] = $val;
		}
		
		// allow for null values to have been set in arrays
		$defined        = get_defined_vars();
		$defined_checks = $defined['n_chks'];
		
		foreach ( $n_args as $key => $value ) {
			
			$isset = array_key_exists( $key, $defined_checks );

			/*
			 * Check $value only if these two conditions are met (no check available? $value is ok):
			 * 1: $n_arg key exists in $n_chk (as defined by bool $isset)
			 * 2: One of these conditions is met:
			 *    - value isn't null and $skip is true
			 *    - $skip is false
			 */
			if ( $isset && (  (  $skip && ! is_null( $n_chks[ $key ] )  ) || ! $skip )  )  {
				
				/*
				 * Value is NOT ok if either of these two conditions is true:
				 * 1: check is an array of possibilities, and $value isn't in that array
				 * 2: check is not an array, and one of these conditions is met:
				 *    - the var type of $value does not match the check var type
				 *    - $value does not match the check value
				 */
				if ( ( is_array( $n_chks[ $key ] ) && ! in_array( $value, $n_chks[ $key ], true ) )
					|| ( ! is_array( $n_chks[ $key ] )
						&& ( gettype( $value ) != gettype( $n_chks[ $key ] ) || $value !== $n_chks[ $key ] ) )
				) {
					$ok = false;
					break;
				}
			}
		}
		
		return $ok;
	}
}
