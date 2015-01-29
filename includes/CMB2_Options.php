<?php

/**
 * Retrieves an instance of CMB2_Option based on the option key
 */
class CMB2_Options {
	/**
	 * Array of all CMB2_Option instances
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
 */
class CMB2_Option {

	/**
	 * Options array
	 * @var array
	 */
	protected $options = array();

	/**
	 * Initiate option object
	 * @since 2.0.0
	 */
	public function __construct( $option_key ) {
		$this->key = $option_key;
	}

	/**
	 * Delete the option from the db
	 * @since  2.0.0
	 * @return bool  Delete success or failure
	 */
	public function delete_option() {
		$this->options = delete_option( $this->key );
		return $this->options;
	}

	/**
	 * Removes an option from an option array
	 * @since  1.0.1
	 * @param  string  $field_id Option array field key
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
	 * @since  1.0.1
	 * @param  string  $field_id Option array field key
	 * @param  mixed   $default  Fallback value for the option
	 * @return array             Requested field or default
	 */
	function get( $field_id, $default = false ) {
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
	 * @since  1.0.1
	 * @param  string  $field_id   Option array field key
	 * @param  mixed   $value      Value to update data with
	 * @param  bool    $resave     Whether to re-save the data
	 * @param  bool    $single     Whether data should not be an array
	 * @return boolean             Return status of update
	 */
	function update( $field_id, $value = '', $resave = false, $single = true ) {
		$this->get_options();

		if ( true !== $field_id ) {

			if ( ! $single ) {
				// If multiple, add to array
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
	 * @uses apply_filters() Calls 'cmb2_override_option_save_{$this->key}' hook to allow
	 * 	overwriting the option value to be stored.
	 *
	 * @since  1.0.1
	 * @return boolean Success/Failure
	 */
	function set( $options = false ) {
		$this->options = $options ? $options : $this->options;

		$test_save = apply_filters( "cmb2_override_option_save_{$this->key}", 'cmb2_no_override_option_save', $this->options, $this );

		if ( 'cmb2_no_override_option_save' !== $test_save ) {
			return $test_save;
		}

		// If no override, update the option
		return update_option( $this->key, $this->options );
	}

	/**
	 * Retrieve option value based on name of option.
	 * @uses apply_filters() Calls 'cmb2_override_option_get_{$this->key}' hook to allow
	 * 	overwriting the option value to be retrieved.
	 *
	 * @since  1.0.1
	 * @param  mixed $default Optional. Default value to return if the option does not exist.
	 * @return mixed          Value set for the option.
	 */
	function get_options( $default = null ) {
		if ( empty( $this->options ) ) {

			$test_get = apply_filters( "cmb2_override_option_get_{$this->key}", 'cmb2_no_override_option_get', $default, $this );

			if ( 'cmb2_no_override_option_get' !== $test_get ) {
				$this->options = $test_get;
			} else {
				// If no override, get the option
				$this->options = get_option( $this->key, $default );
			}
		}

		return (array) $this->options;
	}

}
