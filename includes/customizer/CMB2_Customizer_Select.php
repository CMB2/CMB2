<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Select extends WP_Customize_Control {
    public $type = 'radio';
    
    public function render_content() {       
       echo sprintf( '<div id="customize-control-%s" class="customize-control customize-control-radio" style="display: list-item;">', esc_attr( $this->id ) );
       echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
       echo sprintf( '<select id="%1$s" name="%1$s" data-customize-setting-link="%1$s">', esc_attr( $this->id ) );
       
       foreach( $this->input_attrs as $label => $value ) {
            echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $label ), selected( $value, $this->value(), false ), esc_html( $value ) );   
       } 
       echo '</select>';
    }
}