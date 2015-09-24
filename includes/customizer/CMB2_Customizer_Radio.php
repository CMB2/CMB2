<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Radio extends WP_Customize_Control {
    public $type = 'radio';
    
    public function render_content() {
       /* Need help with this so we can use CMB2 native functions */
       $field = new CMB2_Field( array( 'object_id' => $this->id, 'object_type' => 'radio', 'field_args' => array( 'id' => $this->id, 'type' => 'checkbox', 'name' => $this->id, 'value' => $this->value(), 'desc' => $this->label, 'escaped_value' => esc_html( $this->value() ) ) ) );
       
       $type = new CMB2_Types( $field );
       $items = $type->concat_items( $this->input_attrs );
       
       die( '<pre>' . print_r( $items, true ) );
       echo $type->radio( array( 'options' => $type ), 'radio' );
       return; 
    }
}