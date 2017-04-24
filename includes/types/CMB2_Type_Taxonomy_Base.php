<?php
/**
 * CMB Taxonomy base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
abstract class CMB2_Type_Taxonomy_Base extends CMB2_Type_Multi_Base {

	/**
	 * Checks if we can get a post object, and if so, uses `get_the_terms` which utilizes caching.
	 *
	 * @since  1.0.2
	 * @return mixed Array of terms on success
	 */
	public function get_object_terms() {
		if ( 'options-page' === $this->field->object_type ) {
			return $this->options_terms();
		}

		if ( 'post' !== $this->field->object_type ) {
			return $this->non_post_object_terms();
		}

		// WP caches internally so it's better to use
		return get_the_terms( $this->field->object_id, $this->field->args( 'taxonomy' ) );
	}

	/**
	 * Gets the term objects for the terms stored via options boxes.
	 *
	 * @since  2.2.4
	 * @return mixed Array of terms on success
	 */
	public function options_terms() {
		if ( empty( $this->field->value ) ) {
			return array();
		}

		$terms = (array) $this->field->value;

		foreach ( $terms as $index => $term ) {
			$terms[ $index ] = get_term_by( 'slug', $term, $this->field->args( 'taxonomy' ) );
		}

		return $terms;
	}

	/**
	 * For non-post objects, wraps the call to wp_get_object_terms with transient caching.
	 *
	 * @since  2.2.4
	 * @return mixed Array of terms on success
	 */
	public function non_post_object_terms() {
		$object_id = $this->field->object_id;
		$taxonomy = $this->field->args( 'taxonomy' );

		$cache_key = "cmb-cache-{$taxonomy}-{$object_id}";

		// Check cache
		$cached = get_transient( $cache_key );

		if ( ! $cached ) {
			$cached = wp_get_object_terms( $object_id, $taxonomy );
			// Do our own (minimal) caching. Long enough for a page-load.
			set_transient( $cache_key, $cached, 60 );
		}

		return $cached;
	}

	/**
	 * Wrapper for `get_terms` to account for changes in WP 4.6 where taxonomy is expected
	 * as part of the arguments.
	 *
	 * @since  2.2.2
	 * @return mixed Array of terms on success
	 */
	public function get_terms() {
		return CMB2_Utils::wp_at_least( '4.5.0' )
			? get_terms( wp_parse_args( $this->field->prop( 'query_args', array() ), array(
				'taxonomy' => $this->field->args( 'taxonomy' ),
				'hide_empty' => false,
			) ) )
			: get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
	}

	protected function no_terms_result( $error, $tag = 'li' ) {
		if ( is_wp_error( $error ) ) {
			$message = $error->get_error_message();
			$data = 'data-error="' . esc_attr( $error->get_error_code() ) . '"';
		} else {
			$message = $this->_text( 'no_terms_text', esc_html__( 'No terms', 'cmb2' ) );
			$data = '';
		}

		$this->field->args['select_all_button'] = false;

		return sprintf( '<%3$s><label %1$s>%2$s</label></%3$s>', $data, esc_html( $message ), $tag );
	}

	public function get_object_term_or_default() {
		$saved_terms = $this->get_object_terms();

		return is_wp_error( $saved_terms ) || empty( $saved_terms )
			? $this->field->get_default()
			: array_shift( $saved_terms )->slug;
	}

	/**
	 * Takes a list of all tax terms and outputs.
	 *
	 * @since  2.2.5
	 *
	 * @param  array  $all_terms   Array of all terms.
	 * @param  array|string $saved Array of terms set to the object, or single term slug.
	 *
	 * @return string              List of terms.
	 */
	protected function loop_terms( $all_terms, $saved_terms ) {
		return '';
	}

	/**
	 * Build children hierarchy.
	 *
	 * @param  object       $parent_term The parent term object.
	 * @param  array|string $saved       Array of terms set to the object, or single term slug.
	 *
	 * @return string                    List of terms.
	 */
	protected function build_children( $parent_term, $saved ) {
		if ( empty( $parent_term->term_id ) ) {
			return '';
		}

		$this->parent = $parent_term->term_id;

		$terms   = $this->get_terms();
		$options = '';

		if ( ! empty( $terms ) && is_array( $terms ) ) {
			$options = '<li class="cmb2-indented-hierarchy"><ul>';
			$options .= $this->loop_terms( $terms, $saved );
			$options .= '</ul></li>';
		}

		return $options;
	}

}
