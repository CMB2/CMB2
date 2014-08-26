<?php

/**
 * CMB field class
 * @since  1.1.0
 */
class CMB2_Field {

	/**
	 * Metabox object id
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $object_id;

	/**
	 * Metabox object type
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $object_type;

	/**
	 * Field arguments
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $args;

	/**
	 * Field group object
	 * @var   array
	 * @since 1.1.0
	 */
	public $group;

	/**
	 * Field meta value
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $value;

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
			$this->object_type = $args['object_type'];
			$this->group       = false;
		}

		$this->args = $this->_set_field_defaults( $args['field_args'] );

		// Allow an override for the field's value
		// (assuming no one would want to save 'cmb2_field_no_override_val' as a value)
		$this->value = apply_filters( 'cmb2_override_meta_value', 'cmb2_field_no_override_val', $this->object_id, $this->args(), $this->object_type, $this );

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

		$new_value = $a['repeat'] ? array_values( $new_value ) : $new_value;

		if ( 'options-page' === $a['type'] ) {
			return cmb2_options( $a['id'] )->update( $a['field_id'], $new_value, false, $a['single'] );
		}

		if ( ! $a['single'] ) {
			return add_metadata( $a['type'], $a['id'], $a['field_id'], $new_value, false );
		}

		return update_metadata( $a['type'], $a['id'], $a['field_id'], $new_value );
	}

	/**
	 * Removes/updates metadata/option data
	 * @since  1.0.1
	 * @param  string $old Old value
	 */
	public function remove_data( $old = '' ) {
		$a = $this->data_args();

		return 'options-page' === $a['type']
			? cmb2_options( $a['id'] )->remove( $a['field_id'] )
			: delete_metadata( $a['type'], $a['id'], $a['field_id'], $old );
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
	 * @return mixed             Possibly validated meta value
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
			if ( ! empty( $old ) )
				$this->updated[] = $name;
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

		if ( isset( $this->escaped_value ) )
			return $this->escaped_value;

		$meta_value = $meta_value ? $meta_value : $this->value();
		// Check if the field has a registered escaping callback
		$cb = $this->maybe_callback( 'escape_cb' );
		if ( false === $cb || $this->escaping_exception() ) {
			// If requesting NO escaping, return meta value
			return ! empty( $meta_value ) ? $meta_value : $this->args( 'default' );
		} elseif ( $cb ) {
			// Ok, callback is good, let's run it.
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		// Or custom escaping filter can be used
		$esc = apply_filters( 'cmb2_types_esc_'. $this->type(), null, $meta_value, $this->args(), $this );
		if ( null !== $esc ) {
			return $esc;
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

		$classes    = 'cmb-type-'. sanitize_html_class( $this->type() );
		$classes   .= ' cmb2_id_'. sanitize_html_class( $this->id() );
		$classes   .= $this->args( 'repeatable' ) ? ' cmb-repeat' : '';
		$classes   .= $this->group ? ' cmb-repeat-group-field' : '';
		// 'inline' flag, or _inline in the field type, set to true
		$classes   .= $this->args( 'inline' ) ? ' cmb-inline' : '';

		printf( "<li class=\"cmb-row %s\">\n", $classes );

		if ( 'title' == $this->type() || ! $this->args( 'show_names' ) ) {
			echo "\t<div class=\"cmb-td\">\n";

			if ( ! $this->args( 'show_names' ) ) {
				$style = 'title' == $this->type() ? ' style="display:none;"' : '';
				printf( "\n<label%s for=\"%s\">%s</label>\n", $style, $this->id(), $this->args( 'name' ) );
			}
		} else {

			printf( '<div class="cmb-th"><label for="%1$s">%2$s</label></div>', $this->id(), $this->args( 'name' ) );

			echo "\n\t<div class=\"cmb-td\">\n";
		}

		echo $this->args( 'before' );

		$this_type = new CMB2_Types( $this );
		$this_type->render();

		echo $this->args( 'after' );

		echo "\n\t</div>\n</li>";
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
	 * Fills in empty field parameters with defaults
	 * @since 1.1.0
	 * @param array $args Metabox field config array
	 */
	public function _set_field_defaults( $args ) {

		// Set up blank or default values for empty ones
		if ( ! isset( $args['name'] ) ) $args['name'] = '';
		if ( ! isset( $args['desc'] ) ) $args['desc'] = '';
		if ( ! isset( $args['before'] ) ) $args['before'] = '';
		if ( ! isset( $args['after'] ) ) $args['after'] = '';
		if ( ! isset( $args['protocols'] ) ) $args['protocols'] = null;
		if ( ! isset( $args['default'] ) ) $args['default'] = null;
		if ( ! isset( $args['description'] ) ) {
			$args['description'] = isset( $args['desc'] ) ? $args['desc'] : '';
		}
		if ( ! isset( $args['preview_size'] ) ) $args['preview_size'] = array( 50, 50 );
		if ( ! isset( $args['date_format'] ) ) $args['date_format'] = 'm\/d\/Y';
		if ( ! isset( $args['time_format'] ) ) $args['time_format'] = 'h:i A';
		// Allow a filter override of the default value
		$args['default']    = apply_filters( 'cmb2_default_filter', $args['default'], $this );
		// $args['multiple']   = isset( $args['multiple'] ) ? $args['multiple'] : ( 'multicheck' == $args['type'] ? true : false );
		$args['multiple']   = isset( $args['multiple'] ) ? $args['multiple'] : false;
		$args['select_all_button'] = isset( $args['select_all_button'] ) ? $args['select_all_button'] : true;
		$args['repeatable'] = isset( $args['repeatable'] ) && $args['repeatable'] && ! $this->repeatable_exception( $args['type'] );
		$args['inline']     = isset( $args['inline'] ) && $args['inline'] || false !== stripos( $args['type'], '_inline' );
		$args['on_front']   = ! ( isset( $args['on_front'] ) && ! $args['on_front'] );
		$args['attributes'] = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$args['options']    = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();

		$args['options']    = 'group' == $args['type'] ? wp_parse_args( $args['options'], array(
			'add_button'    => __( 'Add Group', 'cmb2' ),
			'remove_button' => __( 'Remove Group', 'cmb2' ),
		) ) : $args['options'];

		$args['_id']        = $args['id'];
		$args['_name']      = $args['id'];

		if ( $this->group ) {

			$args['id'] = $this->group->args( 'id' ) .'_'. $this->group->args( 'count' ) .'_'. $args['id'];
			$args['_name'] = $this->group->args( 'id' ) .'['. $this->group->args( 'count' ) .']['. $args['_name'] .']';
		}

		if ( 'wysiwyg' == $args['type'] ) {
			$args['id'] = strtolower( str_ireplace( '-', '_', $args['id'] ) );
			$args['options']['textarea_name'] = $args['_name'];
		}

		$option_types = apply_filters( 'cmb2_all_or_nothing_types', array( 'taxonomy_select', 'taxonomy_radio', 'taxonomy_radio_inline' ), $this );

		if ( in_array( $args['type'], $option_types, true ) ) {

			$args['show_option_none'] = isset( $args['show_option_none'] ) ? $args['show_option_none'] : 'None';
			$args['show_option_all'] = isset( $args['show_option_all'] ) ? $args['show_option_all'] : 'All'; // @todo: implementation

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
