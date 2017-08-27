<?php

/**
 * CMB2_Options_Page_Hookup object instance registry.
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Options_Pages {
	
	/**
	 * Array of all page hookup objects
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected static $pages = array();
	
	/**
	 * Add a CMB2_Options_Page_Hookup instance object to the registry.
	 *
	 * @since 2.XXX
	 * @param CMB2_Options_Page_Hookup $page
	 * @return string
	 */
	public static function add( CMB2_Options_Page_Hookup $page ) {
		
		self::$pages[ $page->page ] = $page;
		
		return $page->page;
	}
	
	/**
	 * Retrieve a CMB2_Options_Page_Hookup instance by cmb id.
	 *
	 * @since 2.XXX
	 * @param string $page A CMB2_Options_Page_Hookup instance id.
	 * @return CMB2_Options_Page_Hookup|bool  False or CMB2_Options_Page_Hookup object instance.
	 */
	public static function get( $page ) {
		
		if ( empty( self::$pages ) || empty( self::$pages[ $page ] ) ) {
			return FALSE;
		}
		
		return self::$pages[ $page ];
	}
	
	/**
	 * Retrieve all CMB2_Options_Page_Hookup instances registered.
	 *
	 * @since  2.XXX
	 * @return CMB2_Options_Page_Hookup[] Array of all registered CMB2_Options_Page_Hookup instances.
	 */
	public static function get_all() {
		
		return self::$pages;
	}
	
	/**
	 * Retrieve all CMB2_Options_Page_Hookup instances that have the same options key.
	 *
	 * @since  2.XXX
	 * @param  string $key Key matching options-key
	 * @return CMB2[]       Array of matching CMB2_Options_Page_Hookup instances
	 */
	public static function get_by_options_key( $key ) {
		
		$pages = array();
		
		foreach ( self::$pages as $id => $page ) {
			if ( $page->option_key === $key ) {
				$pages[ $id ] = $page;
			}
		}
		
		return $pages;
	}
	
	/**
	 * Remove a CMB2_Options_Page_Hookup instance object from the registry.
	 *
	 * @since 2.XXX
	 * @param string $page A CMB2_Options_Page_Hookup instance id.
	 * @return bool
	 */
	public static function remove( $page ) {
		
		if ( array_key_exists( $page, self::$pages ) ) {
			unset( self::$pages[ $page ] );
			
			return TRUE;
		}
		
		return FALSE;
	}
}