<?php

/**
 * CMB ajax methods
 * (i.e. a lot of work to get oEmbeds to work with non-post objects)
 *
 * @since  0.9.5
 */
class CMB2_Ajax {


	// Whether to hijack the oembed cache system
	protected $hijack      = false;
	protected $object_id   = 0;
	protected $embed_args  = array();
	protected $object_type = 'post';
	protected $ajax_update = false;


	/**
	 * Handles our oEmbed ajax request
	 * @since  0.9.5
	 * @return object oEmbed embed code | fallback | error message
	 */
	public function oembed_handler() {

		// Verify our nonce
		if ( ! ( isset( $_REQUEST['cmb2_ajax_nonce'], $_REQUEST['oembed_url'] ) && wp_verify_nonce( $_REQUEST['cmb2_ajax_nonce'], 'ajax_nonce' ) ) ) {
			die();
		}

		// Sanitize our search string
		$oembed_string = sanitize_text_field( $_REQUEST['oembed_url'] );

		// Send back error if empty
		if ( empty( $oembed_string ) ) {
			wp_send_json_error( '<p class="ui-state-error-text">' . __( 'Please Try Again', 'cmb2' ) . '</p>' );
		}

		// Set width of embed
		$embed_width = isset( $_REQUEST['oembed_width'] ) && intval( $_REQUEST['oembed_width'] ) < 640 ? intval( $_REQUEST['oembed_width'] ) : '640';

		// Set url
		$oembed_url = esc_url( $oembed_string );

		// Set args
		$embed_args = array( 'width' => $embed_width );

		$this->ajax_update = true;

		// Get embed code (or fallback link)
		$html = $this->get_oembed( array(
			'url'         => $oembed_url,
			'object_id'   => $_REQUEST['object_id'],
			'object_type' => isset( $_REQUEST['object_type'] ) ? $_REQUEST['object_type'] : 'post',
			'oembed_args' => $embed_args,
			'field_id'    => $_REQUEST['field_id'],
		) );

		wp_send_json_success( $html );
	}


	/**
	 * Retrieves oEmbed from url/object ID
	 * @since  0.9.5
	 * @param  array  $args      Arguments for method
	 * @return string            html markup with embed or fallback
	 */
	public function get_oembed( $args ) {

		global $wp_embed;

		$oembed_url = esc_url( $args['url'] );

		// Sanitize object_id
		$this->object_id = is_numeric( $args['object_id'] ) ? absint( $args['object_id'] ) : sanitize_text_field( $args['object_id'] );

		$args = wp_parse_args( $args, array(
			'object_type' => 'post',
			'oembed_args' => $this->embed_args,
			'field_id'    => false,
			'cache_key'   => false,
		) );

		$this->embed_args =& $args;


		/**
		 * Set the post_ID so oEmbed won't fail
		 * wp-includes/class-wp-embed.php, WP_Embed::shortcode()
		 */
		$wp_embed->post_ID = $this->object_id;

		// Special scenario if NOT a post object
		if ( isset( $args['object_type'] ) && 'post' != $args['object_type'] ) {

			if ( 'options-page' == $args['object_type'] ) {

				// Bogus id to pass some numeric checks. Issue with a VERY large WP install?
				$wp_embed->post_ID = 1987645321;

				// Use our own cache key to correspond to this field (vs one cache key per url)
				$args['cache_key'] = $args['field_id'] . '_cache';
			}

			// Ok, we need to hijack the oembed cache system
			$this->hijack = true;
			$this->object_type = $args['object_type'];

			// Gets ombed cache from our object's meta (vs postmeta)
			add_filter( 'get_post_metadata', array( $this, 'hijack_oembed_cache_get' ), 10, 3 );

			// Sets ombed cache in our object's meta (vs postmeta)
			add_filter( 'update_post_metadata', array( $this, 'hijack_oembed_cache_set' ), 10, 4 );

		}

		$embed_args = '';

		foreach ( $args['oembed_args'] as $key => $val ) {
			$embed_args .= " $key=\"$val\"";
		}

		// Ping WordPress for an embed
		$check_embed = $wp_embed->run_shortcode( '[embed' . $embed_args . ']' . $oembed_url . '[/embed]' );

		// Fallback that WordPress creates when no oEmbed was found
		$fallback = $wp_embed->maybe_make_link( $oembed_url );

		// Send back our embed
		if ( $check_embed && $check_embed != $fallback ) {
			return '<div class="embed-status">' . $check_embed . '<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button" rel="' . $args['field_id'] . '">' . __( 'Remove Embed', 'cmb2' ) . '</a></p></div>';
		}

		// Otherwise, send back error info that no oEmbeds were found
		return '<p class="ui-state-error-text">' . sprintf( __( 'No oEmbed Results Found for %s. View more info at', 'cmb2' ), $fallback ) . ' <a href="http://codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>.</p>';

	}


	/**
	 * Hijacks retrieving of cached oEmbed.
	 * Returns cached data from relevant object metadata (vs postmeta)
	 *
	 * @since  0.9.5
	 * @param  boolean $check     Whether to retrieve postmeta or override
	 * @param  int     $object_id Object ID
	 * @param  string  $meta_key  Object metakey
	 * @return mixed              Object's oEmbed cached data
	 */
	public function hijack_oembed_cache_get( $check, $object_id, $meta_key ) {

		if ( ! $this->hijack || ( $this->object_id != $object_id && 1987645321 !== $object_id ) ) {
			return $check;
		}

		if ( $this->ajax_update ) {
			return false;
		}

		// Get cached data
		return ( 'options-page' === $this->object_type )
			? cmb2_options( $this->object_id )->get( $this->embed_args['cache_key'] )
			: get_metadata( $this->object_type, $this->object_id, $meta_key, true );

	}


	/**
	 * Hijacks saving of cached oEmbed.
	 * Saves cached data to relevant object metadata (vs postmeta)
	 *
	 * @since  0.9.5
	 * @param  boolean $check      Whether to continue setting postmeta
	 * @param  int     $object_id  Object ID to get postmeta from
	 * @param  string  $meta_key   Postmeta's key
	 * @param  mixed   $meta_value Value of the postmeta to be saved
	 * @return boolean             Whether to continue setting
	 */
	public function hijack_oembed_cache_set( $check, $object_id, $meta_key, $meta_value ) {

		if ( ! $this->hijack || ( $this->object_id != $object_id && 1987645321 !== $object_id ) ) {
			return $check;
		}

		$this->oembed_cache_set( $meta_key, $meta_value );

		// Anything other than `null` to cancel saving to postmeta
		return true;
	}


	/**
	 * Saves the cached oEmbed value to relevant object metadata (vs postmeta)
	 *
	 * @since  1.3.0
	 * @param  string  $meta_key   Postmeta's key
	 * @param  mixed   $meta_value Value of the postmeta to be saved
	 */
	public function oembed_cache_set( $meta_key, $meta_value ) {

		// Cache the result to our metadata
		return ( 'options-page' !== $this->object_type )
			? update_metadata( $this->object_type, $this->object_id, $meta_key, $meta_value )
			: cmb2_options( $this->object_id )->update( $this->embed_args['cache_key'], $meta_value, true );
	}

}
