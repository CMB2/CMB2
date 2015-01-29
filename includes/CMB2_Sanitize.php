<?php

/**
 * CMB field validation
 * @since  0.0.4
 * @method string _id()
 */
class CMB2_Sanitize {

	/**
	 * A CMB field object
	 * @var CMB2_Field object
	 */
	public $field;

	/**
	 * Field's $_POST value
	 * @var mixed
	 */
	public $value;

	/**
	 * Setup our class vars
	 * @since 1.1.0
	 * @param CMB2_Field $field A CMB field object
	 * @param mixed      $value Field value
	 */
	public function __construct( CMB2_Field $field, $value ) {
		$this->field = $field;
		$this->value = stripslashes_deep( $value ); // get rid of those evil magic quotes
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

		/**
		 * Filter the value before it is saved.
		 *
		 * The dynamic portion of the hook name, $this->field->type(), refers to the field type.
		 *
		 * Passing a non-null value to the filter will short-circuit saving
		 * the field value, saving the passed value instead.
		 *
		 * @param bool|mixed $override_value Sanitization/Validation override value to return.
		 *                                   Default false to skip it.
		 * @param mixed      $value      The value to be saved to this field.
		 * @param int        $object_id  The ID of the object where the value will be saved
		 * @param array      $field_args The current field's arguments
		 * @param object     $sanitizer  This `CMB2_Sanitize` object
		 */
		$override_value = apply_filters( 'cmb2_sanitize_'. $this->field->type(), null, $value, $this->field->object_id, $this->field->args(), $this );
		/**
		 * DEPRECATED. See documentation above.
		 */
		$override_value = apply_filters( 'cmb2_validate_'. $this->field->type(), $override_value, $value, $this->field->object_id, $this->field->args(), $this );

		if ( null !== $override_value ) {
			return $override_value;
		}

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
					return wp_set_object_terms( $this->field->object_id, $value, $this->field->args( 'taxonomy' ) );
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
	 * @param  mixed $value 'on' or false
	 * @return string|false 'on' or false
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
	 * @return string       Empty string or sanitized email
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
	 * @return string       Empty string or sanitized money value
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
		if ( empty( $test ) ) {
			return '';
		}

		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		$value = strtotime( $value['date'] .' '. $value['time'] );

		if ( $tz_offset = $this->field->field_timezone_offset() ) {
			$value += $tz_offset;
		}

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
		if ( empty( $test ) ) {
			return '';
		}

		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		$tzstring = null;

		if ( is_array( $value ) && array_key_exists( 'timezone', $value ) ) {
			$tzstring = $value['timezone'];
		}

		if ( empty( $tzstring ) ) {
			$tzstring = cmb2_utils()->timezone_string();
		}

		$offset = cmb2_utils()->timezone_offset( $tzstring, true );

		if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );
		}

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
		if ( $repeat_value = $this->_check_repeat( $value, __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		return htmlspecialchars_decode( stripslashes( $value ) );
	}

	/**
	 * Peforms saving of `file` attachement's ID
	 * @since  1.1.0
	 * @param  string $value File url
	 */
	public function _save_file_id( $value ) {
		$group      = $this->field->group;
		$args       = $this->field->args();
		$args['id'] = $args['_id'] . '_id';

		unset( $args['_id'], $args['_name'] );
		// And get new field object
		$field      = new CMB2_Field( array(
			'field_args'  => $args,
			'group_field' => $group,
			'object_id'   => $this->field->object_id,
			'object_type' => $this->field->object_type,
		) );
		$id_key     = $field->_id();
		$id_val_old = $field->escaped_value( 'absint' );

		if ( $group ) {
			// Check group $_POST data
			$i       = $group->index;
			$base_id = $group->_id();
			$id_val  = isset( $_POST[ $base_id ][ $i ][ $id_key ] ) ? absint( $_POST[ $base_id ][ $i ][ $id_key ] ) : 0;

		} else {
			// Check standard $_POST data
			$id_val = isset( $_POST[ $field->id() ] ) ? $_POST[ $field->id() ] : null;

		}

		// If there is no ID saved yet, try to get it from the url
		if ( $value && ! $id_val ) {
			$id_val = cmb2_utils()->image_id_from_url( $value );
		}

		if ( $group ) {
			return array(
				'attach_id' => $id_val,
				'field_id'  => $id_key,
			);
		}

		if ( $id_val && $id_val != $id_val_old ) {
			return $field->update_data( $id_val );
		} elseif ( empty( $id_val ) && $id_val_old ) {
			return $field->remove_data( $id_val_old );
		}
	}

	/**
	 * Handles saving of attachment post ID and sanitizing file url
	 * @since  1.1.0
	 * @param  string $value File url
	 * @return string        Sanitized url
	 */
	public function file( $value ) {
		$id_value = $this->_save_file_id( $value );
		$clean = $this->text_url( $value );

		// Return an array with url/id if saving a group field
		return $this->field->group ? array_merge( array( 'url' => $clean), $id_value ) : $clean;
	}

	/**
	 * If repeating, loop through and re-apply sanitization method
	 * @since  1.1.0
	 * @param  mixed  $value  Meta value
	 * @param  string $method Class method
	 * @param  bool   $repeat Whether repeating or not
	 * @return mixed          Sanitized value
	 */
	public function _check_repeat( $value, $method, $repeat ) {
		if ( $repeat || ! $this->field->args( 'repeatable' ) ) {
			return;
		}
		$new_value = array();
		foreach ( $value as $iterator => $val ) {
			$new_value[] = $this->$method( $val, true );
		}
		return $new_value;
	}

}
