<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Checkbox extends WP_Customize_Control {
    public $type = 'checkbox';
    
    public function render_content() {
       /* Need help with this so we can use CMB2 native functions */
       $field = new CMB2_Field( array( 'customizer' => 'cmb_check', 'object_id' => $this->id, 'object_type' => 'checkbox', 'field_args' => array( 'id' => $this->id, 'type' => 'checkbox', 'name' => $this->id, 'value' => $this->value(), 'desc' => $this->label, 'escaped_value' => esc_html( $this->value() ) ) ) );
       
       $type = new CMB2_Types( $field );
       
       echo $type->checkbox();
       return; 
    }
}