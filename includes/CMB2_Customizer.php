<?php
class CMB2_Customizer {
    public function __construct() {
        add_action( 'customize_register', array( $this, 'start_customizer' ) );
    }
    
    public function start_customizer() {
        
    }
}