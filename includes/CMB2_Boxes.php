<?php

/**
 * A CMB2 object instance registry for storing every CMB2 instance.
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
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
	 * Remove a CMB2 instance object from the registry.
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
	 * @return CMB2[] Array of all registered cmb2 instances.
	 */
	public static function get_all() {
		return self::$cmb2_instances;
	}

	/**
	 * Retrieve all CMB2 instances that have the specified property set.
	 *
	 * @since  2.4.0
	 * @param  string $property Property name.
	 * @param  mixed  $compare  (Optional) The value to compare.
	 * @return CMB2[]           Array of matching cmb2 instances.
	 */
	public static function get_by( $property, $compare = 'nocompare' ) {
		$boxes = array();

		foreach ( self::$cmb2_instances as $cmb_id => $cmb ) {
			$prop = $cmb->prop( $property );

			if ( 'nocompare' === $compare ) {
				if ( ! empty( $prop ) ) {
					$boxes[ $cmb_id ] = $cmb;
				}
				continue;
			}

			if ( $compare === $prop ) {
				$boxes[ $cmb_id ] = $cmb;
			}
		}

		return $boxes;
	}

	/**
	 * Retrieve all CMB2 instances as long as they do not include the ignored property.
	 *
	 * @since  2.4.0
	 * @param  string $property  Property name.
	 * @param  mixed  $to_ignore The value to ignore.
	 * @return CMB2[]            Array of matching cmb2 instances.
	 */
	public static function filter_by( $property, $to_ignore = null ) {
		$boxes = array();

		foreach ( self::$cmb2_instances as $cmb_id => $cmb ) {

			if ( $to_ignore === $cmb->prop( $property ) ) {
				continue;
			}

			$boxes[ $cmb_id ] = $cmb;
		}

		return $boxes;
	}

	/**
	 * Deprecated and left for back-compatibility. The original `get_by_property`
	 * method was misnamed and never actually used by CMB2 core.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $property  Property name.
	 * @param  mixed  $to_ignore The value to ignore.
	 * @return CMB2[]            Array of matching cmb2 instances.
	 */
	public static function get_by_property( $property, $to_ignore = null ) {
		_deprecated_function( __METHOD__, '2.4.0', 'CMB2_Boxes::filter_by()' );
		return self::filter_by( $property );
	}
}
