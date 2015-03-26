<?php

/**
 * Show On Filters
 * Use the 'cmb2_show_on' filter to further refine the conditions under which a metabox is displayed.
 * Below you can limit it by ID and page template
 *
 * All methods in this class are automatically filtered
 *
 * @since  1.0.0
 */
class CMB2_Show_Filters {

	/**
	 * Get Show_on key. backwards compatible w/ 'key' indexes
	 *
	 * @since  2.0.0
	 *
	 * @param  array $meta_box_args Metabox config array
	 *
	 * @return mixed                show_on key or false
	 */
	private static function get_show_on_key( $meta_box_args ) {
		$show_on = isset( $meta_box_args['show_on'] ) ? (array) $meta_box_args['show_on'] : false;
		if ( $show_on && is_array( $show_on ) ) {

			if ( array_key_exists( 'key', $show_on ) ) {
				return $show_on['key'];
			}

			$keys = array_keys( $show_on );
			return $keys[0];
		}

		return false;
	}

	/**
	 * Get Show_on value. backwards compatible w/ 'value' indexes
	 *
	 * @since  2.0.0
	 *
	 * @param  array $meta_box_args Metabox config array
	 *
	 * @return mixed                show_on value or false
	 */
	private static function get_show_on_value( $meta_box_args ) {
		$show_on = isset( $meta_box_args['show_on'] ) ? (array) $meta_box_args['show_on'] : false;

		if ( $show_on && is_array( $show_on ) ) {

			if ( array_key_exists( 'value', $show_on ) ) {
				return $show_on['value'];
			}

			$keys = array_keys( $show_on );

			return $show_on[ $keys[0] ];
		}

		return array();
	}

	/**
	 * Add metaboxes for an specific ID
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box_args Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_id( $display, $meta_box_args, $cmb ) {

		$key = self::get_show_on_key( $meta_box_args );
		if ( ! $key || 'id' !== $key ) {
			return $display;
		}

		$object_id = is_admin() ? $cmb->object_id() : @get_the_id();

		if ( ! $object_id ) {
			return false;
		}

		// If current page id is in the included array, display the metabox
		return in_array( $object_id, (array) self::get_show_on_value( $meta_box_args ) );
	}

	/**
	 * Add metaboxes for an specific Page Template
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box_args Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_page_template( $display, $meta_box_args, $cmb ) {

		$key = self::get_show_on_key( $meta_box_args );
		if ( ! $key || 'page-template' !== $key ) {
			return $display;
		}

		$object_id = $cmb->object_id();

		if ( ! $object_id || 'post' !== $cmb->object_type() ) {
			return false;
		}

		// Get current template
		$current_template = get_post_meta( $object_id, '_wp_page_template', true );

		// See if there's a match
		if ( $current_template && in_array( $current_template, (array) self::get_show_on_value( $meta_box_args ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Only show options-page metaboxes on their options page (but only enforce on the admin side)
	 * @since  1.0.0
	 * @param  bool  $display  To display or not
	 * @param  array $meta_box_args Metabox config array
	 * @return bool            Whether to display this metabox on the current page.
	 */
	public static function check_admin_page( $display, $meta_box_args ) {

		$key = self::get_show_on_key( $meta_box_args );
		// check if this is a 'options-page' metabox
		if ( ! $key || 'options-page' !== $key ) {
			return $display;
		}

		// Enforce 'show_on' filter in the admin
		if ( is_admin() ) {

			// If there is no 'page' query var, our filter isn't applicable
			if ( ! isset( $_GET['page'] ) ) {
				return $display;
			}

			$show_on = self::get_show_on_value( $meta_box_args );

			if ( empty( $show_on ) ) {
				return false;
			}

			if ( is_array( $show_on ) ) {
				foreach ( $show_on as $page ) {
					if ( $_GET['page'] == $page ) {
						return true;
					}
				}
			} else {
				if ( $_GET['page'] == $show_on ) {
					return true;
				}
			}

			return false;

		}

		// Allow options-page metaboxes to be displayed anywhere on the front-end
		return true;
	}

}
