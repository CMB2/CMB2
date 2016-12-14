<?php
/**
 * CMB Taxonomy base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_Type_Taxonomy_Base extends CMB2_Type_Multi_Base {

	/**
	 * Checks if we can get a post object, and if so, uses `get_the_terms` which utilizes caching.
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
	 * @since  2.2.2
	 * @return mixed Array of terms on success
	 */
	public function get_terms() {
		return CMB2_Utils::wp_at_least( '4.5.0' )
			? get_terms( array( 'taxonomy' => $this->field->args( 'taxonomy' ), 'hide_empty' => false ) )
			: get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
	}

}
