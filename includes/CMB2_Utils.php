<?php
/**
 * CMB2 Utilities
 *
 * @since  1.1.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Utils {

	/**
	 * The url which is used to load local resources.
	 * @var   string
	 * @since 2.0.0
	 */
	protected $url = '';

	/**
	 * Utility method that attempts to get an attachment's ID by it's url
	 * @since  1.0.0
	 * @param  string  $img_url Attachment url
	 * @return int|false            Attachment ID or false
	 */
	public function image_id_from_url( $img_url ) {
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
			)
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
	 * Utility method that returns time string offset by timezone
	 * @since  1.0.0
	 * @param  string $tzstring Time string
	 * @return string           Offset time string
	 */
	public function timezone_offset( $tzstring ) {
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
				$this->log_if_debug( __METHOD__, __LINE__, $e->getMessage() );
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
	public function timezone_string() {
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
	 * @since  2.0.0
	 * @param  string|int $string Possible timestamp string
	 * @return int   	            Time stamp
	 */
	public function make_valid_time_stamp( $string ) {
		if ( ! $string ) {
			return 0;
		}

		return $this->is_valid_time_stamp( $string )
			? (int) $string :
			strtotime( (string) $string );
	}

	/**
	 * Determine if a value is a valid timestamp
	 * @since  2.0.0
	 * @param  mixed  $timestamp Value to check
	 * @return boolean           Whether value is a valid timestamp
	 */
	public function is_valid_time_stamp( $timestamp ) {
		return (string) (int) $timestamp === (string) $timestamp
			&& $timestamp <= PHP_INT_MAX
			&& $timestamp >= ~PHP_INT_MAX;
	}

	/**
	 * Checks if a value is 'empty'. Still accepts 0.
	 * @since  2.0.0
	 * @param  mixed $value Value to check
	 * @return bool         True or false
	 */
	public function isempty( $value ) {
		return null === $value || '' === $value || false === $value;
	}

	/**
	 * Checks if a value is not 'empty'. 0 doesn't count as empty.
	 * @since  2.2.2
	 * @param  mixed $value Value to check
	 * @return bool         True or false
	 */
	public function notempty( $value ){
		return null !== $value && '' !== $value && false !== $value;
	}

	/**
	 * Filters out empty values (not including 0).
	 * @since  2.2.2
	 * @param  mixed $value Value to check
	 * @return bool         True or false
	 */
	function filter_empty( $value ) {
		return array_filter( $value, array( $this, 'notempty' ) );
	}

	/**
	 * Insert a single array item inside another array at a set position
	 * @since  2.0.2
	 * @param  array &$array   Array to modify. Is passed by reference, and no return is needed.
	 * @param  array $new      New array to insert
	 * @param  int   $position Position in the main array to insert the new array
	 */
	public function array_insert( &$array, $new, $position ) {
		$before = array_slice( $array, 0, $position - 1 );
		$after  = array_diff_key( $array, $before );
		$array  = array_merge( $before, $new, $after );
	}

	/**
	 * Defines the url which is used to load local resources.
	 * This may need to be filtered for local Window installations.
	 * If resources do not load, please check the wiki for details.
	 * @since  1.0.1
	 * @return string URL to CMB2 resources
	 */
	public function url( $path = '' ) {
		if ( $this->url ) {
			return $this->url . $path;
		}

		$cmb2_url = self::get_url_from_dir( cmb2_dir() );

		/**
		 * Filter the CMB location url
		 *
		 * @param string $cmb2_url Currently registered url
		 */
		$this->url = trailingslashit( apply_filters( 'cmb2_meta_box_url', $cmb2_url, CMB2_VERSION ) );

		return $this->url . $path;
	}

	/**
	 * Converts a system path to a URL
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
		$theme_root = get_theme_root();
		if ( 0 === strpos( $dir, $theme_root ) ) {
			// Ok, then use get_theme_root_uri.
			return set_url_scheme( trailingslashit( str_replace( $theme_root, get_theme_root_uri(), $dir ) ) );
		}

		// Check to see if it's anywhere in the root directory

		$site_dir = ABSPATH;
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
	 * @since  2.2.0
	 * @param  string $value       Date value
	 * @param  string $date_format Expected date format
	 * @return mixed               Unix timestamp representing the date.
	 */
	public function get_timestamp_from_value( $value, $date_format ) {
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
	public function php_to_js_dateformat( $format ) {

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
			'A' => 'TT'   // AM/PM
		);

		foreach ( $supported_options as $php => $js ) {
			// replaces every instance of a supported option, but skips escaped characters
			$format = preg_replace( "~(?<!\\\\)$php~", $js, $format );
		}

		$format = preg_replace_callback( '~(?:\\\.)+~', array( $this, 'wrap_escaped_chars' ), $format );

		return $format;
	}

	/**
	 * Helper function for CMB_Utils->php_to_js_dateformat, because php 5.2 was retarded.
	 * @since  2.2.0
	 * @param  $value Value to wrap/escape
	 * @return string Modified value
	 */
	public function wrap_escaped_chars( $value ) {
		return "&#39;" . str_replace( '\\', '', $value[0] ) . "&#39;";
	}

	/**
	 * Send to debug.log if WP_DEBUG is defined and true
	 *
	 * @since  2.2.0
	 *
	 * @param  string  $function Function name
	 * @param  int     $line     Line number
	 * @param  mixed   $msg      Message to output
	 * @param  mixed   $debug    Variable to print_r
	 */
	public function log_if_debug( $function, $line, $msg, $debug = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "In $function, $line:" . print_r( $msg, true ) . ( $debug ? print_r( $debug, true ) : '' ) );
		}
	}

	/**
	 * Determine a file's extension
	 * @since  1.0.0
	 * @param  string       $file File url
	 * @return string|false       File extension or false
	 */
	public function get_file_ext( $file ) {
		$parsed = @parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * Get the file name from a url
	 * @since  2.0.0
	 * @param  string $value File url or path
	 * @return string        File name
	 */
	public function get_file_name_from_path( $value ) {
		$parts = explode( '/', $value );
		return is_array( $parts ) ? end( $parts ) : $value;
	}

	/**
	 * Check if WP version is at least $version.
	 * @since  2.2.2
	 * @param  string  $version WP version string to compare.
	 * @return bool             Result of comparison check.
	 */
	public function wp_at_least( $version ) {
		global $wp_version;
		return version_compare( $wp_version, $version, '>=' );
	}

}
