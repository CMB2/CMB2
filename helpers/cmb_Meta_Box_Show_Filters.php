<?php

/**
 * Show On Filters
 * Use the 'cmb_show_on' filter to further refine the conditions under which a metabox is displayed.
 * Below you can limit it by ID and page template
 *
 * All methods in this class are automatically filtered
 *
 * @since  1.0.0
 */
class cmb_Meta_Box_Show_Filters {

	/**
	 * Add metaboxes for an specific ID
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_id( $display, $meta_box ) {

		if ( ! isset( $meta_box['show_on']['key'] ) || 'id' !== $meta_box['show_on']['key'] )
			return $display;

		$object_id = cmb_Meta_Box::get_object_id();

		if ( ! $object_id )
			return false;

		// If current page id is in the included array, display the metabox
		if ( in_array( $object_id, (array) $meta_box['show_on']['value'] ) )
			return true;

		return false;
	}

	/**
	 * Add metaboxes for an specific Page Template
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_page_template( $display, $meta_box ) {

		if ( ! isset( $meta_box['show_on']['key'] ) || 'page-template' !== $meta_box['show_on']['key'] )
			return $display;

		$object_id = cmb_Meta_Box::get_object_id();

		if ( ! $object_id || cmb_Meta_Box::get_object_type() !== 'post' )
			return false;

		// Get current template
		$current_template = get_post_meta( $object_id, '_wp_page_template', true );

		// See if there's a match
		if ( $current_template && in_array( $current_template, (array) $meta_box['show_on']['value'] ) )
			return true;

		return false;
	}

}
