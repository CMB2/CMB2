<?php
/**
 * CMB2 Hookup Field
 *
 * Adds necessary hooks for certain field types.
 *
 * @since  2.11.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Hookup_Field {

	/**
	 * Field id.
	 *
	 * @var   string
	 * @since 2.11.0
	 */
	protected $field_id;

	/**
	 * CMB2 object id.
	 *
	 * @var   string
	 * @since 2.11.0
	 */
	protected $cmb_id;

	/**
	 * The object type we are performing the hookup for
	 *
	 * @var   string
	 * @since 2.11.0
	 */
	protected $object_type = 'post';

	/**
	 * Initialize all hooks for the given field.
	 *
	 * @since  2.11.0
	 * @param  array $field The field arguments array.
	 * @param  CMB2  $cmb   The CMB2 object.
	 * @return array        The field arguments array.
	 */
	public static function init( $field, CMB2 $cmb ) {
		switch ( $field['type'] ) {
			case 'file':
			case 'file_list':
				// Initiate attachment JS hooks.
				add_filter( 'wp_prepare_attachment_for_js', array( 'CMB2_Type_File_Base', 'prepare_image_sizes_for_js' ), 10, 3 );
				break;

			case 'oembed':
				// Initiate oembed Ajax hooks.
				cmb2_ajax();
				break;

			case 'group':
				if ( empty( $field['render_row_cb'] ) ) {
					$field['render_row_cb'] = array( $cmb, 'render_group_callback' );
				}
				break;
			case 'colorpicker':
				// https://github.com/JayWood/CMB2_RGBa_Picker
				// Dequeue the rgba_colorpicker custom field script if it is used,
				// since we now enqueue our own more current version.
				add_action( 'admin_enqueue_scripts', array( 'CMB2_Type_Colorpicker', 'dequeue_rgba_colorpicker_script' ), 99 );
				break;

			case 'text_datetime_timestamp_timezone':
				foreach ( $cmb->box_types() as $object_type ) {
					if ( ! $cmb->is_supported_core_object_type( $object_type ) ) {
						// Ignore post-types...
						continue;
					}

					if ( empty( $field['field_hookup_instance'][ $object_type ] ) ) {
						$instance = new self( $field, $object_type, $cmb );
						$method   = 'options-page' === $object_type
							? 'text_datetime_timestamp_timezone_option_back_compat'
							: 'text_datetime_timestamp_timezone_back_compat';

						$field['field_hookup_instance'][ $object_type ] = array( $instance, $method );
					}

					if ( false === $field['field_hookup_instance'][ $object_type ] ) {
						// If set to false, no need to filter.
						// This can be set if you have updated your use of the field type value to
						// assume the JSON value.
						continue;
					}

					if ( 'options-page' === $object_type ) {
						$option_name = $cmb->object_id();
						add_filter( "pre_option_{$option_name}", $field['field_hookup_instance'][ $object_type ], 10, 3 );
						continue;
					}

					add_filter( "get_{$object_type}_metadata", $field['field_hookup_instance'][ $object_type ], 10, 5 );
				}
				break;
		}

		return $field;
	}

	/**
	 * Constructor
	 *
	 * @since 2.11.0
	 * @param CMB2 $cmb The CMB2 object to hookup.
	 */
	public function __construct( $field, $object_type, CMB2 $cmb ) {
		$this->field_id    = $field['id'];
		$this->object_type = $object_type;
		$this->cmb_id      = $cmb->cmb_id;
	}

	/**
	 * Adds a back-compat shim for text_datetime_timestamp_timezone field type values.
	 *
	 * Handles old serialized DateTime values, as well as the new JSON formatted values.
	 *
	 * @since  2.11.0
	 *
	 * @param  mixed  $value     The value of the metadata.
	 * @param  int    $object_id ID of the object metadata is for.
	 * @param  string $meta_key  Meta key.
	 * @param  bool   $single    Whether to return a single value.
	 * @param  string $meta_type Type of object metadata is for.
	 * @return mixed             Maybe reserialized value.
	 */
	public function text_datetime_timestamp_timezone_back_compat( $value, $object_id, $meta_key, $single, $meta_type ) {
		if ( $meta_key === $this->field_id ) {
			remove_filter( "get_{$meta_type}_metadata", [ $this, __FUNCTION__ ], 10, 5 );
			$value = get_metadata( $meta_type, $object_id, $meta_key, $single );
			add_filter( "get_{$meta_type}_metadata", [ $this, __FUNCTION__ ], 10, 5 );

			$value = $this->reserialize_safe_value( $value );
		}

		return $value;
	}

	/**
	 * Adds a back-compat shim for text_datetime_timestamp_timezone field type values on options pages.
	 *
	 * Handles old serialized DateTime values, as well as the new JSON formatted values.
	 *
	 * @since  2.11.0
	 *
	 * @param  mixed  $value         The value of the option.
	 * @param  string $option        Option name.
	 * @param  mixed  $default_value Default value.
	 * @return mixed                 The updated value.
	 */
	public function text_datetime_timestamp_timezone_option_back_compat( $value, $option, $default_value ) {
		remove_filter( "pre_option_{$option}", [ $this, __FUNCTION__ ], 10, 3 );
		$value = get_option( $option, $default_value );
		add_filter( "pre_option_{$option}", [ $this, __FUNCTION__ ], 10, 3 );

		if ( ! empty( $value ) && is_array( $value ) ) {

			// Loop fields and update values for all text_datetime_timestamp_timezone fields.
			foreach ( CMB2_Boxes::get( $this->cmb_id )->prop( 'fields' ) as $field ) {
				if (
					'text_datetime_timestamp_timezone' === $field['type']
					&& ! empty( $value[ $field['id'] ] )
				) {
					$value[ $field['id'] ] = $this->reserialize_safe_value( $value[ $field['id'] ] );
				}
			}
		}

		return $value;
	}

	/**
	 * Reserialize a value to a safe serialized DateTime value.
	 *
	 * @since  2.11.0
	 *
	 * @param  mixed $value The value to check.
	 * @return mixed       The value, possibly reserialized.
	 */
	protected function reserialize_safe_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'reserialize_safe_value' ], $value );
		}

		$updated_val = CMB2_Utils::get_datetime_from_value( $value );
		$value = $updated_val ? serialize( $updated_val ) : '';

		return $value;
	}
}
