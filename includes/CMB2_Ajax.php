<?php

/**
 * CMB2 ajax methods
 * (i.e. a lot of work to get oEmbeds to work with non-post objects)
 *
 * @since  0.9.5
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 */
class CMB2_Ajax {

	// Whether to hijack the oembed cache system.
	protected $hijack      = false;
	protected $object_id   = 0;
	protected $embed_args  = array();
	protected $object_type = 'post';
	protected $ajax_update = false;

	/**
	 * Instance of this class.
	 *
	 * @since 2.2.2
	 * @var object
	 */
	protected static $instance;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @since 2.2.2
	 * @return CMB2_Ajax
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 2.2.0
	 */
	protected function __construct() {
		add_action( 'wp_ajax_cmb2_oembed_handler', array( $this, 'oembed_handler' ) );
		add_action( 'wp_ajax_nopriv_cmb2_oembed_handler', array( $this, 'oembed_handler' ) );
		// Need to occasionally clean stale oembed cache data from the option value.
		add_action( 'cmb2_save_options-page_fields', array( __CLASS__, 'clean_stale_options_page_oembeds' ) );
	}

	/**
	 * Handles our oEmbed ajax request
	 *
	 * @since  0.9.5
	 * @return mixed oEmbed embed code | fallback | error message
	 */
	public function oembed_handler() {

		// Verify our nonce.
		if ( ! ( isset( $_REQUEST['cmb2_ajax_nonce'], $_REQUEST['oembed_url'] ) && wp_verify_nonce( $_REQUEST['cmb2_ajax_nonce'], 'ajax_nonce' ) ) ) {
			die();
		}

		// Sanitize our search string.
		$oembed_string = sanitize_text_field( wp_unslash( $_REQUEST['oembed_url'] ) );

		// Send back error if empty.
		if ( empty( $oembed_string ) ) {
			wp_send_json_error( '<p class="ui-state-error-text">' . esc_html__( 'Please Try Again', 'cmb2' ) . '</p>' );
		}

		// Set width of embed.
		$embed_width = isset( $_REQUEST['oembed_width'] ) && intval( $_REQUEST['oembed_width'] ) < 640 ? intval( $_REQUEST['oembed_width'] ) : '640';

		// Set url.
		$oembed_url = esc_url( $oembed_string );

		// Set args.
		$embed_args = array(
			'width' => $embed_width,
		);

		$object_id   = isset( $_REQUEST['object_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['object_id'] ) ) : '';
		$object_type = isset( $_REQUEST['object_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['object_type'] ) ) : 'post';

		/*
		 * The lookup result gets cached against the requested object, so only accept a
		 * target the caller already has rights over -- matching what CMB2's other write
		 * paths require (see CMB2_Hookup::save_post()/save_term()/taxonomy_can_save()).
		 * An empty object_id caches nothing against an object, so it needs no rights.
		 */
		if ( '' !== $object_id && ! $this->can_cache_for_object( $object_type, $object_id ) ) {
			wp_send_json_error( '<p class="ui-state-error-text">' . esc_html__( 'Please Try Again', 'cmb2' ) . '</p>' );
		}

		$this->ajax_update = true;

		// Get embed code (or fallback link).
		$html = $this->get_oembed( array(
			'url'         => $oembed_url,
			'object_id'   => $object_id,
			'object_type' => $object_type,
			'oembed_args' => $embed_args,
			'field_id'    => isset( $_REQUEST['field_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['field_id'] ) ) : '',
		) );

		wp_send_json_success( $html );
	}

	/**
	 * Whether the current user may have an oEmbed result cached against the requested object.
	 *
	 * Both the object type and the object id arrive in the request, and together they
	 * decide where the result is written -- the object's metadata, or, for an
	 * options-page target, the option whose name IS the object id. An object type
	 * outside CMB2's core set names no store to check rights against.
	 *
	 * @since  2.13.0
	 *
	 * @param  string     $object_type Object type the result would be cached against.
	 * @param  string|int $object_id   Object id (an option key for an options-page target).
	 * @return bool                    Whether the caller may write to that target.
	 */
	protected function can_cache_for_object( $object_type, $object_id ) {
		switch ( $object_type ) {
			case 'post':
				return current_user_can( 'edit_post', $object_id );

			case 'user':
				return current_user_can( 'edit_user', $object_id );

			case 'comment':
				return current_user_can( 'edit_comment', $object_id );

			case 'term':
				return $this->can_cache_for_term( $object_id );

			case 'options-page':
				return $this->can_cache_for_options_page( $object_id );
		}

		return false;
	}

	/**
	 * Whether the current user may have a result cached against a term.
	 *
	 * @since  2.13.0
	 *
	 * @param  string|int $term_id Term id.
	 * @return bool                Whether the caller may write to the term's meta.
	 */
	protected function can_cache_for_term( $term_id ) {
		$term = get_term( absint( $term_id ) );

		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		$taxonomy = get_taxonomy( $term->taxonomy );

		// The same check CMB2_Hookup::taxonomy_can_save() makes before writing term meta.
		return isset( $taxonomy->cap->edit_terms ) && current_user_can( $taxonomy->cap->edit_terms );
	}

	/**
	 * Whether the current user may have a result cached into an options-page option.
	 *
	 * The result is stored in the option value itself, so the gate is the registered
	 * box's 'capability' prop -- the value that already governs that options page.
	 * An option key no registered box declared has no such value to consult, and so
	 * is not an accepted target.
	 *
	 * @since  2.13.0
	 *
	 * @param  string $option_key Option key the result would be cached into.
	 * @return bool               Whether the caller may write to that option.
	 */
	protected function can_cache_for_options_page( $option_key ) {
		foreach ( CMB2_Boxes::get_all() as $cmb ) {
			$keys = (array) $cmb->options_page_keys();

			if ( ! in_array( $option_key, $keys, true ) ) {
				continue;
			}

			if ( current_user_can( $cmb->prop( 'capability' ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves oEmbed from url/object ID
	 *
	 * @since  0.9.5
	 * @param  array $args Arguments for method.
	 * @return mixed HTML markup with embed or fallback.
	 */
	public function get_oembed_no_edit( $args ) {
		global $wp_embed;

		$oembed_url = esc_url( $args['url'] );

		// Sanitize object_id.
		$this->object_id = is_numeric( $args['object_id'] ) ? absint( $args['object_id'] ) : sanitize_text_field( $args['object_id'] );

		$args = wp_parse_args( $args, array(
			'object_type' => 'post',
			'oembed_args' => array(),
			'field_id'    => false,
			'wp_error'    => false,
		) );

		$this->embed_args =& $args;

		/*
		 * Set the post_ID so oEmbed won't fail
		 * wp-includes/class-wp-embed.php, WP_Embed::shortcode()
		 */
		$wp_embed->post_ID = $this->object_id;

		// Special scenario if NOT a post object.
		if ( isset( $args['object_type'] ) && 'post' != $args['object_type'] ) {

			if ( 'options-page' == $args['object_type'] ) {

				// Bogus id to pass some numeric checks. Issue with a VERY large WP install?
				$wp_embed->post_ID = 1987645321;
			}

			// Ok, we need to hijack the oembed cache system.
			$this->hijack = true;
			$this->object_type = $args['object_type'];

			// Gets ombed cache from our object's meta (vs postmeta).
			add_filter( 'get_post_metadata', array( $this, 'hijack_oembed_cache_get' ), 10, 3 );

			// Sets ombed cache in our object's meta (vs postmeta).
			add_filter( 'update_post_metadata', array( $this, 'hijack_oembed_cache_set' ), 10, 4 );

		}

		$embed_args = '';

		foreach ( $args['oembed_args'] as $key => $val ) {
			$embed_args .= " $key=\"$val\"";
		}

		// Ping WordPress for an embed.
		$embed = $wp_embed->run_shortcode( '[embed' . $embed_args . ']' . $oembed_url . '[/embed]' );

		// Fallback that WordPress creates when no oEmbed was found.
		$fallback = $wp_embed->maybe_make_link( $oembed_url );

		return compact( 'embed', 'fallback', 'args' );
	}

	/**
	 * Retrieves oEmbed from url/object ID
	 *
	 * @since  0.9.5
	 * @param  array $args Arguments for method.
	 * @return string HTML markup with embed or fallback.
	 */
	public function get_oembed( $args ) {
		$oembed = $this->get_oembed_no_edit( $args );

		// Send back our embed.
		if ( $oembed['embed'] && $oembed['embed'] != $oembed['fallback'] ) {
			return '<div class="cmb2-oembed embed-status">' . $oembed['embed'] . '<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button" rel="' . esc_attr( $oembed['args']['field_id'] ) . '">' . esc_html__( 'Remove Embed', 'cmb2' ) . '</a></p></div>';
		}

		// Otherwise, send back error info that no oEmbeds were found.
		return sprintf(
			'<p class="ui-state-error-text">%s</p>',
			sprintf(
				/* translators: 1: results for. 2: link to codex.wordpress.org/Embeds */
				esc_html__( 'No oEmbed Results Found for %1$s. View more info at %2$s.', 'cmb2' ),
				$oembed['fallback'],
				'<a href="https://wordpress.org/support/article/embeds/" target="_blank">codex.wordpress.org/Embeds</a>'
			)
		);
	}

	/**
	 * Hijacks retrieving of cached oEmbed.
	 * Returns cached data from relevant object metadata (vs postmeta)
	 *
	 * @since  0.9.5
	 * @param  boolean $check     Whether to retrieve postmeta or override.
	 * @param  int     $object_id Object ID.
	 * @param  string  $meta_key  Object metakey.
	 * @return mixed              Object's oEmbed cached data.
	 */
	public function hijack_oembed_cache_get( $check, $object_id, $meta_key ) {
		if ( ! $this->hijack || ( $this->object_id != $object_id && 1987645321 !== $object_id ) ) {
			return $check;
		}

		if ( $this->ajax_update ) {
			return false;
		}

		return $this->cache_action( $meta_key );
	}

	/**
	 * Hijacks saving of cached oEmbed.
	 * Saves cached data to relevant object metadata (vs postmeta)
	 *
	 * @since  0.9.5
	 * @param  boolean $check      Whether to continue setting postmeta.
	 * @param  int     $object_id  Object ID to get postmeta from.
	 * @param  string  $meta_key   Postmeta's key.
	 * @param  mixed   $meta_value Value of the postmeta to be saved.
	 * @return boolean             Whether to continue setting.
	 */
	public function hijack_oembed_cache_set( $check, $object_id, $meta_key, $meta_value ) {

		if (
			! $this->hijack
			|| ( $this->object_id != $object_id && 1987645321 !== $object_id )
			// Only want to hijack oembed meta values.
			|| 0 !== strpos( $meta_key, '_oembed_' )
		) {
			return $check;
		}

		$this->cache_action( $meta_key, $meta_value );

		// Anything other than `null` to cancel saving to postmeta.
		return true;
	}

	/**
	 * Gets/updates the cached oEmbed value from/to relevant object metadata (vs postmeta).
	 *
	 * @since 1.3.0
	 *
	 * @param string $meta_key Postmeta's key.
	 * @return mixed
	 */
	protected function cache_action( $meta_key ) {
		$func_args = func_get_args();
		$action    = isset( $func_args[1] ) ? 'update' : 'get';

		if ( 'options-page' === $this->object_type ) {

			$args = array( $meta_key );

			if ( 'update' === $action ) {
				$args[] = $func_args[1];
				$args[] = true;
			}

			// Cache the result to our options.
			$status = call_user_func_array( array( cmb2_options( $this->object_id ), $action ), $args );
		} else {

			$args = array( $this->object_type, $this->object_id, $meta_key );
			$args[] = 'update' === $action ? $func_args[1] : true;

			// Cache the result to our metadata.
			$status = call_user_func_array( $action . '_metadata', $args );
		}

		return $status;
	}

	/**
	 * Hooks in when options-page data is saved to clean stale
	 * oembed cache data from the option value.
	 *
	 * @since  2.2.0
	 * @param  string $option_key The options-page option key.
	 * @return void
	 */
	public static function clean_stale_options_page_oembeds( $option_key ) {
		$options = cmb2_options( $option_key )->get_options();
		$modified = false;
		if ( is_array( $options ) ) {

			$ttl = apply_filters( 'oembed_ttl', DAY_IN_SECONDS, '', array(), 0 );
			$now = time();

			foreach ( $options as $key => $value ) {
				// Check for cached oembed data.
				if ( 0 === strpos( $key, '_oembed_time_' ) ) {
					$cached_recently = ( $now - $value ) < $ttl;

					if ( ! $cached_recently ) {
						$modified = true;
						// Remove the the cached ttl expiration, and the cached oembed value.
						unset( $options[ $key ] );
						unset( $options[ str_replace( '_oembed_time_', '_oembed_', $key ) ] );
					}
				} elseif ( '{{unknown}}' === $value ) {
					// Remove the cached unknown values.
					$modified = true;
					unset( $options[ $key ] );
				}
			}
		}

		// Update the option and remove stale cache data.
		if ( $modified ) {
			cmb2_options( $option_key )->set( $options );
		}
	}

}
