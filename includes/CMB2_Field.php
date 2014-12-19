<?php

/**
 * CMB field class
 * @since  1.1.0
 * @method string _id()
 * @method string type()
 */
class CMB2_Field {

	/**
	 * Metabox object id
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $object_id = null;

	/**
	 * Metabox object type
	 * @var   string
	 * @since 1.1.0
	 */
	public $object_type = '';

	/**
	 * Field arguments
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $args = array();

	/**
	 * Field group object or false (if no group)
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $group = false;

	/**
	 * Field meta value
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $value = null;

	/**
	 * Field meta value
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $escaped_value = null;

	/**
	 * Constructs our field object
	 * @since 1.1.0
	 * @param array $args  Field arguments
	 */
	public function __construct( $args ) {

		if ( ! empty( $args['group_field'] ) ) {
			$this->group       = $args['group_field'];
			$this->object_id   = $this->group->object_id;
			$this->object_type = $this->group->object_type;
		} else {
			$this->object_id   = $args['object_id'];
			$this->object_type = isset( $args['object_type'] ) ? $args['object_type'] : 'post';
		}

		$this->args = $this->_set_field_defaults( $args['field_args'] );

		/**
		 * Filter whether to override getting of meta value.
		 * Returning a non 'cmb2_field_no_override_val' value
		 * will effectively short-circuit the value retrieval.
		 *
		 * @since 2.0.0
		 *
		 * @param null|array|string $value       The value get_metadata() should
		 *                                       return - a single metadata value,
		 *                                       or an array of values.
		 * @param int               $object_id   Object ID.
		 * @param array             $field_args  All field arguments
		 * @param string            $object_type Object Type
		 * @param CMB2_Field object $field_obj   This field object
		 */
		$this->value = apply_filters( 'cmb2_override_meta_value', 'cmb2_field_no_override_val', $this->object_id, $this->args(), $this->object_type, $this );

		/**
		 * Filter whether to override getting of meta value.
		 *
		 * The dynamic portion of the hook, $field_id, refers to the current
		 * field id paramater. Returning a non 'cmb2_field_no_override_val' value
		 * will effectively short-circuit the value retrieval.
		 *
		 * @since 2.0.0
		 *
		 * @param null|array|string $value       The value get_metadata() should
		 *                                       return - a single metadata value,
		 *                                       or an array of values.
		 * @param int               $object_id   Object ID.
		 * @param array             $field_args  All field arguments
		 * @param string            $object_type Object Type
		 * @param CMB2_Field object $field_obj   This field object
		 */
		$this->value = apply_filters( "cmb2_override_{$this->id( true )}_meta_value", $this->value, $this->object_id, $this->args(), $this->object_type, $this );

		// If no override, get our meta
		$this->value = 'cmb2_field_no_override_val' === $this->value
			? $this->get_data()
			: $this->value;
	}

	/**
	 * Non-existent methods fallback to checking for field arguments of the same name
	 * @since  1.1.0
	 * @param  string $name     Method name
	 * @param  array  $arguments Array of passed-in arguments
	 * @return mixed             Value of field argument
	 */
	public function __call( $name, $arguments ) {
		$key = isset( $arguments[0] ) ? $arguments[0] : false;
		return $this->args( $name, $key );
	}

	/**
	 * Retrieves the field id
	 * @since  1.1.0
	 * @param  boolean $raw Whether to retrieve pre-modidifed id
	 * @return string       Field id
	 */
	public function id( $raw = false ) {
		$id = $raw ? '_id' : 'id';
		return $this->args( $id );
	}

	/**
	 * Get a field argument
	 * @since  1.1.0
	 * @param  string $key Argument to check
	 * @param  string $key Sub argument to check
	 * @return mixed       Argument value or false if non-existent
	 */
	public function args( $key = '', $_key = '' ) {
		$vars = $this->_data( 'args', $key );
		if ( $_key ) {
			return isset( $vars[ $_key ] ) ? $vars[ $_key ] : false;
		}
		return $vars;
	}

	/**
	 * Get Field's value
	 * @since  1.1.0
	 * @param  string $key If value is an array, is used to get array key->value
	 * @return mixed       Field value or false if non-existent
	 */
	public function value( $key = '' ) {
		return $this->_data( 'value', $key );
	}

	/**
	 * Retrieve a portion of a field property
	 * @since  1.1.0
	 * @param  string  $var Field property to check
	 * @param  string  $key Field property array key to check
	 * @return mixed        Queried property value or false
	 */
	public function _data( $var, $key = '' ) {
		$vars = $this->$var;
		if ( $key ) {
			return isset( $vars[ $key ] ) ? $vars[ $key ] : false;
		}
		return $vars;
	}

	/**
	 * Retrieves metadata/option data
	 * @since  1.0.1
	 * @param  string  $field_id Meta key/Option array key
	 * @return mixed             Meta/Option value
	 */
	public function get_data( $field_id = '', $args = array() ) {
		if ( $field_id ) {
			$args['field_id'] = $field_id;
		} else if ( $this->group ) {
			$args['field_id'] = $this->group->id();
		}

		$a = $this->data_args( $args );

		$data = 'options-page' === $a['type']
			? cmb2_options( $a['id'] )->get( $a['field_id'] )
			: get_metadata( $a['type'], $a['id'], $a['field_id'], ( $a['single'] || $a['repeat'] ) );

		if ( $this->group && $data ) {
			$data = isset( $data[ $this->group->args( 'count' ) ][ $this->args( '_id' ) ] )
				? $data[ $this->group->args( 'count' ) ][ $this->args( '_id' ) ]
				: false;
		}
		return $data;
	}

	/**
	 * Updates metadata/option data
	 * @since  1.0.1
	 * @param  mixed $value  Value to update data with
	 * @param  bool  $single Whether data is an array (add_metadata)
	 */
	public function update_data( $new_value, $single = true ) {
		$a = $this->data_args( array( 'single' => $single ) );

		$a[ 'value' ] = $a['repeat'] ? array_values( $new_value ) : $new_value;

		/**
		 * Filter whether to override saving of meta value.
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 *
		 * @param null|bool $check  Whether to allow updating metadata for the given type.
		 * @param array $args       Array of data about current field including:
		 *                              'type'     : Current object type
		 *                              'id'       : Current object ID
		 *                              'field_id' : Current Field ID
		 *                              'repeat'   : Whether current field is repeatable
		 *                              'single'   : Whether to save as a
		 *                              					single meta value
		 * @param array $field_args All field arguments
		 * @param CMB2_Field object $field_obj This field object
		 */
		$override = apply_filters( 'cmb2_override_meta_save', null, $a, $this->args(), $this );

		/**
		 * Filter whether to override saving of meta value.
		 *
		 * The dynamic portion of the hook, $a['field_id'], refers to the current
		 * field id paramater. Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 *
		 * @param null|bool $check  Whether to allow updating metadata for the given type.
		 * @param array $args       Array of data about current field including:
		 *                              'type'     : Current object type
		 *                              'id'       : Current object ID
		 *                              'field_id' : Current Field ID
		 *                              'repeat'   : Whether current field is repeatable
		 *                              'single'   : Whether to save as a
		 *                              					single meta value
		 * @param array $field_args All field arguments
		 * @param CMB2_Field object $field_obj This field object
		 */
		$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_save", $override, $a, $this->args(), $this );

		// If override, return that
		if ( null !== $override ) {
			return $override;
		}
		// Options page handling
		if ( 'options-page' === $a['type'] ) {
			return cmb2_options( $a['id'] )->update( $a['field_id'], $a[ 'value' ], false, $a['single'] );
		}
		// Add metadata if not single
		if ( ! $a['single'] ) {
			return add_metadata( $a['type'], $a['id'], $a['field_id'], $a[ 'value' ], false );
		}
		// Delete meta if we have an empty array
		if ( is_array( $a[ 'value' ] ) && empty( $a[ 'value' ] ) ) {
			return delete_metadata( $a['type'], $a['id'], $a['field_id'], $this->value );
		}

		// Update metadata
		return update_metadata( $a['type'], $a['id'], $a['field_id'], $a[ 'value' ] );
	}

	/**
	 * Removes/updates metadata/option data
	 * @since  1.0.1
	 * @param  string $old Old value
	 */
	public function remove_data( $old = '' ) {
		$a = $this->data_args( array( 'old' => $old ) );

		/**
		 * Filter whether to override removing of meta value.
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 *
		 * @param null|bool $delete Whether to allow metadata deletion of the given type.
		 * @param array $args       Array of data about current field including:
		 *                              'type'     : Current object type
		 *                              'id'       : Current object ID
		 *                              'field_id' : Current Field ID
		 *                              'repeat'   : Whether current field is repeatable
		 *                              'single'   : Whether to save as a
		 *                              					single meta value
		 * @param array $field_args All field arguments
		 * @param CMB2_Field object $field_obj This field object
		 */
		$override = apply_filters( 'cmb2_override_meta_remove', null, $a, $this->args(), $this );

		/**
		 * Filter whether to override removing of meta value.
		 *
		 * The dynamic portion of the hook, $a['field_id'], refers to the current
		 * field id paramater. Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 *
		 * @param null|bool $delete Whether to allow metadata deletion of the given type.
		 * @param array $args       Array of data about current field including:
		 *                              'type'     : Current object type
		 *                              'id'       : Current object ID
		 *                              'field_id' : Current Field ID
		 *                              'repeat'   : Whether current field is repeatable
		 *                              'single'   : Whether to save as a
		 *                              					single meta value
		 * @param array $field_args All field arguments
		 * @param CMB2_Field object $field_obj This field object
		 */
		$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_remove", $override, $a, $this->args(), $this );

		// If no override, remove as usual
		if ( null !== $override ) {
			return $override;
		}
		// Option page handling
		elseif ( 'options-page' === $a['type'] ) {
			return cmb2_options( $a['id'] )->remove( $a['field_id'] );
		}

		// Remove metadata
		return delete_metadata( $a['type'], $a['id'], $a['field_id'], $old );
	}

	/**
	 * data variables for get/set data methods
	 * @since  1.1.0
	 * @param  array $args Override arguments
	 * @return array       Updated arguments
	 */
	public function data_args( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'type'     => $this->object_type,
			'id'       => $this->object_id,
			'field_id' => $this->id( true ),
			'repeat'   => $this->args( 'repeatable' ),
			'single'   => ! $this->args( 'multiple' ),
		) );
		return $args;
	}

	/**
	 * Checks if field has a registered sanitization callback
	 * @since  1.0.1
	 * @param  mixed $meta_value Meta value
	 * @return mixed             Possibly sanitized meta value
	 */
	public function sanitization_cb( $meta_value ) {

		if ( $this->args( 'repeatable' ) && is_array( $meta_value ) ) {
			// Remove empties
			$meta_value = array_filter( $meta_value );
		}

		// Check if the field has a registered validation callback
		$cb = $this->maybe_callback( 'sanitization_cb' );
		if ( false === $cb ) {
			// If requesting NO validation, return meta value
			return $meta_value;
		} elseif ( $cb ) {
			// Ok, callback is good, let's run it.
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		$clean = new CMB2_Sanitize( $this, $meta_value );
		// Validation via 'CMB2_Sanitize' (with fallback filter)
		return $clean->{$this->type()}( $meta_value );
	}

	/**
	 * Process $_POST data to save this field's value
	 * @since  2.0.0
	 * @param  array $data_to_save $_POST data to check
	 * @return bool                Result of save
	 */
	public function save_field( $data_to_save ) {

		$meta_value = isset( $data_to_save[ $this->id( true ) ] )
			? $data_to_save[ $this->id( true ) ]
			: null;

		$new_value = $this->sanitization_cb( $meta_value );
		$name      = $this->id();
		$old       = $this->get_data();
		// if ( $this->args( 'multiple' ) && ! $this->args( 'repeatable' ) && ! $this->group ) {
		// 	$this->remove_data();
		// 	if ( ! empty( $new_value ) ) {
		// 		foreach ( $new_value as $add_new ) {
		// 			$this->updated[] = $name;
		// 			$this->update_data( $add_new, $name, false );
		// 		}
		// 	}
		// } else
		if ( ! empty( $new_value ) && $new_value !== $old  ) {
			$this->updated[] = $name;
			return $this->update_data( $new_value );
		} elseif ( empty( $new_value ) ) {
			if ( ! empty( $old ) ) {
				$this->updated[] = $name;
			}
			return $this->remove_data();
		}
	}

	/**
	 * Checks if field has a callback value
	 * @since  1.0.1
	 * @param  string $cb Callback string
	 * @return mixed      NULL, false for NO validation, or $cb string if it exists.
	 */
	public function maybe_callback( $cb ) {
		$field_args = $this->args();
		if ( ! isset( $field_args[ $cb ] ) ) {
			return;
		}

		// Check if metabox is requesting NO validation
		$cb = false !== $field_args[ $cb ] && 'false' !== $field_args[ $cb ] ? $field_args[ $cb ] : false;

		// If requestion NO validation, return false
		if ( ! $cb ) {
			return false;
		}

		if ( is_callable( $cb ) ) {
			return $cb;
		}
	}

	/**
	 * Determine if current type is excempt from escaping
	 * @since  1.1.0
	 * @return bool  True if exempt
	 */
	public function escaping_exception() {
		// These types cannot be escaped
		return in_array( $this->type(), array(
			'file_list',
			'multicheck',
			'text_datetime_timestamp_timezone',
		) );
	}

	/**
	 * Determine if current type cannot be repeatable
	 * @since  1.1.0
	 * @param  string $type Field type to check
	 * @return bool         True if type cannot be repeatable
	 */
	public function repeatable_exception( $type ) {
		// These types cannot be escaped
		return in_array( $type, array(
			'file', // Use file_list
			'radio',
			'title',
			'group',
			// @todo Ajax load wp_editor: http://wordpress.stackexchange.com/questions/51776/how-to-load-wp-editor-through-ajax-jquery
			'wysiwyg',
			'checkbox',
			'radio_inline',
			'taxonomy_radio',
			'taxonomy_select',
			'taxonomy_multicheck',
		) );
	}

	/**
	 * Escape the value before output. Defaults to 'esc_attr()'
	 * @since  1.0.1
	 * @param  mixed  $meta_value Meta value
	 * @param  mixed  $func       Escaping function (if not esc_attr())
	 * @return mixed              Final value
	 */
	public function escaped_value( $func = 'esc_attr', $meta_value = '' ) {

		if ( ! is_null( $this->escaped_value ) ) {
			return $this->escaped_value;
		}

		$meta_value = $meta_value ? $meta_value : $this->value();

		// Check if the field has a registered escaping callback
		if ( $cb = $this->maybe_callback( 'escape_cb' ) ) {
			// Ok, callback is good, let's run it.
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		// Or custom escaping filter can be used
		$esc = apply_filters( 'cmb2_types_esc_'. $this->type(), null, $meta_value, $this->args(), $this );
		if ( null !== $esc ) {
			return $esc;
		}

		if ( false === $cb || $this->escaping_exception() ) {
			// If requesting NO escaping, return meta value
			return ! empty( $meta_value ) ? $meta_value : $this->args( 'default' );
		}

		// escaping function passed in?
		$func       = $func ? $func : 'esc_attr';
		$meta_value = ! empty( $meta_value ) ? $meta_value : $this->args( 'default' );

		if ( is_array( $meta_value ) ) {
			foreach ( $meta_value as $key => $value ) {
				$meta_value[ $key ] = call_user_func( $func, $value );
			}
		} else {
			$meta_value = call_user_func( $func, $meta_value );
		}

		$this->escaped_value = $meta_value;
		return $this->escaped_value;
	}

	/**
	 * Offset a time value based on timezone
	 * @since  1.0.0
	 * @return string Offset time string
	 */
	public function field_timezone_offset() {
		return cmb2_utils()->timezone_offset( $this->field_timezone() );
	}

	/**
	 * Return timezone string
	 * @since  1.0.0
	 * @return string Timezone string
	 */
	public function field_timezone() {

		// Is timezone arg set?
		if ( $this->args( 'timezone' ) ) {
			return $this->args( 'timezone' ) ;
		}
		// Is there another meta key with a timezone stored as its value we should use?
		else if ( $this->args( 'timezone_meta_key' ) ) {
			return $this->get_data( $this->args( 'timezone_meta_key' ) );
		}

		return false;
	}

	/**
	 * Format the timestamp field value based on the field date/time format arg
	 * @since  2.0.0
	 * @param  int    $meta_value Timestamp
	 * @param  string $format     Either date_format or time_format
	 * @return string             Formatted date
	 */
	public function format_timestamp( $meta_value, $format = 'date_format' ) {
		return date( stripslashes( $this->args( $format ) ), $meta_value );
	}

	/**
	 * Return a formatted timestamp for a field
	 * @since  2.0.0
	 * @param  string $format Either date_format or time_format
	 * @return string         Formatted date
	 */
	public function get_timestamp_format( $format = 'date_format', $meta_value = 0 ) {
		$meta_value = $meta_value ? $meta_value : $this->escaped_value();
		$meta_value = cmb2_utils()->make_valid_time_stamp( $meta_value );

		if ( empty( $meta_value ) ) {
			return '';
		}

		return is_array( $meta_value )
			? array_map( array( $this, 'format_timestamp' ), $meta_value, $format )
			: $this->format_timestamp( $meta_value, $format );
	}

	/**
	 * Render a field row
	 * @since 1.0.0
	 */
	public function render_field() {

		// If field is requesting to not be shown on the front-end
		if ( ! is_admin() && ! $this->args( 'on_front' ) ) {
			return;
		}

		// If field is requesting to be conditionally shown
		if ( is_callable( $this->args( 'show_on_cb' ) ) && ! call_user_func( $this->args( 'show_on_cb' ), $this ) ) {
			return;
		}

		$this->peform_param_callback( 'before_row' );

		printf( "<div class=\"cmb-row %s\">\n", $this->row_classes() );

		if ( 'title' == $this->type() || ! $this->args( 'show_names' ) ) {
			echo "\t<div class=\"cmb-td\">\n";

			if ( ! $this->args( 'show_names' ) ) {
				$style = 'title' == $this->type() ? '' : ' style="display:none;"';
				printf( "\n<label%s for=\"%s\">%s</label>\n", $style, $this->id(), $this->args( 'name' ) );
			}
		} else {

			if ( $this->args( 'name' ) ) {
				printf( '<div class="cmb-th"><label for="%1$s">%2$s</label></div>', $this->id(), $this->args( 'name' ) );
			}

			echo "\n\t<div class=\"cmb-td\">\n";
		}

		$this->peform_param_callback( 'before' );

		$this_type = new CMB2_Types( $this );
		$this_type->render();

		$this->peform_param_callback( 'after' );

		echo "\n\t</div>\n</div>";

		$this->peform_param_callback( 'after_row' );
	}

	/**
	 * Defines the classes for the current CMB field row
	 *
	 * @since  2.0.0
	 * @return string Space concatenated list of classes
	 */
	public function row_classes() {
		$classes = 'cmb-type-'. str_replace( '_', '-', sanitize_html_class( $this->type() ) );
		$classes .= ' cmb2-id-'. str_replace( '_', '-', sanitize_html_class( $this->id() ) );
		$classes .= $this->args( 'repeatable' ) ? ' cmb-repeat' : '';
		$classes .= $this->group ? ' cmb-repeat-group-field' : '';
		// 'inline' flag, or _inline in the field type, set to true
		$classes .= $this->args( 'inline' ) ? ' cmb-inline' : '';

		$repeat_table_rows_types = apply_filters( 'cmb2_repeat_table_row_types', array(
			'text_url', 'text'
		) );

		if ( in_array( $this->type(), $repeat_table_rows_types ) ) {
			$classes .= ' table-layout';
		}

		return apply_filters( 'cmb2_row_classes', $classes, $this );
	}

	/**
	 * Check if param is a callback, and if so, call it.
	 * If not echo out whatever is there.
	 *
	 * @since  2.0.0
	 * @param  string  $param Field parameter
	 */
	public function peform_param_callback( $param ) {
		if ( $cb = $this->maybe_callback( $param ) ) {
			// Ok, callback is good, let's run it and bail
			echo call_user_func( $cb, $this->args(), $this );
			return;
		}

		// Otherwise just echo out whatever's there
		echo $this->args( $param );
	}

	/**
	 * Replaces a hash key - {#} - with the repeatable count
	 * @since  1.2.0
	 * @param  string $value Value to update
	 * @return string        Updated value
	 */
	public function replace_hash( $value ) {
		// Replace hash with 1 based count
		return str_ireplace( '{#}', ( $this->count() + 1 ), $value );
	}

	/**
	 * Retrieve options args. Calls options_cb if it exists.
	 * @since  2.0.0
	 * @param  string  $key Specific option to retrieve
	 * @return array        Array of options
	 */
	public function options( $key = '' ) {
		if ( isset( $this->field_options ) && is_array( $this->field_options ) ) {
			if ( $key ) {
				return array_key_exists( $key, $this->field_options ) ? $this->field_options[ $key ] : false;
			}

			return $this->field_options;
		}

		$this->field_options = (array) $this->args['options'];

		if ( is_callable( $this->args['options_cb'] ) ) {
			$options = call_user_func( $this->args['options_cb'], $this );

			if ( $options && is_array( $options ) ) {
				$this->field_options += $options;
			}
		}

		if ( $key ) {
			return array_key_exists( $key, $this->field_options ) ? $this->field_options[ $key ] : false;
		}

		return $this->field_options;
	}

	/**
	 * Fills in empty field parameters with defaults
	 * @since 1.1.0
	 * @param array $args Metabox field config array
	 */
	public function _set_field_defaults( $args ) {

		// Set up blank or default values for empty ones
		$args = wp_parse_args( $args, array(
			'type'              => '',
			'name'              => '',
			'desc'              => '',
			'before'            => '',
			'after'             => '',
			'options_cb'        => '',
			'options'           => array(),
			'attributes'        => array(),
			'protocols'         => null,
			'default'           => null,
			'select_all_button' => true,
			'multiple'          => false,
			'repeatable'        => false,
			'inline'            => false,
			'on_front'          => true,
			'show_names'        => true,
			'date_format'       => 'm\/d\/Y',
			'time_format'       => 'h:i A',
			'description'       => isset( $args['desc'] ) ? $args['desc'] : '',
			'preview_size'      => 'file' == $args['type'] ? array( 350, 350 ) : array( 50, 50 ),
		) );


		// Allow a filter override of the default value
		$args['default']    = apply_filters( 'cmb2_default_filter', $args['default'], $this );
		// $args['multiple']   = isset( $args['multiple'] ) ? $args['multiple'] : ( 'multicheck' == $args['type'] ? true : false );
		$args['repeatable'] = $args['repeatable'] && ! $this->repeatable_exception( $args['type'] );
		$args['inline']     = $args['inline'] || false !== stripos( $args['type'], '_inline' );

		// options param can be passed a callback as well
		if ( is_callable( $args['options'] ) ) {
			$args['options_cb'] = $args['options'];
			$args['options'] = array();
		}

		$args['options']    = 'group' == $args['type'] ? wp_parse_args( $args['options'], array(
			'add_button'    => __( 'Add Group', 'cmb2' ),
			'remove_button' => __( 'Remove Group', 'cmb2' ),
		) ) : $args['options'];

		$args['_id']        = $args['id'];
		$args['_name']      = $args['id'];

		if ( $this->group ) {

			$args['id']    = $this->group->args( 'id' ) .'_'. $this->group->args( 'count' ) .'_'. $args['id'];
			$args['_name'] = $this->group->args( 'id' ) .'['. $this->group->args( 'count' ) .']['. $args['_name'] .']';
		}

		if ( 'wysiwyg' == $args['type'] ) {
			$args['id'] = strtolower( str_ireplace( '-', '_', $args['id'] ) );
			$args['options']['textarea_name'] = $args['_name'];
		}

		$option_types = apply_filters( 'cmb2_all_or_nothing_types', array( 'taxonomy_select', 'taxonomy_radio', 'taxonomy_radio_inline' ), $this );

		if ( in_array( $args['type'], $option_types, true ) ) {

			$args['show_option_none'] = isset( $args['show_option_none'] ) ? $args['show_option_none'] : __( 'None', 'cmb2' );
			$args['show_option_all']  = isset( $args['show_option_all'] ) ? $args['show_option_all'] : __( 'All', 'cmb2' ); // @todo: implementation

		}

		return $args;
	}

	/**
	 * Updates attributes array values unless they exist from the field config array
	 * @since 1.1.0
	 * @param array $attrs Array of attributes to update
	 */
	public function maybe_set_attributes( $attrs = array() ) {
		return wp_parse_args( $this->args['attributes'], $attrs );
	}

}
