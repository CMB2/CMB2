<?php

/**
 * A CMB2 object instance registry for storing every CMB2 instance.
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Boxes {

	/**
	 * Array of all metabox objects.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected static $cmb2_instances = array();

	/**
	 * Add a CMB2 instance object to the registry.
	 *
	 * @since 1.X.X
	 *
	 * @param CMB2 $cmb_instance CMB2 instance.
	 */
	public static function add( CMB2 $cmb_instance ) {
		self::$cmb2_instances[ $cmb_instance->cmb_id ] = $cmb_instance;
	}

	/**
	 * Remove a CMB2 instance object to the registry.
	 *
	 * @since 1.X.X
	 *
	 * @param string $cmb_id A CMB2 instance id.
	 */
	public static function remove( $cmb_id ) {
		if ( array_key_exists( $cmb_id, self::$cmb2_instances ) ) {
			unset( self::$cmb2_instances[ $cmb_id ] );
		}
	}

	/**
	 * Retrieve a CMB2 instance by cmb id.
	 *
	 * @since 1.X.X
	 *
	 * @param string $cmb_id A CMB2 instance id.
	 *
	 * @return CMB2|bool False or CMB2 object instance.
	 */
	public static function get( $cmb_id ) {
		if ( empty( self::$cmb2_instances ) || empty( self::$cmb2_instances[ $cmb_id ] ) ) {
			return false;
		}

		return self::$cmb2_instances[ $cmb_id ];
	}

	/**
	 * Retrieve all CMB2 instances registered.
	 *
	 * @since  1.X.X
	 * @return CMB2[] Array of all registered metaboxes.
	 */
	public static function get_all() {
		return self::$cmb2_instances;
	}

}
