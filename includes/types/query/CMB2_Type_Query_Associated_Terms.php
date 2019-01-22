<?php
/**
 * CMB2 Query Associated Terms
 *
 * @since  2.X.X
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

/**
 * Class CMB2_Type_Query_Associated_Terms
 */
class CMB2_Type_Query_Associated_Terms extends CMB2_Type_Query_Associated_Objects {

	/**
	 * The query source object type.
	 *
	 * @var string
	 */
	protected $source_type = 'term';

	/**
	 * @var mixed string|array
	 */
	protected $taxonomies = 'post_tag';

	/**
	 * Constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		if ( ! empty( $args['taxonomy'] ) ) {
			$this->taxonomies = $args['taxonomy'];
		}
	}

	/**
	 * @return mixed
	 */
	public function fetch() {
		return get_terms( $this->query_args );
	}

	/**
	 * @param $args
	 * @return array
	 */
	public function default_query_args( $args = array() ) {
		return array(
			'taxonomy' => $this->taxonomies,
			'hide_empty' => false,
		);
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return int
	 */
	public function get_id( $object ) {
		return $object->term_id;
	}

	/**
	 *
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_title( $object ) {
		return $object->name;
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_thumb( $object ) {
		return '';
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_edit_link( $object ) {
		return get_edit_term_link( $object->term_id, $object->taxonomy );
	}

	/**
	 * @param $id
	 *
	 * @return false|WP_Term
	 */
	public function get_object( $id ) {
		$found = false;

		foreach ( (array) $this->taxonomies as $taxonomy ) {
			$term = get_term( $id, $taxonomy );

			if ( ! empty( $term ) && ! $term instanceof WP_Error ) {
				$found = $term;
				break;
			}
		}

		return $found;
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_object_type_label( $object ) {
		$taxonomy = get_taxonomy( $object->taxonomy );
		return $taxonomy->labels->name;
	}

	/**
	 * @return array
	 */
	public function get_all_object_type_labels() {
		$labels = array();

		foreach ( (array) $this->taxonomies as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy );

			if ( ! empty( $tax ) ) {
				$labels[] = $tax->labels->name;
			}
		}

		return $labels;
	}
}
