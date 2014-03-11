<?php

/**
 * CMB field validation
 * @since  0.0.4
 */
class cmb_Meta_Box_Sanitize {

	/**
	 * A CMB field object
	 * @var cmb_Meta_Box_field object
	 */
	public $field;

	/**
	 * Field's $_POST value
	 * @var mixed
	 */
	public $value;

	/**
	 * Setup our class vars
	 * @since 1.0.3
	 * @param object $field A CMB field object
	 * @param mixed  $value Field value
	 */
	public function __construct( $field, $value ) {
		$this->field       = $field;
		$this->value       = $value;
		$this->object_id   = cmb_Meta_Box::get_object_id();
		$this->object_type = cmb_Meta_Box::get_object_type();
	}

	/**
	 * Catchall method if field's 'sanitization_cb' is NOT defined, or field type does not have a corresponding validation method
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		list( $value ) = $arguments;
		return $this->default_sanitization( $value );
	}

	/**
	 * Default fallback sanitization method. Applies filters.
	 * @since  1.0.2
	 * @param  mixed $value Meta value
	 */
	public function default_sanitization( $value ) {

		// Allow field type validation via filter
		$updated = apply_filters( 'cmb_validate_'. $this->field->type(), null, $value, $this->object_id, $this->field->args(), $this );

		if ( null !== $updated )
			return $updated;

		switch ( $this->field->type() ) {
			case 'wysiwyg':
				// $value = wp_kses( $value );
				// break;
			case 'textarea_small':
				return $this->textarea( $value );
			case 'taxonomy_select':
			case 'taxonomy_radio':
			case 'taxonomy_multicheck':
				if ( $this->field->args( 'taxonomy' ) ) {
					return wp_set_object_terms( $this->object_id, $value, $this->field->args( 'taxonomy' ) );
				}
			case 'multicheck':
			case 'file_list':
			case 'oembed':
				// no filtering
				return $value;
			default:
				// Handle repeatable fields array
				// We'll fallback to 'sanitize_text_field'
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : call_user_func( 'sanitize_text_field', $value );
		}

	}

	/**
	 * Simple checkbox validation
	 * @since  1.0.1
	 * @param  mixed  $val 'on' or false
	 * @return mixed         'on' or false
	 */
	public function checkbox( $value ) {
		return $value === 'on' ? 'on' : false;
	}

	/**
	 * Validate url in a meta value
	 * @since  1.0.1
	 * @param  string $value  Meta value
	 * @return string        Empty string or escaped url
	 */
	public function text_url( $value ) {
		$protocols = $this->field->args( 'protocols' );
		// for repeatable
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = $val ? esc_url_raw( $val, $protocols ) : $this->field->args( 'default' );
			}
		} else {
			$value = $value ? esc_url_raw( $value, $protocols ) : $this->field->args( 'default' );
		}

		return $value;
	}

	public function colorpicker( $value ) {
		// for repeatable
		if ( is_array( $value ) ) {
			$check = $value;
			$value = array();
			foreach ( $check as $key => $val ) {
				if ( $val && '#' != $val ) {
					$value[ $key ] = esc_attr( $val );
				}
			}
		} else {
			$value = ! $value || '#' == $value ? '' : esc_attr( $value );
		}
		return $value;
	}

	/**
	 * Validate email in a meta value
	 * @since  1.0.1
	 * @param  string $value Meta value
	 * @return string       Empty string or validated email
	 */
	public function text_email( $value ) {
		// for repeatable
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$val = trim( $val );
				$value[ $key ] = is_email( $val ) ? $val : '';
			}
		} else {
			$value = trim( $value );
			$value = is_email( $value ) ? $value : '';
		}

		return $value;
	}

	/**
	 * Validate money in a meta value
	 * @since  1.0.1
	 * @param  string $value Meta value
	 * @return string       Empty string or validated money value
	 */
	public function text_money( $value ) {
		
		global $wp_locale;
		
		$search = array( $wp_locale->number_format['thousands_sep'], $wp_locale->number_format['decimal_point'] );
		$replace = array( '', '.' );

		// for repeatable
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = number_format_i18n( (float) str_ireplace( $search, $replace, $val ), 2 );
			}
		} else {
			$value = number_format_i18n( (float) str_ireplace( $search, $replace, $value ), 2 );
		}

		return $value;
	}

	/**
	 * Converts text date to timestamp
	 * @since  1.0.2
	 * @param  string $value Meta value
	 * @return string       Timestring
	 */
	public function text_date_timestamp( $value ) {
		return is_array( $value ) ? array_map( 'strtotime', $value ) : strtotime( $value );
	}

	/**
	 * Datetime to timestamp
	 * @since  1.0.1
	 * @param  string $value Meta value
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp( $value, $repeat = false ) {

		$test = is_array( $value ) ? array_filter( $value ) : '';
		if ( empty( $test ) )
			return '';

		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) )
			return $repeat_value;

		$value = strtotime( $value['date'] .' '. $value['time'] );

		if ( $tz_offset = $this->field->field_timezone_offset() )
			$value += $tz_offset;

		return $value;
	}

	/**
	 * Datetime to imestamp with timezone
	 * @since  1.0.1
	 * @param  string $value Meta value
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp_timezone( $value, $repeat = false ) {

		$test = is_array( $value ) ? array_filter( $value ) : '';
		if ( empty( $test ) )
			return '';

		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) )
			return $repeat_value;

		$tzstring = null;

		if ( is_array( $value ) && array_key_exists( 'timezone', $value ) )
			$tzstring = $value['timezone'];

		if ( empty( $tzstring ) )
			$tzstring = cmb_Meta_Box::timezone_string();

		$offset = cmb_Meta_Box::timezone_offset( $tzstring, true );

		if ( substr( $tzstring, 0, 3 ) === 'UTC' )
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );

		$value = new DateTime( $value['date'] .' '. $value['time'], new DateTimeZone( $tzstring ) );
		$value = serialize( $value );

		return $value;
	}

	/**
	 * Sanitize textareas and wysiwyg fields
	 * @since  1.0.1
	 * @param  string $value Meta value
	 * @return string       Sanitized data
	 */
	public function textarea( $value ) {
		return is_array( $value ) ? array_map( 'wp_kses_post', $value ) : wp_kses_post( $value );
	}

	/**
	 * Sanitize code textareas
	 * @since  1.0.2
	 * @param  string $value Meta value
	 * @return string       Sanitized data
	 */
	public function textarea_code( $value, $repeat = false ) {
		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) )
			return $repeat_value;

		return htmlspecialchars_decode( stripslashes( $value ) );
	}

	/**
	 * Sanitize code textareas
	 * @since  1.0.2
	 * @param  string $value  Meta value
	 * @return string        Sanitized data
	 */
	public function file( $value ) {
		$args = $this->field->args();
		$id = $this->field->id();
		$_id_name = $id .'_id';
		// get _id old value
		$_id_old = $this->field->get_data( $_id_name );

		// If specified NOT to save the file ID
		if ( isset( $args['save_id'] ) && ! $args['save_id'] ) {
			$_new_id = '';
		} else {
			// otherwise get the file ID
			$_new_id = isset( $_POST[ $_id_name ] ) ? $_POST[ $_id_name ] : null;

			// If there is no ID saved yet, try to get it from the url
			if ( isset( $_POST[ $id ] ) && $_POST[ $id ] && ! $_new_id ) {
				$_new_id = cmb_Meta_Box::image_id_from_url( esc_url_raw( $_POST[ $id ] ) );
			}

		}

		if ( $_new_id && $_new_id != $_id_old ) {
			$updated = cmb_Meta_Box::update_data( $_new_id, $_id_name );
		} elseif ( '' == $_new_id && $_id_old ) {
			cmb_Meta_Box::remove_data( $_id_name, $old );
		}

		return $this->default_sanitization( $value );

	}

	/**
	 * If repeating, loop through and re-apply sanitization method
	 * @since  1.0.3
	 * @param  mixed  $value  Meta value
	 * @param  string $method Class method
	 * @param  bool   $repeat Whether repeating or not
	 * @return mixed          Sanitized value
	 */
	public function _check_repeat( $value, $method, $repeat ) {
		if ( $repeat || ! $this->field->args( 'repeatable' ) )
			return;
		$new_value = array();
		foreach ( $value as $iterator => $val ) {
			$new_value[] = $this->$method( $val, true );
		}
		return $new_value;
	}

}
