<?php
/**
 * CMB taxonomy_radio field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Taxonomy_Radio extends CMB2_Type_Taxonomy_Base {

	public function render() {
		$field = $this->field;
		$names = $this->get_object_terms();

		$saved_term = is_wp_error( $names ) || empty( $names ) ? $this->field->get_default() : array_shift( $names )->slug;
		$terms      = $this->get_terms();
		$options    = '';
		$i = 1;

		if ( ! $terms ) {
			$options .= sprintf( '<li><label>%s</label></li>', esc_html( $this->_text( 'no_terms_text', esc_html__( 'No terms', 'cmb2' ) ) ) );
		} else {
			$option_none  = $field->args( 'show_option_none' );
			if ( ! empty( $option_none ) ) {

				$field_id = $this->_id();

				/**
				 * Default (option-none) taxonomy-radio value.
				 *
				 * @since 1.3.0
				 *
				 * @param string $option_none_value Default (option-none) taxonomy-radio value.
				 */
				$option_none_value = apply_filters( 'cmb2_taxonomy_radio_default_value', '' );

				/**
				 * Default (option-none) taxonomy-radio value.
				 *
				 * The dynamic portion of the hook name, $field_id, refers to the field id attribute.
				 *
				 * @since 1.3.0
				 *
				 * @param string $option_none_value Default (option-none) taxonomy-radio value.
				 */
				$option_none_value = apply_filters( "cmb2_taxonomy_radio_{$field_id}_default_value", $option_none_value );

				$args = array(
					'value' => $option_none_value,
					'label' => $option_none,
				);
				if ( $saved_term == $option_none_value ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->list_input( $args, $i );
				$i++;
			}

			foreach ( $terms as $term ) {
				$args = array(
					'value' => $term->slug,
					'label' => $term->name,
				);

				if ( $saved_term == $term->slug ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->list_input( $args, $i );
				$i++;
			}
		}

		return $this->rendered(
			$this->types->radio( array( 'options' => $options ), 'taxonomy_radio' )
		);
	}
}
