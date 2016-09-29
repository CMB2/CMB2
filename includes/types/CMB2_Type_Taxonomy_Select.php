<?php
/**
 * CMB taxonomy_select field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Taxonomy_Select extends CMB2_Type_Taxonomy_Base {

	private function render_options( $terms , $saved_term ) {
		$options = '';
		foreach ( $terms as $term ) {
			$options .= $this->select_option( array(
					'label'   => $term->name,
					'value'   => $term->slug,
					'checked' => $saved_term === $term->slug,
			) );
		}
		return $options;
	}
	
	public function render() {
		$names = $this->get_object_terms();

		$saved_term  = is_wp_error( $names ) || empty( $names ) ? $this->field->get_default() : $names[key( $names )]->slug;
		$terms       = $this->get_terms();
		$options     = '';
		$option_none = $this->field->args( 'show_option_none' );
		$hierarchy   = $this->field->args( 'hierarchy' );
		
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
		
		if ( ! empty( $terms ) ) {
			if ( $hierarchy ) {
				$groups = array();
				foreach ( $terms as $term ) {
					if ( $term->parent == 0 ) {
						if ( isset( $groups[$term->term_id] ) ) {
							$groups[$term->term_id]['term'] = $term;
						} else {
							$groups[$term->term_id] = array( 'term' => $term, 'children' => array() );
						}
					} else {
						if ( !isset( $groups[$term->parent] ) ) {
							$groups[$term->parent] = array('term' => null, 'children' => array() );
						}
						$groups[$term->parent]['children'][] = $term;
					}
				}
				foreach( $groups as $group ) {
					$options .= $this->select_optgroup( array (
							'label' => $group['term']->name,
							'options' => $this->render_options( $group['children'], $saved_term )
					) );
				}
			} else {
				$this->render_options( $terms, $saved_term );
			}
		}

		return $this->rendered(
			$this->types->select( array( 'options' => $options ) )
		);
	}
}
