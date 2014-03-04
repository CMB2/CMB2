<?php

/**
 * CMB field validation
 * @since  0.0.4
 */
class cmb_Meta_Box_Sanitize {

	/**
	 * A single instance of this class.
	 * @var   cmb_Meta_Box_types object
	 * @since 1.0.0
	 */
	public static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 * @since  1.0.0
	 * @return cmb_Meta_Box_Sanitize A single instance of this class.
	 */
	public static function get() {
		if ( self::$instance === null )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Sample field validation
	 * @since  0.0.4
	 */
	public static function check_text( $text ) {
		return $text === 'hello' ? true : false;
	}

	/**
	 * Simple checkbox validation
	 * @since  1.0.1
	 * @param  mixed  $text 'on' or false
	 * @return mixex        'on' or false
	 */
	public static function checkbox( $text ) {
		return $text === 'on' ? 'on' : false;
	}

	/**
	 * Validate url in a meta value
	 * @since  1.0.1
	 * @param  string $meta  Meta value
	 * @param  array  $field Field config array
	 * @return string        Empty string or escaped url
	 */
	public static function text_url( $meta, $field ) {

		$protocols = isset( $field['protocols'] ) ? (array) $field['protocols'] : null;
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = $value ? esc_url_raw( $value, $protocols ) : $field['default'];
			}
		} else {
			$meta = $meta ? esc_url_raw( $meta, $protocols ) : $field['default'];
		}

		return $meta;
	}

	/**
	 * Because we don't want oembed data passed through the cmb_validate_ filter
	 * @since  1.0.1
	 * @param  string  $meta oembed cached data
	 * @return string        oembed cached data
	 */
	public static function oembed( $meta ) {
		return $meta;
	}

	/**
	 * Validate email in a meta value
	 * @since  1.0.1
	 * @param  string $meta Meta value
	 * @return string       Empty string or validated email
	 */
	public static function text_email( $meta ) {

		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$value = trim( $value );
				$meta[ $key ] = is_email( $value ) ? $value : '';
			}
		} else {
			$meta = trim( $meta );
			$meta = is_email( $meta ) ? $meta : '';
		}

		return $meta;
	}

	/**
	 * Validate money in a meta value
	 * @since  1.0.1
	 * @param  string $meta Meta value
	 * @return string       Empty string or validated money value
	 */
	public static function text_money( $meta ) {
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = number_format( (float) str_ireplace( ',', '', $value ), 2, '.', ',');
			}
		} else {
			$meta = number_format( (float) str_ireplace( ',', '', $meta ), 2, '.', ',');
		}

		return $meta;
	}

	/**
	 * Converts text date to timestamp
	 * @since  1.0.2
	 * @param  string $meta Meta value
	 * @return string       Timestring
	 */
	public static function text_date_timestamp( $meta ) {
		return strtotime( $meta );;
	}

	/**
	 * Datetime to timestamp
	 * @since  1.0.1
	 * @param  string $meta Meta value
	 * @return string       Timestring
	 */
	public static function text_datetime_timestamp( $meta ) {

		$test = is_array( $meta ) ? array_filter( $meta ) : '';
		if ( empty( $test ) )
			return '';

		$meta = strtotime( $meta['date'] .' '. $meta['time'] );

		if ( $tz_offset = cmb_Meta_Box::field_timezone_offset() )
			$meta += $tz_offset;

		return $meta;
	}

	/**
	 * Datetime to imestamp with timezone
	 * @since  1.0.1
	 * @param  string $meta Meta value
	 * @return string       Timestring
	 */
	public static function text_datetime_timestamp_timezone( $meta ) {

		$test = is_array( $meta ) ? array_filter( $meta ) : '';
		if ( empty( $test ) )
			return '';

		$tzstring = null;

		if ( is_array( $meta ) && array_key_exists( 'timezone', $meta ) )
			$tzstring = $meta['timezone'];

		if ( empty( $tzstring ) )
			$tzstring = cmb_Meta_Box::timezone_string();

		$offset = cmb_Meta_Box::timezone_offset( $tzstring, true );

		if ( substr( $tzstring, 0, 3 ) === 'UTC' )
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );

		$meta = new DateTime( $meta['date'] .' '. $meta['time'], new DateTimeZone( $tzstring ) );
		$meta = serialize( $meta );

		return $meta;
	}

	/**
	 * Sanitize textareas and wysiwyg fields
	 * @since  1.0.1
	 * @param  string $meta Meta value
	 * @return string       Sanitized data
	 */
	public static function textarea( $meta ) {
		return wp_kses_post( $meta );
	}

	/**
	 * Sanitize code textareas
	 * @since  1.0.2
	 * @param  string $meta Meta value
	 * @return string       Sanitized data
	 */
	public static function textarea_code( $meta ) {
		return htmlspecialchars_decode( stripslashes( $meta ) );
	}

	/**
	 * Sanitize code textareas
	 * @since  1.0.2
	 * @param  string $meta  Meta value
	 * @param  array  $field Field config array
	 * @return string        Sanitized data
	 */
	public static function file( $meta, $field ) {
		$_id_name = $field['id'] .'_id';
		// get _id old value
		$_id_old = cmb_Meta_Box::get_data( $_id_name );

		// If specified NOT to save the file ID
		if ( isset( $field['save_id'] ) && ! $field['save_id'] ) {
			$_new_id = '';
		} else {
			// otherwise get the file ID
			$_new_id = isset( $_POST[ $_id_name ] ) ? $_POST[ $_id_name ] : null;

			// If there is no ID saved yet, try to get it from the url
			if ( isset( $_POST[ $field['id'] ] ) && $_POST[ $field['id'] ] && ! $_new_id ) {
				$_new_id = cmb_Meta_Box::image_id_from_url( esc_url_raw( $_POST[ $field['id'] ] ) );
			}

		}

		if ( $_new_id && $_new_id != $_id_old ) {
			$updated[] = $_id_name;
			cmb_Meta_Box::update_data( $_new_id, $_id_name );
		} elseif ( '' == $_new_id && $_id_old ) {
			$updated[] = $_id_name;
			cmb_Meta_Box::remove_data( $_id_name, $old );
		}

		return self::default_sanitization( $meta, $field );

	}

	/**
	 * Catchall method if field's 'sanitization_cb' is NOT defined, or field type does not have a corresponding validation method
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		list( $meta_value, $field ) = $arguments;
		return self::default_sanitization( $meta_value, $field );
	}

	/**
	 * Default fallback sanitization method. Applies filters.
	 * @since  1.0.2
	 * @param  mixed $meta_value Meta value
	 * @param  array $field      Field config array
	 */
	public static function default_sanitization( $meta_value, $field ) {

		$object_type = cmb_Meta_Box::get_object_type();
		$object_id   = cmb_Meta_Box::get_object_id();

		// Allow field type validation via filter
		$updated     = apply_filters( 'cmb_validate_'. $field['type'], null, $meta_value, $object_id, $field, $object_type );

		if ( null != $updated ) {
			return $updated;
		}

		// we'll fallback to 'sanitize_text_field', or 'wp_kses_post`
		switch ( $field['type'] ) {
			case 'wysiwyg':
				// $cb = 'wp_kses';
				// break;
			case 'textarea_small':
				$cb = array( 'cmb_Meta_Box_Sanitize', 'textarea' );
				break;
			default:
				$cb = 'sanitize_text_field';
				break;
		}

		// Handle repeatable fields array
		if ( is_array( $meta_value ) ) {
			foreach ( $meta_value as $key => $value ) {
				$meta_value[ $key ] = call_user_func( $cb, $value );
			}
		} else {
			$meta_value = call_user_func( $cb, $meta_value );
		}

		return $meta_value;
	}

}
