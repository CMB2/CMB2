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
	 * Parent term ID when looping hierarchical terms.
	 *
	 * @var integer|null
	 */
	protected $parent = null;

	/**
	 * Checks if we can get a post object, and if so, uses `get_the_terms` which utilizes caching.
	 *
	 * @since  1.0.2
	 * @return mixed Array of terms on success
	 */
	public function get_object_terms() {
		switch ( $this->field->object_type ) {
			case 'options-page':
			case 'term':
				return $this->options_terms();
			case 'post':
				// WP caches internally so it's better to use
				return get_the_terms( $this->field->object_id, $this->field->args( 'taxonomy' ) );

			default:
				return $this->non_post_object_terms();
		}
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
			$terms[ $index ] = $this->get_term_by_value( $term );
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
		$args = array(
			'taxonomy'   => $this->field->args( 'taxonomy' ),
			'hide_empty' => false,
		);

		if ( null !== $this->parent ) {
			$args['parent'] = $this->parent;
		}

		$args = wp_parse_args( $this->field->prop( 'query_args', array() ), $args );

		return CMB2_Utils::wp_at_least( '4.5.0' )
			? get_terms( $args )
			: get_terms( $this->field->args( 'taxonomy' ), http_build_query( $args ) );
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
			: $this->get_term_value( array_shift( $saved_terms ) );
	}

	/**
	 * Gets the term property used for the field values, set by the 'value_field' field arg.
	 *
	 * @since  2.14.0
	 * @return string 'term_id' or 'slug'.
	 */
	public function value_field() {
		static $logged = array();

		$value_field = $this->field->args( 'value_field' );

		// Only these two, since wp_set_object_terms() can't take any other term property.
		// Logged once per field, as this runs for every term.
		if ( $value_field && ! in_array( $value_field, array( 'slug', 'term_id' ), true ) ) {
			$field_id = $this->field->_id( '', false );
			if ( ! isset( $logged[ $field_id ] ) ) {
				$logged[ $field_id ] = true;
				CMB2_Utils::log_if_debug( __METHOD__, __LINE__, "{$this->field->type()} {$field_id} has an unsupported 'value_field', so slugs are used. Only 'slug' and 'term_id' are supported. Got: ", $value_field );
			}
		}

		return 'term_id' === $value_field ? 'term_id' : 'slug';
	}

	/**
	 * Gets the field value for a term.
	 *
	 * @since  2.14.0
	 *
	 * @param  WP_Term|false $term Term object, or false if the term was not found.
	 *
	 * @return int|string          Term ID or term slug, or '' if there is no term.
	 */
	public function get_term_value( $term ) {
		return $term ? $term->{$this->value_field()} : '';
	}

	/**
	 * Gets a term object from a field value.
	 *
	 * @since  2.14.0
	 *
	 * @param  int|string $value Term ID or term slug.
	 *
	 * @return WP_Term|false     Term object, or false if not found.
	 */
	public function get_term_by_value( $value ) {
		$taxonomy = $this->field->args( 'taxonomy' );

		if ( 'term_id' === $this->value_field() ) {
			// Posted values can be anything, and only scalars can be a term ID or slug.
			if ( ! is_scalar( $value ) ) {
				return false;
			}

			// Digits only, not is_numeric(), so slugs like '1e3' or '-1' are not read as IDs.
			if ( preg_match( '/^\d+$/D', (string) $value ) ) {
				return get_term_by( 'id', $value, $taxonomy );
			}
		}

		// Slugs saved before a field switched to term IDs should still resolve.
		// An all-digit slug is read as an ID; see CONVENTIONS.md C14.
		return get_term_by( 'slug', $value, $taxonomy );
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
			$options .= $this->child_option_output( $terms, $saved );
		}

		return $options;
	}

	/**
	 * Build child terms output.
	 *
	 * @since  2.6.1
	 *
	 * @param  array        $terms Array of child terms.
	 * @param  array|string $saved Array of terms set to the object, or single term slug.
	 *
	 * @return string              Child option output.
	 */
	public function child_option_output( $terms, $saved ) {
		$output = '<li class="cmb2-indented-hierarchy"><ul>';
		$output .= $this->loop_terms( $terms, $saved );
		$output .= '</ul></li>';

		return $output;
	}

}
