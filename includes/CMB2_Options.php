<?php
/**
 * CMB2 Utility classes for handling multi-dimensional array data for options
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

/**
 * Retrieves an instance of CMB2_Option based on the option key
 *
 * @package   CMB2
 * @author    CMB2 team
 */
class CMB2_Options {
	/**
	 * Array of all CMB2_Option instances
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected static $option_sets = array();

	public static function get( $option_key ) {

		if ( empty( self::$option_sets ) || empty( self::$option_sets[ $option_key ] ) ) {
			self::$option_sets[ $option_key ] = new CMB2_Option( $option_key );
		}

		return self::$option_sets[ $option_key ];
	}
}

/**
 * Handles getting/setting of values to an option array
 * for a specific option key
 *
 * @package   CMB2
 * @author    CMB2 team
 */
class CMB2_Option {

	/**
	 * Options array
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Current option key
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Initiate option object
	 *
	 * @param string $option_key Option key where data will be saved.
	 *                           Leave empty for temporary data store.
	 * @since 2.0.0
	 */
	public function __construct( $option_key = '' ) {
		$this->key = ! empty( $option_key ) ? $option_key : '';
	}

	/**
	 * Delete the option from the db
	 *
	 * @since  2.0.0
	 * @return mixed Delete success or failure
	 */
	public function delete_option() {
		$deleted = $this->key ? delete_option( $this->key ) : true;
		$this->options = $deleted ? array() : $this->options;
		return $this->options;
	}

	/**
	 * Removes an option from an option array
	 *
	 * @since  1.0.1
	 * @param string $field_id Option array field key.
	 * @param bool   $resave Whether or not to resave.
	 * @return array             Modified options
	 */
	public function remove( $field_id, $resave = false ) {

		$this->get_options();

		if ( isset( $this->options[ $field_id ] ) ) {
			unset( $this->options[ $field_id ] );
		}

		if ( $resave ) {
			$this->set();
		}

		return $this->options;
	}

	/**
	 * Retrieves an option from an option array
	 *
	 * @since  1.0.1
	 * @param string $field_id Option array field key.
	 * @param mixed  $default  Fallback value for the option.
	 * @return array             Requested field or default
	 */
	public function get( $field_id, $default = false ) {
		$opts = $this->get_options();

		if ( 'all' == $field_id ) {
			return $opts;
		} elseif ( array_key_exists( $field_id, $opts ) ) {
			return false !== $opts[ $field_id ] ? $opts[ $field_id ] : $default;
		}

		return $default;
	}

	/**
	 * Updates Option data
	 *
	 * @since  1.0.1
	 * @param string $field_id Option array field key.
	 * @param mixed  $value    Value to update data with.
	 * @param bool   $resave   Whether to re-save the data.
	 * @param bool   $single   Whether data should not be an array.
	 * @return boolean Return status of update.
	 */
	public function update( $field_id, $value = '', $resave = false, $single = true ) {
		$this->get_options();

		if ( true !== $field_id ) {

			if ( ! $single ) {
				// If multiple, add to array.
				$this->options[ $field_id ][] = $value;
			} else {
				$this->options[ $field_id ] = $value;
			}
		}

		if ( $resave || true === $field_id ) {
			return $this->set();
		}

		return true;
	}

	/**
	 * Saves the option array
	 * Needs to be run after finished using remove/update_option
	 *
	 * @uses apply_filters() Calls 'cmb2_override_option_save_{$this->key}' hook
	 * to allow overwriting the option value to be stored.
	 *
	 * @since  1.0.1
	 * @param  array $options Optional options to override.
	 * @return bool           Success/Failure
	 */
	public function set( $options = array() ) {
		if ( ! empty( $options ) || empty( $options ) && empty( $this->key ) ) {
			$this->options = $options;
		}

		$this->options = wp_unslash( $this->options ); // get rid of those evil magic quotes.

		if ( empty( $this->key ) ) {
			return false;
		}

		$test_save = apply_filters( "cmb2_override_option_save_{$this->key}", 'cmb2_no_override_option_save', $this->options, $this );

		if ( 'cmb2_no_override_option_save' !== $test_save ) {
			// If override, do not proceed to update the option, just return result.
			return $test_save;
		}

		/**
		 * Whether to auto-load the option when WordPress starts up.
		 *
		 * The dynamic portion of the hook name, $this->key, refers to the option key.
		 *
		 * @since 2.4.0
		 *
		 * @param bool        $autoload   Whether to load the option when WordPress starts up.
		 * @param CMB2_Option $cmb_option This object.
		 */
		$autoload = apply_filters( "cmb2_should_autoload_{$this->key}", true, $this );

		return update_option(
			$this->key,
			$this->options,
			! $autoload || 'no' === $autoload ? false : true
		);
	}

	/**
	 * Retrieve option value based on name of option.
	 *
	 * @uses apply_filters() Calls 'cmb2_override_option_get_{$this->key}' hook to allow
	 * overwriting the option value to be retrieved.
	 *
	 * @since  1.0.1
	 * @param  mixed $default Optional. Default value to return if the option does not exist.
	 * @return mixed          Value set for the option.
	 */
	public function get_options( $default = null ) {
		if ( empty( $this->options ) && ! empty( $this->key ) ) {

			$test_get = apply_filters( "cmb2_override_option_get_{$this->key}", 'cmb2_no_override_option_get', $default, $this );

			if ( 'cmb2_no_override_option_get' !== $test_get ) {
				$this->options = $test_get;
			} else {
				// If no override, get the option.
				$this->options = get_option( $this->key, $default );
			}
		}

		$this->options = (array) $this->options;

		return $this->options;
	}

}
