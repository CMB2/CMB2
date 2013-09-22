<?php

/**
 * CMB ajax methods
 * (i.e. a lot of work to get oEmbeds to work with non-post objects)
 *
 * @since  0.9.5
 */
class cmb_Meta_Box_ajax {

	// A single instance of this class.
	public static $instance    = null;
	// Whether to hijack the oembed cache system
	public static $hijack      = false;
	public static $cached      = false;
	public static $object_id   = 0;
	public static $embed_args  = array();
	public static $object_type = 'post';
	public static $originial_use_cache;
	public static $oembed_url;
	public static $cachekey;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return cmb_Meta_Box_types A single instance of this class.
	 */
	public static function get() {
		if ( self::$instance === null )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Handles our oEmbed ajax request
	 * @since  0.9.5
	 * @return object oEmbed embed code | fallback | error message
	 */
	public function oembed_handler() {

		// verify our nonce
		if ( ! ( isset( $_REQUEST['cmb_ajax_nonce'], $_REQUEST['oembed_url'] ) && wp_verify_nonce( $_REQUEST['cmb_ajax_nonce'], 'ajax_nonce' ) ) )
			die();

		// sanitize our search string
		$oembed_string = sanitize_text_field( $_REQUEST['oembed_url'] );

		// send back error if empty
		if ( empty( $oembed_string ) )
			self::send_result( '<p class="ui-state-error-text">'. __( 'Please Try Again', 'cmb' ) .'</p>', false );

		// Set width of embed
		$embed_width = isset( $_REQUEST['oembed_width'] ) && intval( $_REQUEST['oembed_width'] ) < 640 ? intval( $_REQUEST['oembed_width'] ) : '640';

		// set url
		$oembed_url = esc_url( $oembed_string );
		// set args
		$embed_args = array( 'width' => $embed_width );

		// Get embed code (or fallback link)
		$check_embed = self::get_oembed( $oembed_url, $_REQUEST['object_id'], array(
			'object_type' => isset( $_REQUEST['object_type'] ) ? $_REQUEST['object_type'] : 'post',
			'oembed_args' => $embed_args,
		) );

		// check if oembed & get html
		$html = self::oembed_markup( $check_embed, $_REQUEST['field_id'] );

		self::send_result( $html );

	}

	public function get_oembed( $url, $object_id, $args = array() ) {
		global $wp_embed;

		self::$oembed_url = esc_url( $url );
		self::$object_id = absint( $object_id );

		$args = self::$embed_args = wp_parse_args( $args, array(
			'object_type' => 'post',
			'oembed_args' => self::$embed_args,
		) );
		// save the 'usecache' setting
		self::$originial_use_cache = $wp_embed->usecache;
		// set the post_ID so oEmbed won't fail
		$wp_embed->post_ID = self::$object_id;

		// Special scenario if NOT a post object
		if ( isset( $args['object_type'] ) && $args['object_type'] != 'post' ) {

			// Ok, we need to hijack the oembed cache system
			self::$hijack = true;
			self::$object_type = $args['object_type'];

			// If we're not on a page with a post_id
			$wp_embed->usecache = false;

			// Checks ombed cache in our object's meta (vs postmeta)
			add_filter( 'embed_oembed_discover', array( 'cmb_Meta_Box_ajax', 'hijack_cache_oembed_get' ) );

			// Sets ombed cache in our object's meta (vs postmeta)
			add_filter( 'oembed_result', array( 'cmb_Meta_Box_ajax', 'hijack_cache_oembed_set' ) );

			// Tells 'update_post_meta' to not save postmeta
			add_filter( 'update_post_metadata', array( 'cmb_Meta_Box_ajax', 'cancel_postmeta_save' ) );

		}

		$embed_args = '';
		foreach ( $args as $key => $val ) {
			$embed_args .= " $key=\"$val\"";
		}

		// ping WordPress for an embed
		$check_embed = $wp_embed->run_shortcode( '[embed'. $embed_args .']'. self::$oembed_url .'[/embed]' );

		// fallback that WordPress creates when no oEmbed was found
		$fallback = $wp_embed->maybe_make_link( self::$oembed_url );

		// reset the 'usecache' setting
		$wp_embed->usecache = self::$originial_use_cache;

		return array(
			'embed' => $check_embed,
			'fallback' => $fallback,
		);

	}

	/**
	 * Hijacks retrieving of cached oEmbed.
	 * Returns cached data from relevant object metadata (vs postmeta)
	 *
	 * Uses `embed_oembed_discover` filter as a hook
	 *
	 * @since  0.9.5
	 * @param  boolean $discover (untouched) auto-discover boolean
	 * @return boolean           (untouched) auto-discover boolean
	 */
	public function hijack_cache_oembed_get( $discover ) {
		if ( ! self::$hijack || ! self::$originial_use_cache )
			return $discover;

		// Attributes
		$attr = wp_parse_args( self::$embed_args, wp_embed_defaults() );

		// kses converts & into &amp; and we need to undo this
		// See http://core.trac.wordpress.org/ticket/11311
		$url = str_replace( '&amp;', '&', self::$oembed_url );

		// generate cache key
		self::$cachekey = '_oembed_' . md5( $url . serialize( $attr ) );

		// get cached data
		$cache = get_metadata( self::$object_type, self::$object_id, self::$cachekey, true );

		// Failures are cached
		if ( '{{unknown}}' === $cache )
			self::$cached = $this->maybe_make_link( $url );

		if ( ! empty( $cache ) )
			self::$cached = apply_filters( 'embed_oembed_html', $cache, $url, $attr, self::$object_id );

		// return untouched $discover variable
		return $discover;

	}

	public function hijack_cache_oembed_set( $html ) {
		if ( ! self::$hijack )
			return $html;

		// Cache the result to our metadata
		$cache = ( $html ) ? $html : '{{unknown}}';
		update_metadata( self::$object_type, self::$object_id, self::$cachekey, $cache );

		return $html;

	}

	public function cancel_postmeta_save( $check ) {
		if ( ! self::$hijack )
			return $check;

		// Anything other than `null` to cancel saving
		return false;

	}

	public static function oembed_markup( $check_embed, $field_id ) {

		$embed = $fallback = false;

		extract( $check_embed );

		// Send back our embed
		if ( $embed && $embed != $fallback )
			return '<div class="embed_status">'. $embed .'<p><a href="#" class="cmb_remove_file_button" rel="'. $field_id .'">'. __( 'Remove Embed', 'cmb' ) .'</a></p></div>';

		// Send back error info when no oEmbeds were found
		return '<p class="ui-state-error-text">'. sprintf( __( 'No oEmbed Results Found for %s. View more info at', 'cmb' ), $fallback ) .' <a href="http://codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>.</p>';
	}

	/**
	 * Helper to send json encoded response to ajax
	 * @since  0.9.5
	 * @param  string  $data    Data to be shown via ajax
	 * @param  boolean $success Success or fail
	 */
	public static function send_result( $data, $success = true ) {
		$found = $success ? 'found' : 'not found';
		// send back our encoded data
		echo json_encode( array( 'result' => $data, 'id' => $found ) );
		die();
	}

}
