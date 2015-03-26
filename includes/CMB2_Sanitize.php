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
	 * Field's value
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
		return $this->default_sanitization( $this->value );
	}

	/**
	 * Default fallback sanitization method. Applies filters.
	 * @since  1.0.2
	 */
	public function default_sanitization() {

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
		$override_value = apply_filters( "cmb2_sanitize_{$this->field->type()}", null, $this->value, $this->field->object_id, $this->field->args(), $this );
		/**
		 * DEPRECATED. See documentation above.
		 */
		$override_value = apply_filters( "cmb2_validate_{$this->field->type()}", $override_value, $this->value, $this->field->object_id, $this->field->args(), $this );

		if ( null !== $override_value ) {
			return $override_value;
		}

		switch ( $this->field->type() ) {
			case 'wysiwyg':
				// $value = wp_kses( $this->value );
				// break;
			case 'textarea_small':
				return $this->textarea( $this->value );
			case 'taxonomy_select':
			case 'taxonomy_radio':
			case 'taxonomy_multicheck':
				if ( $this->field->args( 'taxonomy' ) ) {
					return wp_set_object_terms( $this->field->object_id, $this->value, $this->field->args( 'taxonomy' ) );
				}
			case 'multicheck':
			case 'file_list':
			case 'oembed':
				// no filtering
				return $this->value;
			default:
				// Handle repeatable fields array
				// We'll fallback to 'sanitize_text_field'
				return is_array( $this->value ) ? array_map( 'sanitize_text_field', $this->value ) : call_user_func( 'sanitize_text_field', $this->value );
		}
	}

	/**
	 * Simple checkbox validation
	 * @since  1.0.1
	 * @return string|false 'on' or false
	 */
	public function checkbox() {
		return $this->value === 'on' ? 'on' : false;
	}

	/**
	 * Validate url in a meta value
	 * @since  1.0.1
	 * @return string        Empty string or escaped url
	 */
	public function text_url() {
		$protocols = $this->field->args( 'protocols' );
		// for repeatable
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$this->value[ $key ] = $val ? esc_url_raw( $val, $protocols ) : $this->field->args( 'default' );
			}
		} else {
			$this->value = $this->value ? esc_url_raw( $this->value, $protocols ) : $this->field->args( 'default' );
		}

		return $this->value;
	}

	public function colorpicker() {
		// for repeatable
		if ( is_array( $this->value ) ) {
			$check = $this->value;
			$this->value = array();
			foreach ( $check as $key => $val ) {
				if ( $val && '#' != $val ) {
					$this->value[ $key ] = esc_attr( $val );
				}
			}
		} else {
			$this->value = ! $this->value || '#' == $this->value ? '' : esc_attr( $this->value );
		}
		return $this->value;
	}

	/**
	 * Validate email in a meta value
	 * @since  1.0.1
	 * @return string       Empty string or sanitized email
	 */
	public function text_email() {
		// for repeatable
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$val = trim( $val );
				$this->value[ $key ] = is_email( $val ) ? $val : '';
			}
		} else {
			$this->value = trim( $this->value );
			$this->value = is_email( $this->value ) ? $this->value : '';
		}

		return $this->value;
	}

	/**
	 * Validate money in a meta value
	 * @since  1.0.1
	 * @return string       Empty string or sanitized money value
	 */
	public function text_money() {

		global $wp_locale;

		$search = array( $wp_locale->number_format['thousands_sep'], $wp_locale->number_format['decimal_point'] );
		$replace = array( '', '.' );

		// for repeatable
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$this->value[ $key ] = number_format_i18n( (float) str_ireplace( $search, $replace, $val ), 2 );
			}
		} else {
			$this->value = number_format_i18n( (float) str_ireplace( $search, $replace, $this->value ), 2 );
		}

		return $this->value;
	}

	/**
	 * Converts text date to timestamp
	 * @since  1.0.2
	 * @return string       Timestring
	 */
	public function text_date_timestamp() {
		return is_array( $this->value ) ? array_map( 'strtotime', $this->value ) : strtotime( $this->value );
	}

	/**
	 * Datetime to timestamp
	 * @since  1.0.1
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp( $repeat = false ) {

		$test = is_array( $this->value ) ? array_filter( $this->value ) : '';
		if ( empty( $test ) ) {
			return '';
		}

		if ( $repeat_value = $this->_check_repeat( __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		$this->value = strtotime( $this->value['date'] . ' ' . $this->value['time'] );

		if ( $tz_offset = $this->field->field_timezone_offset() ) {
			$this->value += $tz_offset;
		}

		return $this->value;
	}

	/**
	 * Datetime to imestamp with timezone
	 * @since  1.0.1
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp_timezone( $repeat = false ) {

		$test = is_array( $this->value ) ? array_filter( $this->value ) : '';
		if ( empty( $test ) ) {
			return '';
		}

		if ( $repeat_value = $this->_check_repeat( __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		$tzstring = null;

		if ( is_array( $this->value ) && array_key_exists( 'timezone', $this->value ) ) {
			$tzstring = $this->value['timezone'];
		}

		if ( empty( $tzstring ) ) {
			$tzstring = cmb2_utils()->timezone_string();
		}

		$offset = cmb2_utils()->timezone_offset( $tzstring );

		if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );
		}

		$this->value = new DateTime( $this->value['date'] . ' ' . $this->value['time'], new DateTimeZone( $tzstring ) );
		$this->value = serialize( $this->value );

		return $this->value;
	}

	/**
	 * Sanitize textareas and wysiwyg fields
	 * @since  1.0.1
	 * @return string       Sanitized data
	 */
	public function textarea() {
		return is_array( $this->value ) ? array_map( 'wp_kses_post', $this->value ) : wp_kses_post( $this->value );
	}

	/**
	 * Sanitize code textareas
	 * @since  1.0.2
	 * @return string       Sanitized data
	 */
	public function textarea_code( $repeat = false ) {
		if ( $repeat_value = $this->_check_repeat( __FUNCTION__, $repeat ) ) {
			return $repeat_value;
		}

		return htmlspecialchars_decode( stripslashes( $this->value ) );
	}

	/**
	 * Peforms saving of `file` attachement's ID
	 * @since  1.1.0
	 */
	public function _save_file_id() {
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
		if ( $this->value && ! $id_val ) {
			$id_val = cmb2_utils()->image_id_from_url( $this->value );
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
	 * @return string        Sanitized url
	 */
	public function file() {
		$id_value = $this->_save_file_id( $this->value );
		$clean = $this->text_url( $this->value );

		// Return an array with url/id if saving a group field
		return $this->field->group ? array_merge( array( 'url' => $clean ), $id_value ) : $clean;
	}

	/**
	 * If repeating, loop through and re-apply sanitization method
	 * @since  1.1.0
	 * @param  string $method Class method
	 * @param  bool   $repeat Whether repeating or not
	 * @return mixed          Sanitized value
	 */
	public function _check_repeat( $method, $repeat ) {
		if ( $repeat || ! $this->field->args( 'repeatable' ) ) {
			return;
		}
		$new_value = array();
		foreach ( $this->value as $iterator => $val ) {
			$new_value[] = $this->$method( $val, true );
		}
		return $new_value;
	}

}
