<?php
class CMB2_Customizer {
    
    /**
	 * Metabox Form ID
	 * @var   CMB2 object
	 * @since 2.0.2
	 */
	protected $cmb;
	
    public function __construct( $wp_customize ) {
        require_once( 'CMB2_Customizer_Text_Control.php' );
        $this->start_customizer( $wp_customize );
    }
    
    public function start_customizer( $wp_customize ) {
        
        
        
        
        /* Add Address Info to Customizer */
    $wp_customize->add_section( 
    	'cmb_address_options', 
    	array(
    		'title'       => __( 'Address Settings', 'mytheme' ),
    		'description' => __('Address Settings', 'mytheme'), 
    		'priority'    => 800,
    	) 
    );
    /* Street */
    $wp_customize->add_setting( 'nest_street',
	array(
    	'default' => '',
		'type' => 'theme_mod',
		'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_setting( 'nest_newline',
	array(
    	'default' => '',
		'type' => 'theme_mod',
		'sanitize_callback' => 'sanitize_text_field'
        )
    );
    
    
    $wp_customize->add_control( new CMB2_Customizer_Text_Control( 
    	$wp_customize, 
    	'nest_street',
    	array(
    		'label'    => 'Street Address', 
    		'section'  => 'cmb_address_options',
    		'settings' => 'nest_street',
    		'priority' => 10,
    		'type' => 'text'
    	)
    )); 
    $wp_customize->add_control( new CMB2_Customizer_Text_Control( 
    	$wp_customize, 
    	'nest_street',
    	array(
    		'label'    => 'Street Address', 
    		'section'  => 'cmb_address_options',
    		'settings' => 'nest_newline',
    		'priority' => 12,
    		'type' => 'text'
    	)
    ));
        return;
        
        $object_type = $this->cmb->prop( 'object_types' );
        if ( 'customizer' != $object_type ) {
           return;
        } 
        /* Add Address Info to Customizer */
        $customizer_id = $this->cmb->prop( 'id' );
        $wp_customize->add_section( 
        	$customizer_id, 
        	array(
        		'title'       => $this->cmb->prop( 'title' ),
        		'priority'    => $this->cmb->prop( 'priority' ),
        		'capability'  => 'edit_theme_options',
        	) 
        );
        
        $fields = $this->cmb->prop( 'fields' );
        foreach( $fields as $field_type ) {
            /* Street */
            $blah = $wp_customize->add_setting( $field_type[ 'id' ],
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
            		'settings' => $blah,
            		'priority' => 10,
            	)
            ));
            /*(
                $wp_customize->add_control( new WP_Customize_Control( 
    	$wp_customize, 
    	'nest_street_address',
    	array(
    		'label'    => 'Street Address', 
    		'section'  => 'nest_address_options',
    		'settings' => 'nest_street',
    		'priority' => 10,
    	)
    ));
    */
        }
        
        
    }
}