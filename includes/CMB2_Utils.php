<?php

/**
 * CMB field class
 * @since  1.1.0
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
	 * @return mixed            Attachment ID or false
	 */
	public function image_id_from_url( $img_url ) {
		global $wpdb;

		$img_url = esc_url_raw( $img_url );
		// Get just the file name
		if ( false !== strpos( $img_url, '/' ) ) {
			$explode = explode( '/', $img_url );
			$img_url = end( $explode );
		}

		// And search for a fuzzy match of the file name
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid LIKE '%%%s%%' LIMIT 1;", $img_url ) );

		// If we found an attachement ID, return it
		if ( ! empty( $attachment ) && is_array( $attachment ) ) {
			return $attachment[0];
		}

		// No luck
		return false;
	}

	/**
	 * Utility method that returns time string offset by timezone
	 * @since  1.0.0
	 * @param  string $tzstring Time string
	 * @return string           Offset time string
	 */
	public function timezone_offset( $tzstring ) {
		if ( ! empty( $tzstring ) && is_string( $tzstring ) ) {
			if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
				$tzstring = str_replace( array( ':15', ':30', ':45' ), array( '.25', '.5', '.75' ), $tzstring );
				return intval( floatval( substr( $tzstring, 3 ) ) * HOUR_IN_SECONDS );
			}

			$date_time_zone_selected = new DateTimeZone( $tzstring );
			$tz_offset = timezone_offset_get( $date_time_zone_selected, date_create() );

			return $tz_offset;
		}

		return 0;
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
			strtotime( $string );
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
	 * @return string URL to CMB resources
	 */
	public function url( $path = '' ) {
		if ( $this->url ) {
			return $this->url . $path;
		}

		if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
			// Windows
			$content_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
			$content_url = str_replace( $content_dir, WP_CONTENT_URL, cmb2_dir() );
			$cmb2_url = str_replace( DIRECTORY_SEPARATOR, '/', $content_url );

		} else {
			$cmb2_url = str_replace(
				array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
				array( WP_CONTENT_URL, WP_PLUGIN_URL ),
				cmb2_dir()
			);
		}

		/**
		 * Filter the CMB location url
		 *
		 * @param string $cmb2_url Currently registered url
		 */
		$this->url = trailingslashit( apply_filters( 'cmb2_meta_box_url', set_url_scheme( $cmb2_url ), CMB2_VERSION ) );

		return $this->url . $path;
	}

}
