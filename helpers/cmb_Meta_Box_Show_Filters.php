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

		$object_id = is_admin() ? cmb_Meta_Box::get_object_id() : @get_the_id();

		if ( ! $object_id )
			return false;

		// If current page id is in the included array, display the metabox
		return in_array( $object_id, (array) $meta_box['show_on']['value'] );
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

	/**
	 * Only show options-page metaboxes on their options page (but only enforce on the admin side)
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_admin_page( $display, $meta_box ) {

		// check if this is a 'options-page' metabox
		if ( ! isset( $meta_box['show_on']['key'] ) || 'options-page' !== $meta_box['show_on']['key'] )
			return $display;

		// Enforce 'show_on' filter in the admin
		if ( is_admin() ) {

			// If there is no 'page' query var, our filter isn't applicable
			if ( ! isset( $_GET['page'] ) )
				return $display;

			if ( ! isset( $meta_box['show_on']['value'] ) )
				return false;

			$pages = $meta_box['show_on']['value'];

			if ( is_array( $pages ) ) {
				foreach ( $pages as $page ) {
					if ( $_GET['page'] == $page )
						return true;
				}
			} else {
				if ( $_GET['page'] == $pages )
					return true;
			}

			return false;

		}

		// Allow options-page metaboxes to be displayed anywhere on the front-end
		return true;
	}

}
