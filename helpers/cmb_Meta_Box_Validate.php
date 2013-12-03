<?php

/**
 * CMB field validation
 * @since  0.0.4
 */
class cmb_Meta_Box_Validate {

	/**
	 * A single instance of this class.
	 * @var   cmb_Meta_Box_types object
	 * @since 1.0.0
	 */
	public static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 * @since  1.0.0
	 * @return cmb_Meta_Box_Validate A single instance of this class.
	 */
	public static function get() {
		if ( self::$instance === null )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Sample field validation
	 * @since  0.0.4
	 */
	public static function check_text( $text ) {
		return $text === 'hello' ? true : false;
	}

	/**
	 * Validate url in a meta value
	 * @since  1.0.1
	 * @param  string  $meta Meta value
	 * @return string        Empty string or escaped url
	 */
	public static function text_url( $meta ) {

		$protocols = isset( cmb_Meta_Box::$field['protocols'] ) ? (array) cmb_Meta_Box::$field['protocols'] : null;
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = $value ? esc_url( $value, $protocols ) : cmb_Meta_Box::$field['default'];
			}
		} else {
			$meta = $meta ? esc_url( $meta, $protocols ) : cmb_Meta_Box::$field['default'];
		}

		return $meta;
	}

	/**
	 * Validate email in a meta value
	 * @since  1.0.1
	 * @param  string  $meta Meta value
	 * @return string        Empty string or validated email
	 */
	public static function text_email( $meta ) {

		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$value = trim( $value );
				$meta[ $key ] = is_email( $value ) ? $value : '';
			}
		} else {
			$meta = trim( $meta );
			$meta = is_email( $meta ) ? $meta : '';
		}

		return $meta;
	}

	/**
	 * Validate money in a meta value
	 * @since  1.0.1
	 * @param  string  $meta Meta value
	 * @return string        Empty string or validated money value
	 */
	public static function text_money( $meta ) {
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = number_format( (float) str_ireplace( ',', '', $value ), 2, '.', ',');
			}
		} else {
			$meta = number_format( (float) str_ireplace( ',', '', $meta ), 2, '.', ',');
		}

		return $meta;
	}

	/**
	 * Default fallback if field's 'sanitization_cb' is NOT defined, or field type does not have a corresponding validation method
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		list( $meta_value, $is_saving, $field ) = $arguments;

		// Handle repeatable fields array
		if ( is_array( $meta_value ) ) {
			foreach ( $meta_value as $key => $value ) {
				// Allow field type validation via filter
				$updated = apply_filters( 'cmb_validate_'. $field['type'], $value, cmb_Meta_Box::get_object_id(), $field, cmb_Meta_Box::get_object_type(), $is_saving );

				if ( $updated === $value ) {
					// If nothing changed, we'll fallback to 'sanitize_text_field'
					$updated = sanitize_text_field( $value );
				}
				$meta_value[ $key ] = $updated;
			}
		} else {
			// Allow field type validation via filter
			$updated = apply_filters( 'cmb_validate_'. $field['type'], $meta_value, cmb_Meta_Box::get_object_id(), $field, cmb_Meta_Box::get_object_type(), $is_saving );
			if ( $updated === $meta_value ) {
				// If nothing changed, we'll fallback to 'sanitize_text_field'
				$updated = sanitize_text_field( $meta_value );
			}
			$meta_value = $updated;
		}

		return $meta_value;
	}

}
