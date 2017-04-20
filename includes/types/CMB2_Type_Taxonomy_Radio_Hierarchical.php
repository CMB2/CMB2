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
class CMB2_Type_Taxonomy_Radio_Hierarchical extends CMB2_Type_Taxonomy_Radio {
	protected $parent = 0;

	public function render() {
		return $this->rendered(
			$this->types->radio( array(
				'options' => $this->get_term_options(),
			), 'taxonomy_radio_hierarchical' )
		);
	}

	protected function list_term_input( $term, $saved_term ) {
		$options = parent::list_term_input( $term, $saved_term );
		$children = $this->build_children( $term, $saved_term );

		if ( ! empty( $children ) ) {
			$options .= $children;
		}

		return $options;
	}

	public function get_terms() {
		return CMB2_Utils::wp_at_least( '4.5.0' )
			? get_terms( wp_parse_args( $this->field->prop( 'query_args', array() ), array(
				'taxonomy'   => $this->field->args( 'taxonomy' ),
				'hide_empty' => false,
				'parent'     => $this->parent,
			) ) )
			: get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0&parent=0' );
	}

}
