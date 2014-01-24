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
	 * @param  string  $meta Meta value
	 * @return string        Empty string or escaped url
	 */
	public static function text_url( $meta ) {

		$protocols = isset( cmb_Meta_Box::$field['protocols'] ) ? (array) cmb_Meta_Box::$field['protocols'] : null;
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = $value ? esc_url_raw( $value, $protocols ) : cmb_Meta_Box::$field['default'];
			}
		} else {
			$meta = $meta ? esc_url_raw( $meta, $protocols ) : cmb_Meta_Box::$field['default'];
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
	 * @param  string  $meta Meta value
	 * @return string        Empty string or validated email
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
	 * @param  string  $meta Meta value
	 * @return string        Empty string or validated money value
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
	 * Datetime to timestamp
	 * @since  1.0.1
	 * @param  string  $meta Meta value
	 * @return string        Timestring
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
	 * @param  string  $meta Meta value
	 * @return string        Timestring
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
	 * @param  string  $meta Meta value
	 * @return string        Sanitized data
	 */
	public static function textarea( $meta ) {
		return wp_kses_post( $meta );
	}

	/**
	 * Default fallback if field's 'sanitization_cb' is NOT defined, or field type does not have a corresponding validation method
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		list( $meta_value, $field ) = $arguments;

		// Handle repeatable fields array
		if ( is_array( $meta_value ) ) {
			foreach ( $meta_value as $key => $value ) {
				// Allow field type validation via filter
				$updated = apply_filters( 'cmb_validate_'. $field['type'], $value, cmb_Meta_Box::get_object_id(), $field, cmb_Meta_Box::get_object_type() );

				if ( $updated === $value ) {
					// If nothing changed, we'll fallback to 'sanitize_text_field'
					$updated = sanitize_text_field( $value );
				}
				$meta_value[ $key ] = $updated;
			}
		} else {

			switch ( $field['type'] ) {
				case 'wysiwyg':
				case 'textarea_small':
					return self::textarea( $meta_value );

				default:
					// Allow field type validation via filter
					$updated = apply_filters( 'cmb_validate_'. $field['type'], $meta_value, cmb_Meta_Box::get_object_id(), $field, cmb_Meta_Box::get_object_type() );
					if ( $updated === $meta_value ) {
						// If nothing changed, we'll fallback to 'sanitize_text_field'
						return sanitize_text_field( $meta_value );
					}
					return $updated;
			}

		}

		return $meta_value;
	}

}
