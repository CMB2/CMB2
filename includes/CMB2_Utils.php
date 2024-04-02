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
	 * @param  string $img_url Attachment url.
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
	 * @since  2.2.4
	 * @link   http://core.trac.wordpress.org/ticket/18947
	 * @global array $_wp_additional_image_sizes
	 * @return array The image sizes
	 */
	public static function get_available_image_sizes() {
		global $_wp_additional_image_sizes;

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
	 * @param  array|string $size Image size. Accepts an array of width and height (in that order).
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

					/**
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
		}// End if.

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
	 * @param  string $tzstring Time string.
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

		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists.
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
	 * Returns a unix timestamp, first checking if value already is a timestamp.
	 *
	 * @since  2.0.0
	 * @param  string|int $string Possible timestamp string.
	 * @return int Time stamp.
	 */
	public static function make_valid_time_stamp( $string ) {
		if ( ! $string ) {
			return 0;
		}

		$valid = self::is_valid_time_stamp( $string );
		if ( $valid ) {
			$timestamp  = (int) $string;
			$length     = strlen( (string) $timestamp );
			$unixlength = strlen( (string) time() );
			$diff       = $length - $unixlength;

			// If value is larger than a unix timestamp, we need to round to the
			// nearest unix timestamp (in seconds).
			if ( $diff > 0 ) {
				$divider   = (int) '1' . str_repeat( '0', $diff );
				$timestamp = round( $timestamp / $divider );
			}
		} else {
			$timestamp = @strtotime( (string) $string );
		}

		return $timestamp;
	}

	/**
	 * Determine if a value is a valid date.
	 *
	 * @since  2.9.1
	 * @param  mixed $date Value to check.
	 * @return boolean     Whether value is a valid date
	 */
	public static function is_valid_date( $date ) {
		return ( is_string( $date ) && @strtotime( $date ) )
			|| self::is_valid_time_stamp( $date );
	}

	/**
	 * Determine if a value is a valid timestamp
	 *
	 * @since  2.0.0
	 * @param  mixed $timestamp Value to check.
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
	 * @param  mixed $value Value to check.
	 * @return bool         True or false
	 */
	public static function isempty( $value ) {
		return null === $value || '' === $value || false === $value || array() === $value;
	}

	/**
	 * Checks if a value is not 'empty'. 0 doesn't count as empty.
	 *
	 * @since  2.2.2
	 * @param  mixed $value Value to check.
	 * @return bool         True or false
	 */
	public static function notempty( $value ) {
		return null !== $value && '' !== $value && false !== $value && array() !== $value;
	}

	/**
	 * Filters out empty values (not including 0).
	 *
	 * @since  2.2.2
	 * @param  mixed $value Value to check.
	 * @return array True or false.
	 */
	public static function filter_empty( $value ) {
		return array_filter( $value, array( __CLASS__, 'notempty' ) );
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @since  2.0.2
	 * @param  array $array    Array to modify. Is passed by reference, and no return is needed. Passed by reference.
	 * @param  array $new      New array to insert.
	 * @param  int   $position Position in the main array to insert the new array.
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
	 * @since  1.0.1
	 *
	 * @param string $path URL path.
	 * @return string URL to CMB2 resources
	 */
	public static function url( $path = '' ) {
		if ( self::$url ) {
			return self::$url . $path;
		}

		$cmb2_url = self::get_url_from_dir( cmb2_dir() );

		/**
		 * Filter the CMB location url.
		 *
		 * @param string $cmb2_url Currently registered url.
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

		// Check to see if it's anywhere in the root directory.
		$site_dir = self::get_normalized_abspath();
		$site_url = trailingslashit( is_multisite() ? network_site_url() : site_url() );

		$url = str_replace(
			array( $site_dir, WP_PLUGIN_DIR ),
			array( $site_url, WP_PLUGIN_URL ),
			$dir
		);

		return set_url_scheme( $url );
	}

	/**
	 * Get the normalized absolute path defined by WordPress.
	 *
	 * @since  2.2.6
	 *
	 * @return string  Normalized absolute path.
	 */
	protected static function get_normalized_abspath() {
		return self::normalize_path( self::$ABSPATH );
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
	 * @param  string $value       Date value.
	 * @param  string $date_format Expected date format.
	 * @return mixed               Unix timestamp representing the date.
	 */
	public static function get_timestamp_from_value( $value, $date_format ) {
		$date_object = date_create_from_format( $date_format, $value );
		return $date_object ? $date_object->setTime( 0, 0, 0 )->getTimeStamp() : strtotime( $value );
	}

	/**
	 * Takes a php date() format string and returns a string formatted to suit for the date/time pickers
	 * It will work only with the following subset of date() options:
	 *
	 * Formats: d, l, j, z, m, F, n, y, and Y.
	 *
	 * A slight effort is made to deal with escaped characters.
	 *
	 * Other options are ignored, because they would either bring compatibility problems between PHP and JS, or
	 * bring even more translation troubles.
	 *
	 * @since 2.2.0
	 * @param string $format PHP date format.
	 * @return string reformatted string
	 */
	public static function php_to_js_dateformat( $format ) {

		// order is relevant here, since the replacement will be done sequentially.
		$supported_options = array(
			'd'   => 'dd',  // Day, leading 0.
			'j'   => 'd',   // Day, no 0.
			'z'   => 'o',   // Day of the year, no leading zeroes.
			// 'D' => 'D',   // Day name short, not sure how it'll work with translations.
			'l '  => 'DD ',  // Day name full, idem before.
			'l, ' => 'DD, ',  // Day name full, idem before.
			'm'   => 'mm',  // Month of the year, leading 0.
			'n'   => 'm',   // Month of the year, no leading 0.
			// 'M' => 'M',   // Month, Short name.
			'F '  => 'MM ',  // Month, full name.
			'F, ' => 'MM, ',  // Month, full name.
			'y'   => 'y',   // Year, two digit.
			'Y'   => 'yy',  // Year, full.
			'H'   => 'HH',  // Hour with leading 0 (24 hour).
			'G'   => 'H',   // Hour with no leading 0 (24 hour).
			'h'   => 'hh',  // Hour with leading 0 (12 hour).
			'g'   => 'h',   // Hour with no leading 0 (12 hour).
			'i'   => 'mm',  // Minute with leading 0.
			's'   => 'ss',  // Second with leading 0.
			'a'   => 'tt',  // am/pm.
			'A'   => 'TT', // AM/PM.
		);

		foreach ( $supported_options as $php => $js ) {
			// replaces every instance of a supported option, but skips escaped characters.
			$format = preg_replace( "~(?<!\\\\)$php~", $js, $format );
		}

		$supported_options = array(
			'l' => 'DD',  // Day name full, idem before.
			'F' => 'MM',  // Month, full name.
		);

		if ( isset( $supported_options[ $format ] ) ) {
			$format = $supported_options[ $format ];
		}

		$format = preg_replace_callback( '~(?:\\\.)+~', array( __CLASS__, 'wrap_escaped_chars' ), $format );

		return $format;
	}

	/**
	 * Get a DateTime object from a value.
	 *
	 * @since 2.11.0
	 *
	 * @param string $value The value to convert to a DateTime object.
	 *
	 * @return DateTime|null
	 */
	public static function get_datetime_from_value( $value ) {
		return is_serialized( $value )
			// Ok, we need to unserialize the value
			// -- allows back-compat for older field values with serialized DateTime objects.
			? self::unserialize_datetime( $value )
			// Handle new json formatted values.
			: self::json_to_datetime( $value );
	}

	/**
	 * Unserialize a datetime value string.
	 *
	 * This is a back-compat method for older field values with serialized DateTime objects.
	 *
	 * @since 2.11.0
	 *
	 * @param string $date_value The serialized datetime value.
	 *
	 * @return DateTime|null
	 */
	public static function unserialize_datetime( $date_value ) {
		$datetime = @unserialize( trim( $date_value ), array( 'allowed_classes' => array( 'DateTime' ) ) );

		return $datetime && $datetime instanceof DateTime ? $datetime : null;
	}

	/**
	 * Convert a json datetime value string to a DateTime object.
	 *
	 * @since 2.11.0
	 *
	 * @param string $json_string The json value string.
	 *
	 * @return DateTime|null
	 */
	public static function json_to_datetime( $json_string ) {
		if ( ! is_string( $json_string ) ) {
			return null;
		}

		$json = json_decode( $json_string );

		// Check if json decode was successful
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		// If so, convert to DateTime object.
		return self::unserialize_datetime( str_replace(
			'stdClass',
			'DateTime',
			serialize( $json )
		) );
	}

	/**
	 * Helper function for CMB_Utils::php_to_js_dateformat().
	 *
	 * @since  2.2.0
	 * @param  string $value Value to wrap/escape.
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
	 * @param  string $function Function name.
	 * @param  int    $line     Line number.
	 * @param  mixed  $msg      Message to output.
	 * @param  mixed  $debug    Variable to print_r.
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
	 * @param  string $file File url.
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
	 * @param  string $value File url or path.
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
	 * @return bool            Result of comparison check.
	 */
	public static function wp_at_least( $version ) {
		return version_compare( get_bloginfo( 'version' ), $version, '>=' );
	}

	/**
	 * Combines attributes into a string for a form element.
	 *
	 * @since  1.1.0
	 * @param  array $attrs        Attributes to concatenate.
	 * @param  array $attr_exclude Attributes that should NOT be concatenated.
	 * @return string              String of attributes for form element.
	 */
	public static function concat_attrs( $attrs, $attr_exclude = array() ) {
		$attr_exclude[] = 'rendered';
		$attr_exclude[] = 'js_dependencies';

		$attributes = '';
		foreach ( $attrs as $attr => $val ) {
			$excluded = in_array( $attr, (array) $attr_exclude, true );
			$empty    = false === $val && 'value' !== $attr;
			if ( ! $excluded && ! $empty ) {
				$val = is_array( $val ) ? implode( ',', $val ) : $val;

				// if data attribute, use single quote wraps, else double.
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
	 * @param string $att HTML attribute.
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
	 * If number is numeric, normalize it with floatval or intval, depending on if decimal is found.
	 *
	 * @since  2.2.6
	 *
	 * @param mixed $value Value to normalize (if numeric).
	 * @return mixed         Possibly normalized value.
	 */
	public static function normalize_if_numeric( $value ) {
		if ( is_numeric( $value ) ) {
			$value = false !== strpos( $value, '.' ) ? floatval( $value ) : intval( $value );
		}

		return $value;
	}

	/**
	 * Generates a 12 character unique hash from a string.
	 *
	 * @since  2.4.0
	 *
	 * @param string $string String to create a hash from.
	 *
	 * @return string
	 */
	public static function generate_hash( $string ) {
		return substr( base_convert( md5( $string ), 16, 32 ), 0, 12 );
	}

}
