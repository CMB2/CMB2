<?php
/**
 * CMB2 Base - Base object functionality.
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read $args        The objects array of properties/arguments.
 * @property-read $meta_box    The objects array of properties/arguments.
 * @property-read $properties  The objects array of properties/arguments.
 * @property-read $cmb_id      Current CMB2 instance ID
 * @property-read $object_id   Object ID
 * @property-read $object_type Type of object being handled. (e.g., post, user, comment, or term)
 */
abstract class CMB2_Base {

	/**
	 * Current CMB2 instance ID
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected $cmb_id = '';

	/**
	 * The object properties name.
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected $properties_name = 'meta_box';

	/**
	 * Object ID
	 *
	 * @var   mixed
	 * @since 2.2.3
	 */
	protected $object_id = 0;

	/**
	 * Type of object being handled. (e.g., post, user, comment, or term)
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected $object_type = '';

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 *
	 * @var   array
	 * @since 2.2.3
	 */
	public $data_to_save = array();

	/**
	 * Array of field param callback results
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	protected $callback_results = array();

	/**
	 * The deprecated_param method deprecated param message signature.
	 */
	const DEPRECATED_PARAM = 1;

	/**
	 * The deprecated_param method deprecated callback param message signature.
	 */
	const DEPRECATED_CB_PARAM = 2;

	/**
	 * Get started
	 *
	 * @since 2.2.3
	 * @param array $args Object properties array.
	 */
	public function __construct( $args = array() ) {
		if ( ! empty( $args ) ) {
			foreach ( array(
				'cmb_id',
				'properties_name',
				'object_id',
				'object_type',
				'data_to_save',
			) as $object_prop ) {
				if ( isset( $args[ $object_prop ] ) ) {
					$this->{$object_prop} = $args[ $object_prop ];
				}
			}
		}
	}

	/**
	 * Returns the object ID
	 *
	 * @since  2.2.3
	 * @param  integer $object_id Object ID.
	 * @return integer Object ID
	 */
	public function object_id( $object_id = 0 ) {
		if ( $object_id ) {
			$this->object_id = $object_id;
		}

		return $this->object_id;
	}

	/**
	 * Returns the object type
	 *
	 * @since  2.2.3
	 * @param  string $object_type Object Type.
	 * @return string Object type
	 */
	public function object_type( $object_type = '' ) {
		if ( $object_type ) {
			$this->object_type = $object_type;
		}

		return $this->object_type;
	}

	/**
	 * Get the object type for the current page, based on the $pagenow global.
	 *
	 * @since  2.2.2
	 * @return string  Page object type name.
	 */
	public function current_object_type() {
		global $pagenow;
		$type = 'post';

		if ( in_array( $pagenow, array( 'user-edit.php', 'profile.php', 'user-new.php' ), true ) ) {
			$type = 'user';
		}

		if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php' ), true ) ) {
			$type = 'comment';
		}

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
			$type = 'term';
		}

		return $type;
	}

	/**
	 * Set object property.
	 *
	 * @since  2.2.2
	 * @param  string $property Metabox config property to retrieve.
	 * @param  mixed  $value    Value to set if no value found.
	 * @return mixed            Metabox config property value or false.
	 */
	public function set_prop( $property, $value ) {
		$this->{$this->properties_name}[ $property ] = $value;

		return $this->prop( $property );
	}

	/**
	 * Get object property and optionally set a fallback
	 *
	 * @since  2.0.0
	 * @param  string $property Metabox config property to retrieve.
	 * @param  mixed  $fallback Fallback value to set if no value found.
	 * @return mixed            Metabox config property value or false
	 */
	public function prop( $property, $fallback = null ) {
		if ( array_key_exists( $property, $this->{$this->properties_name} ) ) {
			return $this->{$this->properties_name}[ $property ];
		} elseif ( $fallback ) {
			return $this->{$this->properties_name}[ $property ] = $fallback;
		}
	}

	/**
	 * Get default field arguments specific to this CMB2 object.
	 *
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent).
	 * @return array                   Array of field arguments.
	 */
	protected function get_default_args( $field_args, $field_group = null ) {
		if ( $field_group ) {
			$args = array(
				'field_args'  => $field_args,
				'group_field' => $field_group,
			);
		} else {
			$args = array(
				'field_args'  => $field_args,
				'object_type' => $this->object_type(),
				'object_id'   => $this->object_id(),
				'cmb_id'      => $this->cmb_id,
			);
		}

		return $args;
	}

	/**
	 * Get a new field object specific to this CMB2 object.
	 *
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent).
	 * @return CMB2_Field CMB2_Field object
	 */
	protected function get_new_field( $field_args, $field_group = null ) {
		return new CMB2_Field( $this->get_default_args( $field_args, $field_group ) );
	}

	/**
	 * Determine whether this cmb object should show, based on the 'show_on_cb' callback.
	 *
	 * @since 2.0.9
	 *
	 * @return bool Whether this cmb should be shown.
	 */
	public function should_show() {
		// Default to showing this cmb
		$show = true;

		// Use the callback to determine showing the cmb, if it exists.
		if ( is_callable( $this->prop( 'show_on_cb' ) ) ) {
			$show = (bool) call_user_func( $this->prop( 'show_on_cb' ), $this );
		}

		return $show;
	}

	/**
	 * Displays the results of the param callbacks.
	 *
	 * @since 2.0.0
	 * @param string $param Field parameter.
	 */
	public function peform_param_callback( $param ) {
		echo $this->get_param_callback_result( $param );
	}

	/**
	 * Store results of the param callbacks for continual access
	 *
	 * @since  2.0.0
	 * @param  string $param Field parameter.
	 * @return mixed         Results of param/param callback
	 */
	public function get_param_callback_result( $param ) {

		// If we've already retrieved this param's value.
		if ( array_key_exists( $param, $this->callback_results ) ) {

			// Send it back.
			return $this->callback_results[ $param ];
		}

		// Check if parameter has registered a callback.
		if ( $cb = $this->maybe_callback( $param ) ) {

			// Ok, callback is good, let's run it and store the result.
			ob_start();
			$returned = $this->do_callback( $cb );

			// Grab the result from the output buffer and store it.
			$echoed = ob_get_clean();

			// This checks if the user returned or echoed their callback.
			// Defaults to using the echoed value.
			$this->callback_results[ $param ] = $echoed ? $echoed : $returned;

		} else {

			// Otherwise just get whatever is there.
			$this->callback_results[ $param ] = isset( $this->{$this->properties_name}[ $param ] ) ? $this->{$this->properties_name}[ $param ] : false;
		}

		return $this->callback_results[ $param ];
	}

	/**
	 * Unset the cached results of the param callback.
	 *
	 * @since  2.2.6
	 * @param  string $param Field parameter.
	 * @return CMB2_Base
	 */
	public function unset_param_callback_cache( $param ) {
		if ( isset( $this->callback_results[ $param ] ) ) {
			unset( $this->callback_results[ $param ] );
		}

		return $this;
	}

	/**
	 * Handles the parameter callbacks, and passes this object as parameter.
	 *
	 * @since  2.2.3
	 * @param  callable $cb                The callback method/function/closure.
	 * @param  mixed    $additional_params Any additoinal parameters which should be passed to the callback.
	 * @return mixed                       Return of the callback function.
	 */
	protected function do_callback( $cb, $additional_params = null ) {
		return call_user_func( $cb, $this->{$this->properties_name}, $this, $additional_params );
	}

	/**
	 * Checks if field has a callback value
	 *
	 * @since  1.0.1
	 * @param  string $cb Callback string.
	 * @return mixed      NULL, false for NO validation, or $cb string if it exists.
	 */
	public function maybe_callback( $cb ) {
		$args = $this->{$this->properties_name};
		if ( ! isset( $args[ $cb ] ) ) {
			return null;
		}

		// Check if requesting explicitly false.
		$cb = false !== $args[ $cb ] && 'false' !== $args[ $cb ] ? $args[ $cb ] : false;

		// If requesting NO validation, return false.
		if ( ! $cb ) {
			return false;
		}

		if ( is_callable( $cb ) ) {
			return $cb;
		}

		return null;
	}

	/**
	 * Checks if this object has parameter corresponding to the given filter
	 * which is callable. If so, it registers the callback, and if not,
	 * converts the maybe-modified $val to a boolean for return.
	 *
	 * The registered handlers will have a parameter name which matches the filter, except:
	 * - The 'cmb2_api' prefix will be removed
	 * - A '_cb' suffix will be added (to stay inline with other '*_cb' parameters).
	 *
	 * @since  2.2.3
	 *
	 * @param  string $hook_name     The hook name.
	 * @param  bool   $val           The default value.
	 * @param  string $hook_function The hook function. Default: 'add_filter'.
	 *
	 * @return null|bool             Null if hook is registered, or bool for value.
	 */
	public function maybe_hook_parameter( $hook_name, $val = null, $hook_function = 'add_filter' ) {

		// Remove filter prefix, add param suffix.
		$parameter = substr( $hook_name, strlen( 'cmb2_api_' ) ) . '_cb';

		return self::maybe_hook(
			$this->prop( $parameter, $val ),
			$hook_name,
			$hook_function
		);
	}

	/**
	 * Checks if given value is callable, and registers the callback.
	 * If is non-callable, converts the $val to a boolean for return.
	 *
	 * @since  2.2.3
	 *
	 * @param  bool   $val           The default value.
	 * @param  string $hook_name     The hook name.
	 * @param  string $hook_function The hook function.
	 *
	 * @return null|bool         Null if hook is registered, or bool for value.
	 */
	public static function maybe_hook( $val, $hook_name, $hook_function ) {
		if ( is_callable( $val ) ) {
			call_user_func( $hook_function, $hook_name, $val, 10, 2 );
			return null;
		}

		// Cast to bool.
		return ! ! $val;
	}

	/**
	 * Mark a param as deprecated and inform when it has been used.
	 *
	 * There is a default WordPress hook deprecated_argument_run that will be called
	 * that can be used to get the backtrace up to what file and function used the
	 * deprecated argument.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.2.3
	 *
	 * @param string $function The function that was called.
	 * @param string $version  The version of CMB2 that deprecated the argument used.
	 * @param string $message  Optional. A message regarding the change, or numeric
	 *                         key to generate message from additional arguments.
	 *                         Default null.
	 */
	protected function deprecated_param( $function, $version, $message = null ) {

		if ( is_numeric( $message ) ) {
			$args = func_get_args();

			switch ( $message ) {

				case self::DEPRECATED_PARAM:
					$message = sprintf( __( 'The "%1$s" field parameter has been deprecated in favor of the "%2$s" parameter.', 'cmb2' ), $args[3], $args[4] );
					break;

				case self::DEPRECATED_CB_PARAM:
					$message = sprintf( __( 'Using the "%1$s" field parameter as a callback has been deprecated in favor of the "%2$s" parameter.', 'cmb2' ), $args[3], $args[4] );
					break;

				default:
					$message = null;
					break;
			}
		}

		/**
		 * Fires when a deprecated argument is called. This is a WP core action.
		 *
		 * @since 2.2.3
		 *
		 * @param string $function The function that was called.
		 * @param string $message  A message regarding the change.
		 * @param string $version  The version of CMB2 that deprecated the argument used.
		 */
		do_action( 'deprecated_argument_run', $function, $message, $version );

		/**
		 * Filters whether to trigger an error for deprecated arguments. This is a WP core filter.
		 *
		 * @since 2.2.3
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated arguments. Default true.
		 */
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && apply_filters( 'deprecated_argument_trigger_error', true ) ) {
			if ( function_exists( '__' ) ) {
				if ( ! is_null( $message ) ) {
					trigger_error( sprintf( __( '%1$s was called with a parameter that is <strong>deprecated</strong> since version %2$s! %3$s', 'cmb2' ), $function, $version, $message ) );
				} else {
					trigger_error( sprintf( __( '%1$s was called with a parameter that is <strong>deprecated</strong> since version %2$s with no alternative available.', 'cmb2' ), $function, $version ) );
				}
			} else {
				if ( ! is_null( $message ) ) {
					trigger_error( sprintf( '%1$s was called with a parameter that is <strong>deprecated</strong> since version %2$s! %3$s', $function, $version, $message ) );
				} else {
					trigger_error( sprintf( '%1$s was called with a parameter that is <strong>deprecated</strong> since version %2$s with no alternative available.', $function, $version ) );
				}
			}
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field Requested property.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'args':
			case 'meta_box':
				if ( $field === $this->properties_name ) {
					return $this->{$this->properties_name};
				}
			case 'properties':
				return $this->{$this->properties_name};
			case 'cmb_id':
			case 'object_id':
			case 'object_type':
				return $this->{$field};
			default:
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s property: %2$s', 'cmb2' ), __CLASS__, $field ) );
		}
	}

	/**
	 * Allows overloading the object with methods... Whooaaa oooh it's magic, y'knoooow.
	 *
	 * @since 1.0.0
	 * @throws Exception Invalid method exception.
	 *
	 * @param string $method Non-existent method.
	 * @param array  $args   All arguments passed to the method.
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		$object_class = strtolower( get_class( $this ) );

		if ( ! has_filter( "{$object_class}_inherit_{$method}" ) ) {
			throw new Exception( sprintf( esc_html__( 'Invalid %1$s method: %2$s', 'cmb2' ), get_class( $this ), $method ) );
		}

		array_unshift( $args, $this );

		/**
		 * Allows overloading the object (CMB2 or CMB2_Field) with additional capabilities
		 * by registering hook callbacks.
		 *
		 * The first dynamic portion of the hook name, $object_class, refers to the object class,
		 * either cmb2 or cmb2_field.
		 *
		 * The second dynamic portion of the hook name, $method, is the non-existent method being
		 * called on the object. To avoid possible future methods encroaching on your hooks,
		 * use a unique method (aka, $cmb->prefix_my_method()).
		 *
		 * When registering your callback, you will need to ensure that you register the correct
		 * number of `$accepted_args`, accounting for this object instance being the first argument.
		 *
		 * @param array $args The arguments to be passed to the hook.
		 *                    The first argument will always be this object instance.
		 */
		return apply_filters_ref_array( "{$object_class}_inherit_{$method}", $args );
	}
}
