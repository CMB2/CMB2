<?php

class CMB2_Customizer {

	/**
	 * Setup Customizer integration
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function __construct( $wp_customize ) {

		$this->start_customizer( $wp_customize );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );

	}

	/**
	 * Start customizer integration
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function start_customizer( $wp_customize ) {

		$field_type_mapping = array(
			'title'                            => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text'                             => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_small'                       => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_medium'                      => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_email'                       => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_url'                         => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_money'                       => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_time'                        => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_date_timestamp'              => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_datetime_timestamp'          => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'text_datetime_timestamp_timezone' => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'text',
			),
			'textarea'                         => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'textarea',
			),
			'textarea_small'                   => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'textarea',
			),
			'textarea_code'                    => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'textarea',
			),
			'checkbox'                         => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'checkbox',
			),
			'multicheck'                       => array(
				'class' => 'CMB2_Customize_Check_Multi_Control',
				'type'  => 'checkbox',
			),
			'multicheck_inline'                => array(
				'class' => 'CMB2_Customize_Check_Multi_Control',
				'type'  => 'checkbox',
			),
			'radio'                            => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'radio',
			),
			'radio_inline'                     => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'radio',
			),
			'select'                           => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'select',
			),
			'taxonomy_radio'                   => array(
				'class' => 'CMB2_Customize_Taxonomy_Control',
				'type'  => 'radio',
			),
			'taxonomy_radio_inline'            => array(
				'class' => 'CMB2_Customize_Taxonomy_Control',
				'type'  => 'radio',
			),
			'taxonomy_select'                  => array(
				'class' => 'CMB2_Customize_Taxonomy_Control',
				'type'  => 'select',
			),
			'taxonomy_multicheck'              => array(
				'class' => 'CMB2_Customize_Taxonomy_Multi_Control',
				'type'  => 'radio',
			),
			'taxonomy_multicheck_inline'       => array(
				'class' => 'CMB2_Customize_Taxonomy_Multi_Control',
				'type'  => 'radio',
			),
			'colorpicker'                      => array(
				'class' => 'WP_Customize_Color_Control',
				'type'  => 'color',
			),
			'file'                             => array(
				'class' => 'WP_Customize_Media_Control',
				'type'  => 'media',
			),
			'select_timezone'                  => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'select',
			),
			'hidden'                           => array(
				'class' => 'CMB2_Customize_Control',
				'type'  => 'hidden',
			),
		);

		// Allow extending
		$field_type_mapping = apply_filters( 'cmb2_customizer_field_type_mapping', $field_type_mapping );

		// Load classes
		foreach ( $field_type_mapping as $field_type => $field_data ) {
    		$type_class = $field_data[ 'class' ];
			if ( ! class_exists( $type_class ) && 0 === strpos( $type_class, 'CMB2_' ) ) {
				include_once cmb2_dir( 'includes/customizer/' . $type_class . '.php' );
			}
		}

		/**
		 * @var CMB2[] $customizer_objects
		 */
		$customizer_objects = array();
		$customizer_boxes   = CMB2_Boxes::get_all();

		foreach ( $customizer_boxes as $type => $instance ) {
			$customizer_objects[] = cmb2_get_metabox( $type, 0, 'customizer' );
		}

		/* Can't get to work: select_timezone, text_date_timestamp, text_datetime_timestamp, text_datetime_timestamp_timezone */

		foreach ( $customizer_objects as $index => $cmb ) {
			/* Add Address Info to Customizer */
			$customizer_id = $cmb->prop( 'id' );

			$wp_customize->add_section( $customizer_id, array(
				'title'      => $cmb->prop( 'title' ),
				'priority'   => $cmb->prop( 'priority' ),
				'capability' => 'edit_theme_options',
			) );

			$fields = $cmb->prop( 'fields' );

			foreach ( $fields as $field ) {
				$field = $cmb->get_field( $field );

				$field_type = $field->type();
				$field_id   = $field->_id();
				$field_label = $field->name();

				// Skip if it doesn't exist
				if ( ! isset( $field_type_mapping[ $field_type ] ) ) {
					continue;
				}
				$type_class = $field_type_mapping[ $field_type ][ 'class' ];

				$setting_args = array(
					'type' => 'option',
				);
				$wp_customize->add_setting( $field_id, $setting_args );

				if ( class_exists( $type_class ) ) {
					$type = $field_type_mapping[ $field_type ][ 'type' ];;

					$customize_args = array(
						'label'    => $field_label,
						'section'  => $customizer_id,
						'settings' => $field_id,
						'id'       => $field_id,
						'priority' => 10,
						'choices'  => $field->options(),
						'type'     => $type,
					);

					$control = new $type_class( $wp_customize, $field_id, $customize_args );

					// Add field to control for further reference
					$control->cmb2_field = $field;

					$wp_customize->add_control( $control );
				}
			}
		}

	}

	/**
	 * Enqueue scripts needed for CMB2
	 */
	function customize_controls_enqueue_scripts() {

		CMB2_hookup::enqueue_cmb_css();
		CMB2_hookup::enqueue_cmb_js();

	}

}