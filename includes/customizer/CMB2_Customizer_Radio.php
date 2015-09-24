<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Radio extends WP_Customize_Control {
    public $type = 'radio';
    
    public function render_content() {
       /* Need help with this so we can use CMB2 native functions */
       $field = new CMB2_Field( array( 'object_id' => $this->id, 'object_type' => 'radio', 'field_args' => array( 'id' => $this->id, 'type' => 'checkbox', 'name' => $this->id, 'value' => $this->value(), 'desc' => $this->label, 'escaped_value' => esc_html( $this->value() ) ) ) );
       
       
       /*
           'type'  => 'radio',
			'class' => 'cmb2-option',
			'name'  => $this->_name(),
			'id'    => $this->_id( $i ),
			'value' => $this->field->escaped_value(),
			'label' => '',
        */
       
       $type = new CMB2_Types( $field );
       $radio = array();
       $counter = 0;
       foreach( $this->input_attrs as $label => $value) {
           
           $radio[] = sprintf( '<li><input type="radio" value="%s" id="%s" name="%s[]" %s />&nbsp;<label for="%s">%s</label></li>', esc_html( $this->value() ), esc_attr( $this->id . '_' . $counter ), esc_attr( $this->id ), checked( true, true, false ), esc_attr( $this->id . '_' . $counter ), esc_html( $value ) );
           $counter += 1;
               
       }
       
       $radio = implode( '', $radio );
       echo $type->radio( array( 'options' => $radio ), 'radio' );
       return; 
    }
}