<?php
/**
 * Class CMB2_Customize_Check_Multi_Control
 *
 * Created referencing code from Justin Tadlock: http://justintadlock.com/archives/2015/05/26/multiple-checkbox-customizer-control
 */
class CMB2_Customize_Check_Multi_Control extends CMB2_Customize_Control {

	/**
	 * Enqueue scripts
	 */
	public function enqueue() {

		wp_enqueue_script( 'customizer.js', cmb2_utils()->url( 'js/customizer.js' ), array( 'jquery' ) );

	}

	/**
	 * Render content
	 */
	public function render_content() {

		$field_value = $this->value();

		if ( ! is_array( $field_value ) ) {
			$field_value = explode( ',', $field_value );
		}

		$counter = 0;

		echo sprintf( '<span class="customize-control-title">%s</span>', esc_html( $this->label ) );

		foreach ( $this->choices as $value => $label ) {
			$input = sprintf( '<label for="%1$s"><input type="checkbox" value="%2$s" id="%1$s" name="%3$s[]"%4$s />&nbsp;%5$s</label>', esc_attr( $this->id . '_' . $counter ), esc_attr( $value ), esc_attr( $this->id ), checked( true, in_array( $value, $field_value ), false ), esc_html( $label ) );

			echo $input . '<br />';

			$counter += 1;
		}

		echo sprintf( '<input type="hidden" id="%1$s" value="%2$s" %3$s />', esc_attr( $this->id ), esc_attr( implode( ',', $field_value ) ), $this->get_link() );

	}

}