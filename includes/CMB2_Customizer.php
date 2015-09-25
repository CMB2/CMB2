<?php
require 'customizer/CMB2_Customizer_Checkbox.php';
require 'customizer/CMB2_Customizer_Textarea.php';
require 'customizer/CMB2_Customizer_Radio.php';
require 'customizer/CMB2_Customizer_Radio_Taxonomy.php';
require 'customizer/CMB2_Customizer_Taxonomy_Select.php';
require 'customizer/CMB2_Customizer_Select.php';
require 'customizer/CMB2_Customizer_Text_Time.php';

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
            'title'                   => 'WP_Customize_Control',
            'text'                    => 'WP_Customize_Control',
            'text_small'              => 'WP_Customize_Control',
            'text_medium'             => 'WP_Customize_Control',
            'text_email'              => 'WP_Customize_Control',
            'text_url'                => 'WP_Customize_Control',
            'text_money'              => 'WP_Customize_Control',
            'colorpicker'             => 'WP_Customize_Color_Control',
            'file'                    => 'WP_Customize_Media_Control',
            'checkbox'                => 'CMB_Customize_Checkbox',
            'textarea'                => 'CMB_Customize_Textarea',
            'textarea_small'          => 'CMB_Customize_Textarea',
            'textarea_code'           => 'CMB_Customize_Textarea',
            'radio'                   => 'CMB_Customize_Radio',
            'radio_inline'            => 'CMB_Customize_Radio',
            'taxonomy_radio'          => 'CMB_Customize_Radio_Taxonomy',
            'taxonomy_radio_inline'   => 'CMB_Customize_Radio_Taxonomy',
            'taxonomy_select'         => 'CMB_Customize_Taxonomy_Select',
            'select'                  => 'CMB_Customize_Select',
            'text_time'               => 'CMB_Customize_Text_Time'
        );
        $field_types_builtin = array(
            'WP_Customize_Color_Control',
            'WP_Customize_Media_Control',
            'WP_Customize_Control'
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
                    $type = $field_type[ 'type' ];
                    /* Detect Taxonomy names */
                    
                    if ( $type == 'taxonomy_radio' || $type == 'taxonomy_radio_inline' || $type == 'taxonomy_select' ) {
                        
                        $type = $field_type[ 'taxonomy' ];
                        if ( empty( $type ) ) {
                            continue;
                        }
                    }
                    
                    $customize_args = array(
                		'label'         => $field_type[ 'name' ], 
                		'section'       => $customizer_id,
                		'settings'      => $field_type[ 'id' ],
                		'id'            => $field_type[ 'id' ],
                		'priority'      => 10,
                		'input_attrs'   => $field_type[ 'options' ],
                		'type'          => $type
                    );
                    if ( in_array( $type_class, $field_types_builtin ) ) {
                        unset( $customize_args[ 'type' ] );
                    }
                     
                    
                    $wp_customize->add_control( new $type_class( 
                        $wp_customize, 
                    	$field_type[ 'id' ], 
                    	$customize_args
                    ) );
                } 
                
            
            }
        }    
    }
}