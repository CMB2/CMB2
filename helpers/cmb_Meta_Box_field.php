<?php

/**
 * CMB field class
 * @since  1.0.3
 */
class cmb_Meta_Box_field {

	/**
	 * Metabox object id
	 * @var   mixed
	 * @since 1.0.0
	 */
	public $object_id;

	/**
	 * Metabox object type
	 * @var   mixed
	 * @since 1.0.0
	 */
	public $object_type;

	/**
	 * Field arguments
	 * @var   mixed
	 * @since 1.0.0
	 */
	public $args;

	/**
	 * Field meta value
	 * @var   mixed
	 * @since 1.0.0
	 */
	public $meta;

	/**
	 * An iterator value for repeatable fields
	 * @var   integer
	 * @since 1.0.0
	 */
	public $iterator = 0;


	public function __construct( $field_args, $object_id, $object_type ) {
		$this->object_id   = $object_id;
		$this->object_type = $object_type;
		$this->args        = $this->set_field_defaults( $field_args );

		// Allow an override for the field's value
		// (assuming no one would want to save 'cmb_no_override_val' as a value)
		$this->value = apply_filters( 'cmb_override_meta_value', 'cmb_no_override_val', $object_id, $this->args(), $object_type, $this );

		// If no override, get our meta
		$this->value = 'cmb_no_override_val' === $this->value
			? $this->get_data()
			: $this->value;
	}

	public function args( $key = '' ) {
		return $this->_data( 'args', $key );
	}

	public function value( $key = '' ) {
		return $this->_data( 'value', $key );
	}

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
	public function get_data() {

		$type     = $this->object_type;
		$id       = $this->object_id;
		$field_id = $this->args( 'id' );
		$single   = ! $this->args( 'multiple' );
		$repeat   = $this->args( 'repeatable' );

		$data = 'options-page' === $type
			? cmb_Meta_Box::get_option( $id, $field_id )
			: get_metadata( $type, $id, $field_id, ( $single || $repeat ) /* If multicheck this can be multiple values */ );

		return $data;
	}

	public function escaping_exception() {
		// These types cannot be escaped
		return in_array( $this->args( 'type' ), array(
			'file_list',
			'multicheck',
			'text_datetime_timestamp_timezone',
		) );
	}

	public function repeatable_exception( $type ) {
		// These types cannot be escaped
		return in_array( $type, array(
			'title',
			'file', // Use file_list
			'radio',
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
		$cb = cmb_Meta_Box::maybe_callback( $this->args(), 'escape_cb' );
		if ( false === $cb || $this->escaping_exception() ) {
			// If requesting NO escaping, return meta value
			return $meta_value;
		} elseif ( $cb ) {
			// Ok, callback is good, let's run it.
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		// Or custom escaping filter can be used
		$esc = apply_filters( 'cmb_types_esc_'. $this->args( 'type' ), null, $meta_value, $this->args(), $this );
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
	 * Fills in empty field parameters with defaults
	 * @since  1.0.3
	 * @param  array $field Metabox field config array
	 */
	public function set_field_defaults( $args ) {

		// Set up blank or default values for empty ones
		if ( ! isset( $args['name'] ) ) $args['name'] = '';
		if ( ! isset( $args['desc'] ) ) $args['desc'] = '';
		if ( ! isset( $args['before'] ) ) $args['before'] = '';
		if ( ! isset( $args['after'] ) ) $args['after'] = '';
		if ( ! isset( $args['protocols'] ) ) $args['protocols'] = null;
		if ( ! isset( $args['default'] ) ) {
			// Phase out 'std', and use 'default' instead
			$args['default'] = isset( $args['std'] ) ? $args['std'] : '';
		}
		if ( ! isset( $args['preview_size'] ) ) $args['preview_size'] = array( 50, 50 );
		// Allow a filter override of the default value
		$args['default']    = apply_filters( 'cmb_default_filter', $args['default'], $args, $this->object_type, $this->object_type );
		// 'cmb_std_filter' deprectated, use 'cmb_default_filter' instead
		$args['default']    = apply_filters( 'cmb_std_filter', $args['default'], $args, $this->object_type, $this->object_type );
		$args['allow']      = 'file' == $args['type'] && ! isset( $args['allow'] ) ? array( 'url', 'attachment' ) : array();
		$args['save_id']    = 'file' == $args['type'] && ! isset( $args['save_id'] );
		$args['multiple']   = isset( $args['multiple'] ) ? $args['multiple'] : ( 'multicheck' == $args['type'] ? true : false );
		$args['repeatable'] = isset( $args['repeatable'] ) && $args['repeatable'] && ! $this->repeatable_exception( $args['type'] );
		$args['inline']     = isset( $args['inline'] ) && $args['inline'] || false !== stripos( $args['type'], '_inline' );
		$args['on_front']   = ! ( isset( $args['on_front'] ) && ! $args['on_front'] );
		$args['attributes'] = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$args['options']    = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();

		if ( 'wysiwyg' == $args['type'] ) {
			$args['id'] = strtolower( str_ireplace( array( '-', '_' ), '', $args['id'] ) ) . 'wpeditor';
		}


		return $args;
	}

	/**
	 * Updates attributes array values unless they exist from the field config array
	 * @since  1.0.3
	 * @param  array  $attrs Array of attributes to update
	 */
	public function maybe_set_attributes( $attrs = array() ) {
		return wp_parse_args( $attrs, $this->args['attributes'] );
	}

}
