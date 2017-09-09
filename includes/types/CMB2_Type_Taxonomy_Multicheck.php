<?php
/**
 * CMB taxonomy_multicheck field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Taxonomy_Multicheck extends CMB2_Type_Taxonomy_Base {
	protected $counter = 0;

	public function render() {
		return $this->rendered(
			$this->types->radio( array(
				'class'   => $this->get_wrapper_classes(),
				'options' => $this->get_term_options(),
			), 'taxonomy_multicheck' )
		);
	}

	protected function get_term_options() {
		$all_terms = $this->get_terms();

		if ( ! $all_terms || is_wp_error( $all_terms ) ) {
			return $this->no_terms_result( $all_terms );
		}

		return $this->loop_terms( $all_terms, $this->get_object_term_or_default() );
	}

	protected function loop_terms( $all_terms, $saved_terms ) {
		$options = '';
		foreach ( $all_terms as $term ) {
			$options .= $this->list_term_input( $term, $saved_terms );
		}

		return $options;
	}

	protected function list_term_input( $term, $saved_terms ) {
		$args = array(
			'value' => $term->slug,
			'label' => $term->name,
			'type'  => 'checkbox',
			'name'  => $this->_name() . '[]',
		);

		if ( is_array( $saved_terms ) && in_array( $term->slug, $saved_terms ) ) {
			$args['checked'] = 'checked';
		}

		return $this->list_input( $args, ++$this->counter );
	}

	public function get_object_term_or_default() {
		$saved_terms = $this->get_object_terms();

		return is_wp_error( $saved_terms ) || empty( $saved_terms )
			? $this->field->get_default()
			: wp_list_pluck( $saved_terms, 'slug' );
	}

	protected function get_wrapper_classes() {
		$classes = 'cmb2-checkbox-list cmb2-list';
		if ( false === $this->field->args( 'select_all_button' ) ) {
			$classes .= ' no-select-all';
		}

		return $classes;
	}

}
