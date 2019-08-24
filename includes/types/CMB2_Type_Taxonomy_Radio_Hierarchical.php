<?php
/**
 * CMB taxonomy_radio_hierarchical field type
 *
 * @since  2.2.5
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Taxonomy_Radio_Hierarchical extends CMB2_Type_Taxonomy_Radio {

	/**
	 * Parent term ID when looping hierarchical terms.
	 *
	 * @var integer
	 */
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

}
