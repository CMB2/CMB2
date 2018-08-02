<?php
/**
 * CMB2 field objects
 *
 * @since  1.1.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @method string _id()
 * @method string type()
 * @method mixed fields()
 */
class CMB2_Field extends CMB2_Base {

	/**
	 * The object properties name.
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected $properties_name = 'args';

	/**
	 * Field arguments
	 *
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $args = array();

	/**
	 * Field group object or false (if no group)
	 *
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $group = false;

	/**
	 * Field meta value
	 *
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $value = null;

	/**
	 * Field meta value
	 *
	 * @var   mixed
	 * @since 1.1.0
	 */
	public $escaped_value = null;

	/**
	 * Grouped Field's current numeric index during the save process
	 *
	 * @var   mixed
	 * @since 2.0.0
	 */
	public $index = 0;

	/**
	 * Array of field options
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	protected $field_options = array();

	/**
	 * Array of provided field text strings
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	protected $strings;

	/**
	 * The field's render context. In most cases, 'edit', but can be 'display'.
	 *
	 * @var   string
	 * @since 2.2.2
	 */
	public $render_context = 'edit';

	/**
	 * All CMB2_Field callable field arguments.
	 * Can be used to determine if a field argument is callable.
	 *
	 * @var array
	 */
	public static $callable_fields = array(
		'default_cb',
		'classes_cb',
		'options_cb',
		'text_cb',
		'label_cb',
		'render_row_cb',
		'display_cb',
		'before_group',
		'before_group_row',
		'before_row',
		'before',
		'before_field',
		'after_field',
		'after',
		'after_row',
		'after_group_row',
		'after_group',
	);

	/**
	 * Represents a unique hash representing this field.
	 *
	 * @since  2.2.4
	 *
	 * @var string
	 */
	protected $hash_id = '';

	/**
	 * Constructs our field object
	 *
	 * @since 1.1.0
	 * @param array $args Field arguments
	 */
	public function __construct( $args ) {

		if ( ! empty( $args['group_field'] ) ) {
			$this->group       = $args['group_field'];
			$this->object_id   = $this->group->object_id;
			$this->object_type = $this->group->object_type;
			$this->cmb_id      = $this->group->cmb_id;
		} else {
			$this->object_id   = isset( $args['object_id'] ) && '_' !== $args['object_id'] ? $args['object_id'] : 0;
			$this->object_type = isset( $args['object_type'] ) ? $args['object_type'] : 'post';

			if ( isset( $args['cmb_id'] ) ) {
				$this->cmb_id = $args['cmb_id'];
			}
		}

		$this->args = $this->_set_field_defaults( $args['field_args'] );

		if ( $this->object_id ) {
			$this->value = $this->get_data();
		}
	}

	/**
	 * Non-existent methods fallback to checking for field arguments of the same name
	 *
	 * @since  1.1.0
	 * @param  string $name     Method name
	 * @param  array  $arguments Array of passed-in arguments
	 * @return mixed             Value of field argument
	 */
	public function __call( $name, $arguments ) {
		if ( 'string' === $name ) {
			return call_user_func_array( array( $this, 'get_string' ), $arguments );
		}

		$key = isset( $arguments[0] ) ? $arguments[0] : '';
		return $this->args( $name, $key );
	}

	/**
	 * Retrieves the field id
	 *
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
	 *
	 * @since  1.1.0
	 * @param  string $key  Argument to check
	 * @param  string $_key Sub argument to check
	 * @return mixed        Argument value or false if non-existent
	 */
	public function args( $key = '', $_key = '' ) {
		$arg = $this->_data( 'args', $key );

		if ( in_array( $key, array( 'default', 'default_cb' ), true ) ) {

			$arg = $this->get_default();

		} elseif ( $_key ) {

			$arg = isset( $arg[ $_key ] ) ? $arg[ $_key ] : false;
		}

		return $arg;
	}

	/**
	 * Retrieve a portion of a field property
	 *
	 * @since  1.1.0
	 * @param  string $var Field property to check
	 * @param  string $key Field property array key to check
	 * @return mixed        Queried property value or false
	 */
	public function _data( $var, $key = '' ) {
		$vars = $this->{$var};
		if ( $key ) {
			return array_key_exists( $key, $vars ) ? $vars[ $key ] : false;
		}
		return $vars;
	}

	/**
	 * Get Field's value
	 *
	 * @since  1.1.0
	 * @param  string $key If value is an array, is used to get array key->value
	 * @return mixed       Field value or false if non-existent
	 */
	public function value( $key = '' ) {
		return $this->_data( 'value', $key );
	}

	/**
	 * Retrieves metadata/option data
	 *
	 * @since  1.0.1
	 * @param  string $field_id Meta key/Option array key
	 * @param  array  $args     Override arguments
	 * @return mixed            Meta/Option value
	 */
	public function get_data( $field_id = '', $args = array() ) {
		if ( $field_id ) {
			$args['field_id'] = $field_id;
		} elseif ( $this->group ) {
			$args['field_id'] = $this->group->id();
		}

		$a = $this->data_args( $args );

		/**
		 * Filter whether to override getting of meta value.
		 * Returning a non 'cmb2_field_no_override_val' value
		 * will effectively short-circuit the value retrieval.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $value     The value get_metadata() should
		 *                         return - a single metadata value,
		 *                         or an array of values.
		 *
		 * @param int   $object_id Object ID.
		 *
		 * @param array $args {
		 *     An array of arguments for retrieving data
		 *
		 *     @type string $type     The current object type
		 *     @type int    $id       The current object ID
		 *     @type string $field_id The ID of the field being requested
		 *     @type bool   $repeat   Whether current field is repeatable
		 *     @type bool   $single   Whether current field is a single database row
		 * }
		 *
		 * @param CMB2_Field object $field This field object
		 */
		$data = apply_filters( 'cmb2_override_meta_value', 'cmb2_field_no_override_val', $this->object_id, $a, $this );

		/**
		 * Filter and parameters are documented for 'cmb2_override_meta_value' filter (above).
		 *
		 * The dynamic portion of the hook, $field_id, refers to the current
		 * field id paramater. Returning a non 'cmb2_field_no_override_val' value
		 * will effectively short-circuit the value retrieval.
		 *
		 * @since 2.0.0
		 */
		$data = apply_filters( "cmb2_override_{$a['field_id']}_meta_value", $data, $this->object_id, $a, $this );

		// If no override, get value normally
		if ( 'cmb2_field_no_override_val' === $data ) {
			$data = 'options-page' === $a['type']
				? cmb2_options( $a['id'] )->get( $a['field_id'] )
				: get_metadata( $a['type'], $a['id'], $a['field_id'], ( $a['single'] || $a['repeat'] ) );
		}

		if ( $this->group ) {

			$data = is_array( $data ) && isset( $data[ $this->group->index ][ $this->args( '_id' ) ] )
				? $data[ $this->group->index ][ $this->args( '_id' ) ]
				: false;
		}

		return $data;
	}

	/**
	 * Updates metadata/option data
	 *
	 * @since  1.0.1
	 * @param  mixed $new_value Value to update data with
	 * @param  bool  $single    Whether data is an array (add_metadata)
	 */
	public function update_data( $new_value, $single = true ) {
		$a = $this->data_args( array(
			'single' => $single,
		) );

		$a['value'] = $a['repeat'] ? array_values( $new_value ) : $new_value;

		/**
		 * Filter whether to override saving of meta value.
		 * Returning a non-null value will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 *
		 * @param null|bool $check  Whether to allow updating metadata for the given type.
		 *
		 * @param array $args {
		 *     Array of data about current field including:
		 *
		 *     @type string $value    The value to set
		 *     @type string $type     The current object type
		 *     @type int    $id       The current object ID
		 *     @type string $field_id The ID of the field being updated
		 *     @type bool   $repeat   Whether current field is repeatable
		 *     @type bool   $single   Whether current field is a single database row
		 * }
		 *
		 * @param array $field_args All field arguments
		 *
		 * @param CMB2_Field object $field This field object
		 */
		$override = apply_filters( 'cmb2_override_meta_save', null, $a, $this->args(), $this );

		/**
		 * Filter and parameters are documented for 'cmb2_override_meta_save' filter (above).
		 *
		 * The dynamic portion of the hook, $a['field_id'], refers to the current
		 * field id paramater. Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @since 2.0.0
		 */
		$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_save", $override, $a, $this->args(), $this );

		// If override, return that
		if ( null !== $override ) {
			return $override;
		}

		// Options page handling (or temp data store)
		if ( 'options-page' === $a['type'] || empty( $a['id'] ) ) {
			return cmb2_options( $a['id'] )->update( $a['field_id'], $a['value'], false, $a['single'] );
		}

		// Add metadata if not single
		if ( ! $a['single'] ) {
			return add_metadata( $a['type'], $a['id'], $a['field_id'], $a['value'], false );
		}

		// Delete meta if we have an empty array
		if ( is_array( $a['value'] ) && empty( $a['value'] ) ) {
			return delete_metadata( $a['type'], $a['id'], $a['field_id'], $this->value );
		}

		// Update metadata
		return update_metadata( $a['type'], $a['id'], $a['field_id'], $a['value'] );
	}

	/**
	 * Removes/updates metadata/option data
	 *
	 * @since  1.0.1
	 * @param  string $old Old value
	 */
	public function remove_data( $old = '' ) {
		$a = $this->data_args( array(
			'old' => $old,
		) );

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
		 * @param CMB2_Field object $field This field object
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
		 * @param CMB2_Field object $field This field object
		 */
		$override = apply_filters( "cmb2_override_{$a['field_id']}_meta_remove", $override, $a, $this->args(), $this );

		// If no override, remove as usual
		if ( null !== $override ) {
			return $override;
		} // End if().
		// Option page handling.
		elseif ( 'options-page' === $a['type'] || empty( $a['id'] ) ) {
			return cmb2_options( $a['id'] )->remove( $a['field_id'] );
		}

		// Remove metadata
		return delete_metadata( $a['type'], $a['id'], $a['field_id'], $old );
	}

	/**
	 * Data variables for get/set data methods
	 *
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
	 *
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

		$sanitizer = new CMB2_Sanitize( $this, $meta_value );
		$field_type = $this->type();

		/**
		 * Filter the value before it is saved.
		 *
		 * The dynamic portion of the hook name, $field_type, refers to the field type.
		 *
		 * Passing a non-null value to the filter will short-circuit saving
		 * the field value, saving the passed value instead.
		 *
		 * @param bool|mixed $override_value Sanitization/Validation override value to return.
		 *                                   Default: null. false to skip it.
		 * @param mixed      $value      The value to be saved to this field.
		 * @param int        $object_id  The ID of the object where the value will be saved
		 * @param array      $field_args The current field's arguments
		 * @param object     $sanitizer  This `CMB2_Sanitize` object
		 */
		$override_value = apply_filters( "cmb2_sanitize_{$field_type}", null, $sanitizer->value, $this->object_id, $this->args(), $sanitizer );

		if ( null !== $override_value ) {
			return $override_value;
		}

		// Sanitization via 'CMB2_Sanitize'
		return $sanitizer->{$field_type}();
	}

	/**
	 * Process $_POST data to save this field's value
	 *
	 * @since  2.0.3
	 * @param  array $data_to_save $_POST data to check
	 * @return array|int|bool                Result of save, false on failure
	 */
	public function save_field_from_data( array $data_to_save ) {
		$this->data_to_save = $data_to_save;

		$meta_value = isset( $this->data_to_save[ $this->id( true ) ] )
			? $this->data_to_save[ $this->id( true ) ]
			: null;

		return $this->save_field( $meta_value );
	}

	/**
	 * Sanitize/store a value to this field
	 *
	 * @since  2.0.0
	 * @param  array $meta_value Desired value to sanitize/store
	 * @return array|int|bool              Result of save. false on failure
	 */
	public function save_field( $meta_value ) {

		$updated   = false;
		$action    = '';
		$new_value = $this->sanitization_cb( $meta_value );

		if ( ! $this->args( 'save_field' ) ) {

			// Nothing to see here.
			$action = 'disabled';

		} elseif ( $this->args( 'multiple' ) && ! $this->args( 'repeatable' ) && ! $this->group ) {

			$this->remove_data();
			$count = 0;

			if ( ! empty( $new_value ) ) {
				foreach ( $new_value as $add_new ) {
					if ( $this->update_data( $add_new, false ) ) {
						$count++;
					}
				}
			}

			$updated = $count ? $count : false;
			$action  = 'repeatable';

		} elseif ( ! CMB2_Utils::isempty( $new_value ) && $new_value !== $this->get_data() ) {
			$updated = $this->update_data( $new_value );
			$action  = 'updated';
		} elseif ( CMB2_Utils::isempty( $new_value ) ) {
			$updated = $this->remove_data();
			$action  = 'removed';
		}

		if ( $updated ) {
			$this->value = $this->get_data();
			$this->escaped_value = null;
		}

		$field_id = $this->id( true );

		/**
		 * Hooks after save field action.
		 *
		 * @since 2.2.0
		 *
		 * @param string            $field_id the current field id paramater.
		 * @param bool              $updated  Whether the metadata update action occurred.
		 * @param string            $action   Action performed. Could be "repeatable", "updated", or "removed".
		 * @param CMB2_Field object $field    This field object
		 */
		do_action( 'cmb2_save_field', $field_id, $updated, $action, $this );

		/**
		 * Hooks after save field action.
		 *
		 * The dynamic portion of the hook, $field_id, refers to the
		 * current field id paramater.
		 *
		 * @since 2.2.0
		 *
		 * @param bool              $updated Whether the metadata update action occurred.
		 * @param string            $action  Action performed. Could be "repeatable", "updated", or "removed".
		 * @param CMB2_Field object $field   This field object
		 */
		do_action( "cmb2_save_field_{$field_id}", $updated, $action, $this );

		return $updated;
	}

	/**
	 * Determine if current type is exempt from escaping
	 *
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
	 *
	 * @since  1.1.0
	 * @param  string $type Field type to check
	 * @return bool         True if type cannot be repeatable
	 */
	public function repeatable_exception( $type ) {
		// These types cannot be repeatable.
		$internal_fields = array(
			// Use file_list instead
			'file'                             => 1,
			'radio'                            => 1,
			'title'                            => 1,
			'wysiwyg'                          => 1,
			'checkbox'                         => 1,
			'radio_inline'                     => 1,
			'taxonomy_radio'                   => 1,
			'taxonomy_radio_inline'            => 1,
			'taxonomy_radio_hierarchical'      => 1,
			'taxonomy_select'                  => 1,
			'taxonomy_multicheck'              => 1,
			'taxonomy_multicheck_inline'       => 1,
			'taxonomy_multicheck_hierarchical' => 1,

		);

		/**
		 * Filter field types that are non-repeatable.
		 *
		 * Note that this does *not* allow overriding the default non-repeatable types.
		 *
		 * @since 2.1.1
		 *
		 * @param array $fields Array of fields designated as non-repeatable. Note that the field names are *keys*,
		 *                      and not values. The value can be anything, because it is meaningless. Example:
		 *                      array( 'my_custom_field' => 1 )
		 */
		$all_fields = array_merge( apply_filters( 'cmb2_non_repeatable_fields', array() ), $internal_fields );
		return isset( $all_fields[ $type ] );
	}

	/**
	 * Determine if current type has its own defaults field-arguments method.
	 *
	 * @since  2.2.6
	 * @param  string $type Field type to check
	 * @return bool         True if has own method.
	 */
	public function has_args_method( $type ) {

		// These types have their own arguments parser.
		$type_methods = array(
			'group'   => 'set_field_defaults_group',
			'wysiwyg' => 'set_field_defaults_wysiwyg',
		);

		if ( isset( $type_methods[ $type ] ) ) {
			return $type_methods[ $type ];
		}

		$all_or_nothing_types = array_flip( apply_filters( 'cmb2_all_or_nothing_types', array(
			'select',
			'radio',
			'radio_inline',
			'taxonomy_select',
			'taxonomy_radio',
			'taxonomy_radio_inline',
			'taxonomy_radio_hierarchical',
		), $this ) );

		if ( isset( $all_or_nothing_types[ $type ] ) ) {
			return 'set_field_defaults_all_or_nothing_types';
		}

		return false;
	}

	/**
	 * Escape the value before output. Defaults to 'esc_attr()'
	 *
	 * @since  1.0.1
	 * @param  callable $func       Escaping function (if not esc_attr())
	 * @param  mixed    $meta_value Meta value
	 * @return mixed                Final value
	 */
	public function escaped_value( $func = 'esc_attr', $meta_value = '' ) {

		if ( null !== $this->escaped_value ) {
			return $this->escaped_value;
		}

		$meta_value = $meta_value ? $meta_value : $this->value();

		// Check if the field has a registered escaping callback
		if ( $cb = $this->maybe_callback( 'escape_cb' ) ) {
			// Ok, callback is good, let's run it.
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		$field_type = $this->type();

		/**
		 * Filter the value for escaping before it is ouput.
		 *
		 * The dynamic portion of the hook name, $field_type, refers to the field type.
		 *
		 * Passing a non-null value to the filter will short-circuit the built-in
		 * escaping for this field.
		 *
		 * @param bool|mixed $override_value Escaping override value to return.
		 *                                   Default: null. false to skip it.
		 * @param mixed      $meta_value The value to be output.
		 * @param array      $field_args The current field's arguments.
		 * @param object     $field      This `CMB2_Field` object.
		 */
		$esc = apply_filters( "cmb2_types_esc_{$field_type}", null, $meta_value, $this->args(), $this );
		if ( null !== $esc ) {
			return $esc;
		}

		if ( false === $cb || $this->escaping_exception() ) {
			// If requesting NO escaping, return meta value
			return $this->val_or_default( $meta_value );
		}

		// escaping function passed in?
		$func       = $func ? $func : 'esc_attr';
		$meta_value = $this->val_or_default( $meta_value );

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
	 * Return non-empty value or field default if value IS empty
	 *
	 * @since  2.0.0
	 * @param  mixed $meta_value Field value
	 * @return mixed             Field value, or default value
	 */
	public function val_or_default( $meta_value ) {
		return ! CMB2_Utils::isempty( $meta_value ) ? $meta_value : $this->get_default();
	}

	/**
	 * Offset a time value based on timezone
	 *
	 * @since  1.0.0
	 * @return string Offset time string
	 */
	public function field_timezone_offset() {
		return CMB2_Utils::timezone_offset( $this->field_timezone() );
	}

	/**
	 * Return timezone string
	 *
	 * @since  1.0.0
	 * @return string Timezone string
	 */
	public function field_timezone() {
		$value = '';

		// Is timezone arg set?
		if ( $this->args( 'timezone' ) ) {
			$value = $this->args( 'timezone' );
		} // End if().
		// Is there another meta key with a timezone stored as its value we should use?
		elseif ( $this->args( 'timezone_meta_key' ) ) {
			$value = $this->get_data( $this->args( 'timezone_meta_key' ) );
		}

		return $value;
	}

	/**
	 * Format the timestamp field value based on the field date/time format arg
	 *
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
	 *
	 * @since  2.0.0
	 * @param  string $format     Either date_format or time_format
	 * @param  string $meta_value Optional meta value to check
	 * @return string             Formatted date
	 */
	public function get_timestamp_format( $format = 'date_format', $meta_value = 0 ) {
		$meta_value = $meta_value ? $meta_value : $this->escaped_value();
		$meta_value = CMB2_Utils::make_valid_time_stamp( $meta_value );

		if ( empty( $meta_value ) ) {
			return '';
		}

		return is_array( $meta_value )
			? array_map( array( $this, 'format_timestamp' ), $meta_value, $format )
			: $this->format_timestamp( $meta_value, $format );
	}

	/**
	 * Get timestamp from text date
	 *
	 * @since  2.2.0
	 * @param  string $value Date value
	 * @return mixed         Unix timestamp representing the date.
	 */
	public function get_timestamp_from_value( $value ) {
		return CMB2_Utils::get_timestamp_from_value( $value, $this->args( 'date_format' ) );
	}

	/**
	 * Get field render callback and Render the field row
	 *
	 * @since 1.0.0
	 */
	public function render_field() {
		$this->render_context = 'edit';

		$this->peform_param_callback( 'render_row_cb' );

		// For chaining
		return $this;
	}

	/**
	 * Default field render callback
	 *
	 * @since 2.1.1
	 */
	public function render_field_callback() {

		// If field is requesting to not be shown on the front-end
		if ( ! is_admin() && ! $this->args( 'on_front' ) ) {
			return;
		}

		// If field is requesting to be conditionally shown
		if ( ! $this->should_show() ) {
			return;
		}

		$this->peform_param_callback( 'before_row' );

		printf( "<div class=\"cmb-row %s\" data-fieldtype=\"%s\">\n", $this->row_classes(), $this->type() );

		if ( ! $this->args( 'show_names' ) ) {
			echo "\n\t<div class=\"cmb-td\">\n";

			$this->peform_param_callback( 'label_cb' );

		} else {

			if ( $this->get_param_callback_result( 'label_cb' ) ) {
				echo '<div class="cmb-th">', $this->peform_param_callback( 'label_cb' ), '</div>';
			}

			echo "\n\t<div class=\"cmb-td\">\n";
		}

		$this->peform_param_callback( 'before' );

		$types = new CMB2_Types( $this );
		$types->render();

		$this->peform_param_callback( 'after' );

		echo "\n\t</div>\n</div>";

		$this->peform_param_callback( 'after_row' );

		// For chaining
		return $this;
	}

	/**
	 * The default label_cb callback (if not a title field)
	 *
	 * @since  2.1.1
	 * @return string Label html markup
	 */
	public function label() {
		if ( ! $this->args( 'name' ) ) {
			return '';
		}

		$style = ! $this->args( 'show_names' ) ? ' style="display:none;"' : '';

		return sprintf( "\n" . '<label%1$s for="%2$s">%3$s</label>' . "\n", $style, $this->id(), $this->args( 'name' ) );
	}

	/**
	 * Defines the classes for the current CMB2 field row
	 *
	 * @since  2.0.0
	 * @return string Space concatenated list of classes
	 */
	public function row_classes() {

		$classes = array();

		/**
		 * By default, 'text_url' and 'text' fields get table-like styling
		 *
		 * @since 2.0.0
		 *
		 * @param array $field_types The types of fields which should get the 'table-layout' class
		 */
		$repeat_table_rows_types = apply_filters( 'cmb2_repeat_table_row_types', array(
			'text_url',
			'text',
		) );

		$conditional_classes = array(
			'cmb-type-' . str_replace( '_', '-', sanitize_html_class( $this->type() ) ) => true,
			'cmb2-id-' . str_replace( '_', '-', sanitize_html_class( $this->id() ) )    => true,
			'cmb-repeat'             => $this->args( 'repeatable' ),
			'cmb-repeat-group-field' => $this->group,
			'cmb-inline'             => $this->args( 'inline' ),
			'table-layout'           => 'edit' === $this->render_context && in_array( $this->type(), $repeat_table_rows_types ),
		);

		foreach ( $conditional_classes as $class => $condition ) {
			if ( $condition ) {
				$classes[] = $class;
			}
		}

		if ( $added_classes = $this->args( 'classes' ) ) {
			$added_classes = is_array( $added_classes ) ? implode( ' ', $added_classes ) : (string) $added_classes;
		} elseif ( $added_classes = $this->get_param_callback_result( 'classes_cb' ) ) {
			$added_classes = is_array( $added_classes ) ? implode( ' ', $added_classes ) : (string) $added_classes;
		}

		if ( $added_classes ) {
			$classes[] = esc_attr( $added_classes );
		}

		/**
		 * Globally filter row classes
		 *
		 * @since 2.0.0
		 *
		 * @param string            $classes Space-separated list of row classes
		 * @param CMB2_Field object $field   This field object
		 */
		return apply_filters( 'cmb2_row_classes', implode( ' ', $classes ), $this );
	}

	/**
	 * Get field display callback and render the display value in the column.
	 *
	 * @since 2.2.2
	 */
	public function render_column() {
		$this->render_context = 'display';

		$this->peform_param_callback( 'display_cb' );

		// For chaining
		return $this;
	}

	/**
	 * The method to fetch the value for this field for the REST API.
	 *
	 * @since 2.5.0
	 */
	public function get_rest_value() {
		$field_type = $this->type();
		$field_id   = $this->id( true );

		if ( $cb = $this->maybe_callback( 'rest_value_cb' ) ) {
			apply_filters( "cmb2_get_rest_value_for_{$field_id}", $cb, 99 );
		}

		$value = $this->get_data();

		/**
		 * Filter the value before it is sent to the REST request.
		 *
		 * @since 2.5.0
		 *
		 * @param mixed      $value The value from CMB2_Field::get_data()
		 * @param CMB2_Field $field This field object.
		 */
		$value = apply_filters( 'cmb2_get_rest_value', $value, $this );

		/**
		 * Filter the value before it is sent to the REST request.
		 *
		 * The dynamic portion of the hook name, $field_type, refers to the field type.
		 *
		 * @since 2.5.0
		 *
		 * @param mixed      $value The value from CMB2_Field::get_data()
		 * @param CMB2_Field $field This field object.
		 */
		$value = apply_filters( "cmb2_get_rest_value_{$field_type}", $value, $this );

		/**
		 * Filter the value before it is sent to the REST request.
		 *
		 * The dynamic portion of the hook name, $field_id, refers to the field id.
		 *
		 * @since 2.5.0
		 *
		 * @param mixed      $value The value from CMB2_Field::get_data()
		 * @param CMB2_Field $field This field object.
		 */
		return apply_filters( "cmb2_get_rest_value_for_{$field_id}", $value, $this );
	}

	/**
	 * Default callback to outputs field value in a display format.
	 *
	 * @since 2.2.2
	 */
	public function display_value_callback() {
		// If field is requesting to be conditionally shown
		if ( ! $this->should_show() ) {
			return;
		}

		$display = new CMB2_Field_Display( $this );
		$field_type = $this->type();

		/**
		 * A filter to bypass the default display.
		 *
		 * The dynamic portion of the hook name, $field_type, refers to the field type.
		 *
		 * Passing a non-null value to the filter will short-circuit the default display.
		 *
		 * @param bool|mixed         $pre_output Default null value.
		 * @param CMB2_Field         $field      This field object.
		 * @param CMB2_Field_Display $display    The `CMB2_Field_Display` object.
		 */
		$pre_output = apply_filters( "cmb2_pre_field_display_{$field_type}", null, $this, $display );

		if ( null !== $pre_output ) {
			echo $pre_output;
			return;
		}

		$this->peform_param_callback( 'before_display_wrap' );

		printf( "<div class=\"cmb-column %s\" data-fieldtype=\"%s\">\n", $this->row_classes(), $field_type );

		$this->peform_param_callback( 'before_display' );

		CMB2_Field_Display::get( $this )->display();

		$this->peform_param_callback( 'after_display' );

		echo "\n</div>";

		$this->peform_param_callback( 'after_display_wrap' );

		// For chaining
		return $this;
	}

	/**
	 * Replaces a hash key - {#} - with the repeatable index
	 *
	 * @since  1.2.0
	 * @param  string $value Value to update
	 * @return string        Updated value
	 */
	public function replace_hash( $value ) {
		// Replace hash with 1 based count
		return str_replace( '{#}', ( $this->index + 1 ), $value );
	}

	/**
	 * Retrieve text parameter from field's text array (if it has one), or use fallback text
	 * For back-compatibility, falls back to checking the options array.
	 *
	 * @since  2.2.2
	 * @param  string $text_key Key in field's text array
	 * @param  string $fallback Fallback text
	 * @return string            Text
	 */
	public function get_string( $text_key, $fallback ) {
		// If null, populate with our field strings values.
		if ( null === $this->strings ) {
			$this->strings = (array) $this->args['text'];

			if ( is_callable( $this->args['text_cb'] ) ) {
				$strings = call_user_func( $this->args['text_cb'], $this );

				if ( $strings && is_array( $strings ) ) {
					$this->strings += $strings;
				}
			}
		}

		// If we have that string value, send it back.
		if ( isset( $this->strings[ $text_key ] ) ) {
			return $this->strings[ $text_key ];
		}

		// Check options for back-compat.
		$string = $this->options( $text_key );

		return $string ? $string : $fallback;
	}

	/**
	 * Retrieve options args.
	 *
	 * @since  2.0.0
	 * @param  string $key Specific option to retrieve
	 * @return array|mixed Array of options or specific option.
	 */
	public function options( $key = '' ) {
		if ( empty( $this->field_options ) ) {
			$this->set_options();
		}

		if ( $key ) {
			return array_key_exists( $key, $this->field_options ) ? $this->field_options[ $key ] : false;
		}

		return $this->field_options;
	}

	/**
	 * Generates/sets options args. Calls options_cb if it exists.
	 *
	 * @since  2.2.5
	 *
	 * @return array Array of options
	 */
	public function set_options() {
		$this->field_options = (array) $this->args['options'];

		if ( is_callable( $this->args['options_cb'] ) ) {
			$options = call_user_func( $this->args['options_cb'], $this );

			if ( $options && is_array( $options ) ) {
				$this->field_options = $options + $this->field_options;
			}
		}

		return $this->field_options;
	}

	/**
	 * Store JS dependencies as part of the field args.
	 *
	 * @since 2.2.0
	 * @param array $dependencies Dependies to register for this field.
	 */
	public function add_js_dependencies( $dependencies = array() ) {
		foreach ( (array) $dependencies as $dependency ) {
			$this->args['js_dependencies'][ $dependency ] = $dependency;
		}

		CMB2_JS::add_dependencies( $dependencies );
	}

	/**
	 * Send field data to JS.
	 *
	 * @since 2.2.0
	 */
	public function register_js_data() {
		if ( $this->group ) {
			CMB2_JS::add_field_data( $this->group );
		}

		return CMB2_JS::add_field_data( $this );
	}

	/**
	 * Get an array of some of the field data to be used in the Javascript.
	 *
	 * @since  2.2.4
	 *
	 * @return array
	 */
	public function js_data() {
		return array(
			'label'     => $this->args( 'name' ),
			'id'        => $this->id( true ),
			'type'      => $this->type(),
			'hash'      => $this->hash_id(),
			'box'       => $this->cmb_id,
			'id_attr'   => $this->id(),
			'name_attr' => $this->args( '_name' ),
			'default'   => $this->get_default(),
			'group'     => $this->group_id(),
			'index'     => $this->group ? $this->group->index : null,
		);
	}

	/**
	 * Returns a unique hash representing this field.
	 *
	 * @since  2.2.4
	 *
	 * @return string
	 */
	public function hash_id() {
		if ( '' === $this->hash_id ) {
			$this->hash_id = CMB2_Utils::generate_hash( $this->cmb_id . '||' . $this->id() );
		}

		return $this->hash_id;
	}

	/**
	 * Gets the id of the group field if this field is part of a group.
	 *
	 * @since  2.2.4
	 *
	 * @return string
	 */
	public function group_id() {
		return $this->group ? $this->group->id( true ) : '';
	}

	/**
	 * Get CMB2_Field default value, either from default param or default_cb param.
	 *
	 * @since  0.2.2
	 *
	 * @return mixed  Default field value
	 */
	public function get_default() {
		$default = $this->args['default'];

		if ( null !== $default ) {
			return apply_filters( 'cmb2_default_filter', $default, $this );
		}

		$param = is_callable( $this->args['default_cb'] ) ? 'default_cb' : 'default';
		$default = $this->args['default'] = $this->get_param_callback_result( $param );

		// Allow a filter override of the default value.
		return apply_filters( 'cmb2_default_filter', $this->args['default'], $this );
	}

	/**
	 * Fills in empty field parameters with defaults
	 *
	 * @since 1.1.0
	 *
	 * @param  array  $args Field config array.
	 * @return array        Modified field config array.
	 */
	public function _set_field_defaults( $args ) {

		// Set up blank or default values for empty ones
		$args = wp_parse_args( $args, $this->get_default_field_args( $args ) );

		/*
		 * Deprecated usage:
		 *
		 * 'std' -- use 'default' (no longer works)
		 * 'row_classes' -- use 'class', or 'class_cb'
		 * 'default' -- as callback (use default_cb)
		 */
		$args = $this->convert_deprecated_params( $args );

		$args['repeatable'] = $args['repeatable'] && ! $this->repeatable_exception( $args['type'] );
		$args['inline']     = $args['inline'] || false !== stripos( $args['type'], '_inline' );
		$args['_id']        = $args['id'];
		$args['_name']      = $args['id'];

		if ( $method = $this->has_args_method( $args['type'] ) ) {
			$args = $this->{$method}( $args );
		}

		if ( $this->group ) {
			$args = $this->set_group_sub_field_defaults( $args );
		}

		$args['has_supporting_data'] = in_array(
			$args['type'],
			array(
				// CMB2_Sanitize::_save_file_id_value()/CMB2_Sanitize::_get_group_file_value_array()
				'file',
				// See CMB2_Sanitize::_save_utc_value()
				'text_datetime_timestamp_timezone',
			),
			true
		);

		return $args;
	}

	/**
	 * Sets default arguments for the group field types.
	 *
	 * @since 2.2.6
	 *
	 * @param  array  $args Field config array.
	 * @return array        Modified field config array.
	 */
	protected function set_field_defaults_group( $args ) {
		$args['options'] = wp_parse_args( $args['options'], array(
			'add_button'    => esc_html__( 'Add Group', 'cmb2' ),
			'remove_button' => esc_html__( 'Remove Group', 'cmb2' ),
		) );

		return $args;
	}

	/**
	 * Sets default arguments for the wysiwyg field types.
	 *
	 * @since 2.2.6
	 *
	 * @param  array  $args Field config array.
	 * @return array        Modified field config array.
	 */
	protected function set_field_defaults_wysiwyg( $args ) {
		$args['id'] = strtolower( str_ireplace( '-', '_', $args['id'] ) );
		$args['options']['textarea_name'] = $args['_name'];

		return $args;
	}

	/**
	 * Sets default arguments for the all-or-nothing field types.
	 *
	 * @since 2.2.6
	 *
	 * @param  array  $args Field config array.
	 * @return array        Modified field config array.
	 */
	protected function set_field_defaults_all_or_nothing_types( $args ) {
		$args['show_option_none'] = isset( $args['show_option_none'] ) ? $args['show_option_none'] : null;
		$args['show_option_none'] = true === $args['show_option_none'] ? esc_html__( 'None', 'cmb2' ) : $args['show_option_none'];

		if ( null === $args['show_option_none'] ) {
			$off_by_default = in_array( $args['type'], array( 'select', 'radio', 'radio_inline' ), true );
			$args['show_option_none'] = $off_by_default ? false : esc_html__( 'None', 'cmb2' );
		}

		return $args;
	}

	/**
	 * Sets default arguments for group sub-fields.
	 *
	 * @since 2.2.6
	 *
	 * @param  array  $args Field config array.
	 * @return array        Modified field config array.
	 */
	protected function set_group_sub_field_defaults( $args ) {
		$args['id']    = $this->group->args( 'id' ) . '_' . $this->group->index . '_' . $args['id'];
		$args['_name'] = $this->group->args( 'id' ) . '[' . $this->group->index . '][' . $args['_name'] . ']';

		return $args;
	}

	/**
	 * Gets the default arguments for all fields.
	 *
	 * @since 2.2.6
	 *
	 * @param  array  $args Field config array.
	 * @return array        Field defaults.
	 */
	protected function get_default_field_args( $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : '';

		return array(
			'type'              => $type,
			'name'              => '',
			'desc'              => '',
			'before'            => '',
			'after'             => '',
			'options'           => array(),
			'options_cb'        => '',
			'text'              => array(),
			'text_cb'           => '',
			'attributes'        => array(),
			'protocols'         => null,
			'default'           => null,
			'default_cb'        => '',
			'classes'           => null,
			'classes_cb'        => '',
			'select_all_button' => true,
			'multiple'          => false,
			'repeatable'        => 'group' === $type,
			'inline'            => false,
			'on_front'          => true,
			'show_names'        => true,
			'save_field'        => true, // Will not save if false
			'date_format'       => 'm\/d\/Y',
			'time_format'       => 'h:i A',
			'description'       => isset( $args['desc'] ) ? $args['desc'] : '',
			'preview_size'      => 'file' === $type ? array( 350, 350 ) : array( 50, 50 ),
			'render_row_cb'     => array( $this, 'render_field_callback' ),
			'display_cb'        => array( $this, 'display_value_callback' ),
			'label_cb'          => 'title' !== $type ? array( $this, 'label' ) : '',
			'column'            => false,
			'js_dependencies'   => array(),
			'show_in_rest'      => null,
		);
	}

	/**
	 * Get default field arguments specific to this CMB2 object.
	 *
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent)
	 * @return array                   Array of field arguments.
	 */
	protected function get_default_args( $field_args, $field_group = null ) {
		$args = parent::get_default_args( array(), $this->group );

		if ( isset( $field_args['field_args'] ) ) {
			$args = wp_parse_args( $field_args, $args );
		} else {
			$args['field_args'] = wp_parse_args( $field_args, $this->args );
		}

		return $args;
	}

	/**
	 * Returns a cloned version of this field object, but with
	 * modified/overridden field arguments.
	 *
	 * @since  2.2.2
	 * @param  array $field_args Array of field arguments, or entire array of
	 *                           arguments for CMB2_Field
	 *
	 * @return CMB2_Field         The new CMB2_Field instance.
	 */
	public function get_field_clone( $field_args ) {
		return $this->get_new_field( $field_args );
	}

	/**
	 * Returns the CMB2 instance this field is registered to.
	 *
	 * @since  2.2.2
	 *
	 * @return CMB2|WP_Error If new CMB2_Field is called without cmb_id arg, returns error.
	 */
	public function get_cmb() {
		if ( ! $this->cmb_id ) {
			return new WP_Error( 'no_cmb_id', esc_html__( 'Sorry, this field does not have a cmb_id specified.', 'cmb2' ) );
		}

		return cmb2_get_metabox( $this->cmb_id, $this->object_id, $this->object_type );
	}

	/**
	 * Converts deprecated field parameters to the current/proper parameter, and throws a deprecation notice.
	 *
	 * @since  2.2.3
	 * @param  array $args Metabox field config array.
	 * @return array       Modified field config array.
	 */
	protected function convert_deprecated_params( $args ) {

		if ( isset( $args['row_classes'] ) ) {

			// We'll let this one be.
			// $this->deprecated_param( __CLASS__ . '::__construct()', '2.2.3', self::DEPRECATED_PARAM, 'row_classes', 'classes' );
			// row_classes param could be a callback. This is definitely deprecated.
			if ( is_callable( $args['row_classes'] ) ) {

				$this->deprecated_param( __CLASS__ . '::__construct()', '2.2.3', self::DEPRECATED_CB_PARAM, 'row_classes', 'classes_cb' );

				$args['classes_cb'] = $args['row_classes'];
				$args['classes'] = null;
			} else {

				$args['classes'] = $args['row_classes'];
			}

			unset( $args['row_classes'] );
		}

		// default param can be passed a callback as well
		if ( is_callable( $args['default'] ) ) {

			$this->deprecated_param( __CLASS__ . '::__construct()', '2.2.3', self::DEPRECATED_CB_PARAM, 'default', 'default_cb' );

			$args['default_cb'] = $args['default'];
			$args['default'] = null;
		}

		// options param can be passed a callback as well
		if ( is_callable( $args['options'] ) ) {

			$this->deprecated_param( __CLASS__ . '::__construct()', '2.2.3', self::DEPRECATED_CB_PARAM, 'options', 'options_cb' );

			$args['options_cb'] = $args['options'];
			$args['options'] = array();
		}

		return $args;
	}

}
