<?php
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
                /* Street */
                $wp_customize->add_setting( $field_type[ 'id' ],
            	array(
            		'type' => 'option',
                    )
                );
    
                $wp_customize->add_control( new WP_Customize_Control( 
            	$wp_customize, 
                	$field_type[ 'id' ],
                	array(
                		'label'    => $field_type[ 'name' ], 
                		'section'  => $customizer_id,
                		'settings' => $field_type[ 'id' ],
                		'priority' => 10,
                	)
                ));
            
            }
        }    
    }
}