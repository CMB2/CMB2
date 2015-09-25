<?php
/* Proof of concept of creating custom meta boxes for customizer */
/* Courtesy of JT - http://justintadlock.com/archives/2015/05/26/multiple-checkbox-customizer-control */
class CMB_Customize_Taxonomy_Multi extends WP_Customize_Control {
    public $type = 'taxonomy';
    
    public function enqueue() {
        wp_enqueue_script( 'customizer.js', cmb2_utils()->url( 'js/customizer.js' ), array( 'jquery' ) );
    }
    
    public function render_content() {       
       echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );
       $multi_values = !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value();
       $terms = get_terms( $this->type, array( 'hide_empty' => false ) );
       if ( $terms ) {
            $counter = 0;
            foreach( $terms as $index => $term ) {
                printf( '<label for="%1$s"><input type="checkbox" value="%2$s" id="%1$s" name="%3$s[]" %4$s" />&nbsp;%5$s</label>', esc_attr( $this->id . $counter ), esc_html( $term->term_id ), esc_attr( $this->id ), checked( true, in_array( $term->term_id, $multi_values ), false ), esc_html( $term->name ) );
                echo '<br />';
                $counter += 1; 
            }
        }      
       ?>
       <input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $multi_values ) ); ?>" />
       <?php
    }
}