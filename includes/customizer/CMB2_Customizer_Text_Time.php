<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Text_Time extends WP_Customize_Control {    
    public function render_content() {
       /* Need help with this so we can use CMB2 native functions */
       printf( '<input type="text" class="cmb2-timepicker text-time" id="%1$s" name="%1$s" data-customize-setting-link="%1$s"', $this->id );        
       return; 
    }
}