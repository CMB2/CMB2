<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Radio extends WP_Customize_Control {
    public $type = 'radio';
    
    public function render_content() {       
       ob_start();
       $counter = 0;
       echo sprintf( '<div id="customize-control-%s" class="customize-control customize-control-radio" style="display: list-item;">', esc_attr( $this->id ) );
       echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );

       foreach( $this->input_attrs as $label => $value ) {
           printf( '<label for="%1$s"><input type="radio" value="%2$s" id="%1$s" name="%3$s" %4$s data-customize-setting-link="%6$s" />&nbsp;%5$s</label>', esc_attr( $this->id . '_' . $counter ), esc_html( $value ), '_' . esc_attr( $this->id ), checked( $value, $this->value(), false ), esc_html( $value ), esc_attr( $this->id ) );
           echo '<br />';
           $counter += 1;   
       }
       echo '</div>';
    }
}