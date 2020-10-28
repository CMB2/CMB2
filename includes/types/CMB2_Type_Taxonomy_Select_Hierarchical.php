<?php
/**
 * CMB taxonomy_select_hierarchical field type
 *
 * @since  2.6.1
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Taxonomy_Select_Hierarchical extends CMB2_Type_Taxonomy_Select {

	/**
	 * Parent term ID when looping hierarchical terms.
	 *
	 * @since  2.6.1
	 *
	 * @var integer
	 */
	protected $parent = 0;

	/**
	 * Child loop depth.
	 *
	 * @since  2.6.1
	 *
	 * @var integer
	 */
	protected $level = 0;

	public function render() {
		return $this->rendered(
			$this->types->select( array(
				'options' => $this->get_term_options(),
			), 'taxonomy_select_hierarchical' )
		);
	}

	public function select_option( $args = array() ) {
		if ( $this->level > 0 ) {
			$args['label'] = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $this->level ) . $args['label'];
		}
		$option = parent::select_option( $args );
		$children = $this->build_children( $this->current_term, $this->saved_term );

		if ( ! empty( $children ) ) {
			$option .= $children;
		}

		return $option;
	}

	/**
	 * Build children hierarchy.
	 *
	 * @since  2.6.1
	 *
	 * @param  array        $terms Array of child terms.
	 * @param  array|string $saved Array of terms set to the object, or single term slug.
	 *
	 * @return string              Child option output.
	 */
	public function child_option_output( $terms, $saved ) {
		$this->level++;
		$output = $this->loop_terms( $terms, $saved );
		$this->level--;

		return $output;
	}

}
