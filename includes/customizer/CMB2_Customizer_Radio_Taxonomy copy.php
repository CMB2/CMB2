<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Radio_Taxonomy extends WP_Customize_Control {
    public $type = 'radio';
    
    public function render_content() {       
       $counter = 0;
       echo sprintf( '<div id="customize-control-%s" class="customize-control customize-control-radio" style="display: list-item;">', esc_attr( $this->id ) );
       echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
       $terms = get_terms( $this->type, array( 'hide_empty' => false ) );
       if ( $terms ) {
          foreach( $terms as $index => $term ) {
               echo sprintf( '<label for="%1$s"><input type="radio" value="%2$s" id="%1$s" name="%3$s" %4$s data-customize-setting-link="%6$s" />&nbsp;%5$s</label><br />', esc_attr( $this->id . '_' . $counter ), esc_html( $term->term_id ), '_' . esc_attr( $this->id ), checked( $term->term_id, $this->value(), false ), esc_html( $term->name ), esc_attr( $this->id ) );
               $counter += 1;   
           } 
       }
       echo '</div>';
    }
}