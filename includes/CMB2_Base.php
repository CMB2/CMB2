<?php
/**
 * CMB2 Base - Base object functionality.
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
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
	 * @var   string
	 * @since 2.2.3
	 */
	protected $cmb_id = '';

	/**
	 * The deprecated object properties name.
	 * @var   string
	 * @since 2.2.3
	 */
	protected $properties_name = 'meta_box';

	/**
	 * Object ID
	 * @var   mixed
	 * @since 2.2.3
	 */
	protected $object_id = 0;

	/**
	 * Type of object being handled. (e.g., post, user, comment, or term)
	 * @var   string
	 * @since 2.2.3
	 */
	protected $object_type = 'post';

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 * @var   array
	 * @since 2.2.3
	 */
	public $data_to_save = array();

	/**
	 * Array of field param callback results
	 * @var   array
	 * @since 2.0.0
	 */
	protected $callback_results = array();

	/**
	 * Get started
	 * @since 2.2.3
	 * @param array $args Object properties array
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
	 * @since  2.2.3
	 * @param  integer $object_id Object ID
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
	 * @since  2.2.3
	 * @param  string $object_type Object Type
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
	 * @since  2.2.2
	 * @param  string $property Metabox config property to retrieve
	 * @param  mixed  $value    Value to set if no value found
	 * @return mixed            Metabox config property value or false
	 */
	public function set_prop( $property, $value ) {
		$this->{$this->properties_name}[ $property ] = $value;

		return $this->prop( $property );
	}

	/**
	 * Get object property and optionally set a fallback
	 * @since  2.0.0
	 * @param  string $property Metabox config property to retrieve
	 * @param  mixed  $fallback Fallback value to set if no value found
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
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent)
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
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent)
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

		// Use the callback to determine showing the cmb, if it exists
		if ( is_callable( $this->prop( 'show_on_cb' ) ) ) {
			$show = (bool) call_user_func( $this->prop( 'show_on_cb' ), $this );
		}

		return $show;
	}

	/**
	 * Displays the results of the param callbacks.
	 *
	 * @since 2.0.0
	 * @param string $param Field parameter
	 */
	public function peform_param_callback( $param ) {
		echo $this->get_param_callback_result( $param );
	}

	/**
	 * Store results of the param callbacks for continual access
	 * @since  2.0.0
	 * @param  string $param Field parameter
	 * @return mixed         Results of param/param callback
	 */
	public function get_param_callback_result( $param ) {

		// If we've already retrieved this param's value,
		if ( array_key_exists( $param, $this->callback_results ) ) {

			// send it back
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
	 * Handles the property callbacks, and passes this object as property.
	 * @since  2.2.3
	 * @param  callable $cb The callback method/function/closure
	 * @return mixed        Return of the callback function.
	 */
	protected function do_callback( $cb ) {
		return call_user_func( $cb, $this->{$this->properties_name}, $this );
	}

	/**
	 * Checks if field has a callback value
	 * @since  1.0.1
	 * @param  string $cb Callback string
	 * @return mixed      NULL, false for NO validation, or $cb string if it exists.
	 */
	public function maybe_callback( $cb ) {
		$args = $this->{$this->properties_name};
		if ( ! isset( $args[ $cb ] ) ) {
			return null;
		}

		// Check if requesting explicitly false
		$cb = false !== $args[ $cb ] && 'false' !== $args[ $cb ] ? $args[ $cb ] : false;

		// If requesting NO validation, return false
		if ( ! $cb ) {
			return false;
		}

		if ( is_callable( $cb ) ) {
			return $cb;
		}

		return null;
	}

	/**
	 * Magic getter for our object.
	 * @param string $field
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
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}
