<?php
/**
 * CMB textarea field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Textarea extends CMB2_Type_Base {

	/**
	 * Handles outputting an 'textarea' element
	 *
	 * @since  1.1.0
	 * @param  array $args Override arguments
	 * @return string       Form textarea element
	 */
	public function render( $args = array() ) {
		$args = empty( $args ) ? $this->args : $args;
		$a = $this->parse_args( 'textarea', array(
			'class' => 'cmb2_textarea',
			'name'  => $this->_name(),
			'id'    => $this->_id(),
			'cols'  => 60,
			'rows'  => 10,
			'value' => $this->field->escaped_value( 'esc_textarea' ),
			'desc'  => $this->_desc( true ),
		), $args );

		// Add character counter?
		// Avoid adding when called for grouped WYSIWYGs
		$char_counter_markup = '';
		if ( ! empty( $this->field->args['char_counter'] ) && ( $this->field->args['type'] !== 'wysiwyg' && empty( $this->field->group ) ) ) :

			$char_counter_markup = $this->char_counter_markup();
			$this->field->add_js_dependencies( 'word-count' );
			$this->field->add_js_dependencies( 'cmb2-char-counter' );
			$a['class'] .= ' cmb2-count-chars';

			// Enforce max chars?
			if ( ! empty( $this->field->args['char_max_enforce'] ) && ! empty( $this->field->args['char_max'] ) && $this->field->args['char_counter'] === 'characters' ) :
				$a['maxlength'] = (int) $this->field->args['char_max'];
			endif;

		endif;

		return $this->rendered(
			sprintf( '<textarea%s>%s</textarea>%s%s', $this->concat_attrs( $a, array( 'desc', 'value' ) ), $a['value'], $char_counter_markup, $a['desc'] )
		);
	}
}
