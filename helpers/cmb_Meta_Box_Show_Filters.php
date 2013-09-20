<?php

/**
 * Show On Filters
 * Use the 'cmb_show_on' filter to further refine the conditions under which a metabox is displayed.
 * Below you can limit it by ID and page template
 *
 * All methods in this class are automatically filtered
 */
class cmb_Meta_Box_Show_Filters {

	/**
	 * Add for ID
	 */
	public static function check_id( $display, $meta_box ) {

		if ( ! isset( $meta_box['show_on']['key'] ) || 'id' !== $meta_box['show_on']['key'] )
			return $display;

		$post_id = false;
		// If we're showing it based on ID, get the current ID
		if ( isset( $_GET['post'] ) )
			$post_id = $_GET['post'];
		elseif ( isset( $_POST['post_ID'] ) )
			$post_id = $_POST['post_ID'];

		if ( ! $post_id )
			return false;

		// If current page id is in the included array, display the metabox
		if ( in_array( $post_id, (array) $meta_box['show_on']['value'] ) )
			return true;

		return false;
	}

	/**
	 * Add for Page Template
	 */
	public static function check_page_template( $display, $meta_box ) {

		if ( ! isset( $meta_box['show_on']['key'] ) || 'page-template' !== $meta_box['show_on']['key'] )
			return $display;

		$post_id = false;
		// If we're showing it based on ID, get the current ID
		if ( isset( $_GET['post'] ) )
			$post_id = $_GET['post'];
		elseif ( isset( $_POST['post_ID'] ) )
			$post_id = $_POST['post_ID'];

		if ( ! $post_id || is_page() )
			return false;


		// Get current template
		$current_template = get_post_meta( $post_id, '_wp_page_template', true );

		// See if there's a match
		if ( in_array( $current_template, (array) $meta_box['show_on']['value'] ) )
			return true;

		return false;
	}
}
