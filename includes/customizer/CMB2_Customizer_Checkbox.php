<?php
/* Proof of concept of creating custom meta boxes for customizer */
class CMB_Customize_Checkbox extends WP_Customize_Control {
    public $type = 'checkbox';
    
    public function render_content() {
       ?>
       <label for="cmb_<?php echo esc_attr( $this->id ); ?>">
        <input type="hidden" value="false" name="<?php echo esc_attr( $this->id ); ?>" />
        <input id="cmb_<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" type="checkbox" <?php $this->link();?> value="true" <?php checked( 'true', $this->value() ); ?> />&nbsp;&nbsp;<?php echo esc_html( $this->label ); ?></label>
        <?php    
    }
}