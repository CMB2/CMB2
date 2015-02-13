<?php
/**
 * Stores each CMB2 instance
 */
class CMB2_Boxes {

	/**
	 * Array of all metabox objects
	 * @var   array
	 * @since 2.0.0
	 */
	protected static $meta_boxes = array();

	public static function add( $meta_box ) {
		self::$meta_boxes[ $meta_box->cmb_id ] = $meta_box;
	}

	public static function get( $cmb_id ) {
		if ( empty( self::$meta_boxes ) || empty( self::$meta_boxes[ $cmb_id ] ) ) {
			return false;
		}

		return self::$meta_boxes[ $cmb_id ];
	}

	public static function get_all() {
		return self::$meta_boxes;
	}

}
