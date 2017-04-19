<?php
/**
 * CMB taxonomy_select field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Taxonomy_Select extends CMB2_Type_Taxonomy_Base {

	public function render() {
		$all_terms = $this->get_terms();

		if ( ! $all_terms || is_wp_error( $all_terms ) ) {
			return $this->no_terms_result( $all_terms, 'strong' );
		}

		$saved_term  = $this->get_object_term_or_default();
		$option_none = $this->field->args( 'show_option_none' );
		$options     = '';

		if ( ! empty( $option_none ) ) {

			$field_id = $this->_id();

			/**
			 * Default (option-none) taxonomy-select value.
			 *
			 * @since 1.3.0
			 *
			 * @param string $option_none_value Default (option-none) taxonomy-select value.
			 */
			$option_none_value = apply_filters( 'cmb2_taxonomy_select_default_value', '' );

			/**
			 * Default (option-none) taxonomy-select value.
			 *
			 * The dynamic portion of the hook name, $field_id, refers to the field id attribute.
			 *
			 * @since 1.3.0
			 *
			 * @param string $option_none_value Default (option-none) taxonomy-select value.
			 */
			$option_none_value = apply_filters( "cmb2_taxonomy_select_{$field_id}_default_value", $option_none_value );

			$options .= $this->select_option( array(
				'label'   => $option_none,
				'value'   => $option_none_value,
				'checked' => $saved_term == $option_none_value,
			) );
		}

		$options .= $this->loop_terms( $all_terms, $saved_term );

		return $this->rendered(
			$this->types->select( array(
				'options' => $options,
			) )
		);
	}

	protected function loop_terms( $all_terms, $saved_term ) {
		$options = '';

		foreach ( $all_terms as $term ) {
			$options .= $this->select_option( array(
				'label'   => $term->name,
				'value'   => $term->slug,
				'checked' => $saved_term === $term->slug,
			) );
		}

		return $options;
	}
}
