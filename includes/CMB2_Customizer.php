<?php
require 'customizer/CMB2_Customizer_Checkbox.php';
require 'customizer/CMB2_Customizer_Textarea.php';
require 'customizer/CMB2_Customizer_Radio.php';
class CMB2_Customizer {
    
	
    public function __construct( $wp_customize ) {
        $this->start_customizer( $wp_customize );
    }
    
    public function start_customizer( $wp_customize ) {
    
        $customizer_objects = array();
        $customizer_boxes = CMB2_Boxes::get_all();
        foreach( $customizer_boxes as $type => $instance ) {
            $customizer_objects[] = cmb2_get_metabox( $type, 0, 'customizer' );
        }
        
        $field_type_mapping = array(
            'title'             => 'WP_Customize_Control',
            'text'              => 'WP_Customize_Control',
            'text_small'        => 'WP_Customize_Control',
            'text_medium'       => 'WP_Customize_Control',
            'text_email'        => 'WP_Customize_Control',
            'text_url'          => 'WP_Customize_Control',
            'text_money'        => 'WP_Customize_Control',
            'colorpicker'       => 'WP_Customize_Color_Control',
            'file'              => 'WP_Customize_Media_Control',
            'checkbox'          => 'CMB_Customize_Checkbox',
            'textarea'          => 'CMB_Customize_Textarea',
            'textarea_small'    => 'CMB_Customize_Textarea',
            'textarea_code'     => 'CMB_Customize_Textarea',
            'radio'             => 'CMB_Customize_Radio'
        );
        /* Can't get to work: text_time, select_timezone, text_date_timestamp, text_datetime_timestamp, text_datetime_timestamp_timezone , hidden*/
        
        
        
            
        foreach( $customizer_objects as $index => $cmb ) {
            /* Add Address Info to Customizer */
            $customizer_id = $cmb->prop( 'id' );
            $wp_customize->add_section( 
            	$customizer_id, 
            	array(
            		'title'       => $cmb->prop( 'title' ),
            		'priority'    => $cmb->prop( 'priority' ),
            		'capability'  => 'edit_theme_options',
            	) 
            );
            $fields = $cmb->prop( 'fields' );
            foreach( $fields as $field_type ) {
                if ( !isset( $field_type[ 'options' ] ) ) {
                    $field_type[ 'options' ] = array();
                }
                $type = $field_type[ 'type' ];
                $type_class = isset( $field_type_mapping[ $type ] ) ? $field_type_mapping[ $type ] : false;
                
                /* Street */
                $wp_customize->add_setting( $field_type[ 'id' ],
            	array(
            		'type' => 'option',
                    )
                );
                
                if ( class_exists( $type_class ) ) {
                    $wp_customize->add_control( new $type_class( 
            	$wp_customize, 
                	$field_type[ 'id' ],
                	array(
                		'label'    => $field_type[ 'name' ], 
                		'section'  => $customizer_id,
                		'settings' => $field_type[ 'id' ],
                		'id'       => $field_type[ 'id' ],
                		'priority'    => 10,
                		'input_attrs' => $field_type[ 'options' ]
                        )
                    ));
                }
                
            
            }
        }    
    }
}