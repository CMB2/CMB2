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
	public static $object_id   = 0;
	public static $embed_args  = array();
	public static $object_type = 'post';

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return cmb_Meta_Box_ajax A single instance of this class.
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
		$html = self::get_oembed( $oembed_url, $_REQUEST['object_id'], array(
			'object_type' => isset( $_REQUEST['object_type'] ) ? $_REQUEST['object_type'] : 'post',
			'oembed_args' => $embed_args,
			'field_id' => $_REQUEST['field_id'],
		) );

		self::send_result( $html );

	}

	/**
	 * Retrieves oEmbed from url/object ID
	 * @since  0.9.5
	 * @param  string $url       URL to retrieve oEmbed
	 * @param  int    $object_id Object ID
	 * @param  array  $args      Arguments for method
	 * @return string            html markup with embed or fallback
	 */
	public static function get_oembed( $url, $object_id, $args = array() ) {
		global $wp_embed;

		$oembed_url = esc_url( $url );

		// Sanitize object_id
		self::$object_id = is_numeric( $object_id ) ? absint( $object_id ) : sanitize_text_field( $object_id );

		$args = wp_parse_args( $args, array(
			'object_type' => 'post',
			'oembed_args' => self::$embed_args,
			'field_id'    => false,
			'cache_key'   => false,
		) );

		self::$embed_args =& $args;

		// set the post_ID so oEmbed won't fail
		// wp-includes/class-wp-embed.php, WP_Embed::shortcode(), line 162
		$wp_embed->post_ID = self::$object_id;

		// Special scenario if NOT a post object
		if ( isset( $args['object_type'] ) && $args['object_type'] != 'post' ) {

			if ( 'options-page' == $args['object_type'] ) {
				// bogus id to pass some numeric checks
				// Issue with a VERY large WP install?
				$wp_embed->post_ID = 1987645321;
				// Use our own cache key to correspond to this field (vs one cache key per url)
				$args['cache_key'] = $args['field_id'] .'_cache';
			}
			// Ok, we need to hijack the oembed cache system
			self::$hijack = true;
			self::$object_type = $args['object_type'];

			// Gets ombed cache from our object's meta (vs postmeta)
			add_filter( 'get_post_metadata', array( 'cmb_Meta_Box_ajax', 'hijack_oembed_cache_get' ), 10, 3 );
			// Sets ombed cache in our object's meta (vs postmeta)
			add_filter( 'update_post_metadata', array( 'cmb_Meta_Box_ajax', 'hijack_oembed_cache_set' ), 10, 4 );

		}

		$embed_args = '';
		foreach ( $args['oembed_args'] as $key => $val ) {
			$embed_args .= " $key=\"$val\"";
		}

		// ping WordPress for an embed
		$check_embed = $wp_embed->run_shortcode( '[embed'. $embed_args .']'. $oembed_url .'[/embed]' );

		// fallback that WordPress creates when no oEmbed was found
		$fallback = $wp_embed->maybe_make_link( $oembed_url );

		// Send back our embed
		if ( $check_embed && $check_embed != $fallback )
			return '<div class="embed_status">'. $check_embed .'<p class="cmb_remove_wrapper"><a href="#" class="cmb_remove_file_button" rel="'. $args['field_id'] .'">'. __( 'Remove Embed', 'cmb' ) .'</a></p></div>';

		// Otherwise, send back error info that no oEmbeds were found
		return '<p class="ui-state-error-text">'. sprintf( __( 'No oEmbed Results Found for %s. View more info at', 'cmb' ), $fallback ) .' <a href="http://codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>.</p>';

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
	public static function hijack_oembed_cache_get( $check, $object_id, $meta_key ) {

		if ( ! self::$hijack || ( self::$object_id != $object_id && 1987645321 !== $object_id ) )
			return $check;

		// get cached data
		$data = 'options-page' === self::$object_type
			? cmb_Meta_Box::get_option( self::$object_id, self::$embed_args['cache_key'] )
			: get_metadata( self::$object_type, self::$object_id, $meta_key, true );

		return $data;
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
	public static function hijack_oembed_cache_set( $check, $object_id, $meta_key, $meta_value ) {
		if ( ! self::$hijack || ( self::$object_id != $object_id && 1987645321 !== $object_id ) )
			return $check;

		// Cache the result to our metadata
		if ( 'options-page' === self::$object_type ) {
			// Set the option
			cmb_Meta_Box::update_option( self::$object_id, self::$embed_args['cache_key'], $meta_value, array( 'type' => 'oembed' ) );
			// Save the option
			cmb_Meta_Box::save_option( self::$object_id );
		} else {
			update_metadata( self::$object_type, self::$object_id, $meta_key, $meta_value );
		}

		// Anything other than `null` to cancel saving to postmeta
		return true;
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
