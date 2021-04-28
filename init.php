<?php
/**
 * The initation loader for CMB2, and the main plugin file.
 *
 * @category     WordPress_Plugin
 * @package      CMB2
 * @author       CMB2 team
 * @license      GPL-2.0+
 * @link         https://cmb2.io
 *
 * Plugin Name:  CMB2
 * Plugin URI:   https://github.com/CMB2/CMB2
 * Description:  CMB2 will create metaboxes and forms with custom fields that will blow your mind.
 * Author:       CMB2 team
 * Author URI:   https://cmb2.io
 * Contributors: Justin Sternberg (@jtsternberg / dsgnwrks.pro)
 *               WebDevStudios (@webdevstudios / webdevstudios.com)
 *               Human Made (@humanmadeltd / hmn.md)
 *               Jared Atchison (@jaredatch / jaredatchison.com)
 *               Bill Erickson (@billerickson / billerickson.net)
 *               Andrew Norcross (@norcross / andrewnorcross.com)
 *
 * Version:      2.9.0
 *
 * Text Domain:  cmb2
 * Domain Path:  languages
 *
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * https://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * *********************************************************************
 *               You should not edit the code below
 *               (or any code in the included files)
 *               or things might explode!
 * ***********************************************************************
 */

if ( ! class_exists( 'CMB2_Bootstrap_290_Develop', false ) ) {

	/**
	 * Handles checking for and loading the newest version of CMB2
	 *
	 * @since  2.0.0
	 *
	 * @category  WordPress_Plugin
	 * @package   CMB2
	 * @author    CMB2 team
	 * @license   GPL-2.0+
	 * @link      https://cmb2.io
	 */
	class CMB2_Bootstrap_290_Develop {

		/**
		 * Current version number
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		const VERSION = '2.9.0';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 2.0.0
		 */
		const PRIORITY = 9959;

		/**
		 * Single instance of the CMB2_Bootstrap_290_Develop object
		 *
		 * @var CMB2_Bootstrap_290_Develop
		 */
		public static $single_instance = null;

		/**
		 * Creates/returns the single instance CMB2_Bootstrap_290_Develop object
		 *
		 * @since  2.0.0
		 * @return CMB2_Bootstrap_290_Develop Single instance object
		 */
		public static function initiate() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
			return self::$single_instance;
		}

		/**
		 * Starts the version checking process.
		 * Creates CMB2_LOADED definition for early detection by other scripts
		 *
		 * Hooks CMB2 inclusion to the init hook on a high priority which decrements
		 * (increasing the priority) with each version release.
		 *
		 * @since 2.0.0
		 */
		private function __construct() {
			/**
			 * A constant you can use to check if CMB2 is loaded
			 * for your plugins/themes with CMB2 dependency
			 */
			if ( ! defined( 'CMB2_LOADED' ) ) {
				define( 'CMB2_LOADED', self::PRIORITY );
			}

			if ( ! function_exists( 'add_action' ) ) {
				// We are running outside of the context of WordPress.
				return;
			}

			add_action( 'init', array( $this, 'include_cmb' ), self::PRIORITY );
		}

		/**
		 * A final check if CMB2 exists before kicking off our CMB2 loading.
		 * CMB2_VERSION and CMB2_DIR constants are set at this point.
		 *
		 * @since  2.0.0
		 */
		public function include_cmb() {
			if ( class_exists( 'CMB2', false ) ) {
				return;
			}

			if ( ! defined( 'CMB2_VERSION' ) ) {
				define( 'CMB2_VERSION', self::VERSION );
			}

			if ( ! defined( 'CMB2_DIR' ) ) {
				define( 'CMB2_DIR', trailingslashit( dirname( __FILE__ ) ) );
			}

			$this->l10ni18n();

			// Include helper functions.
			require_once CMB2_DIR . 'includes/CMB2_Base.php';
			require_once CMB2_DIR . 'includes/CMB2.php';
			require_once CMB2_DIR . 'includes/helper-functions.php';

			// Now kick off the class autoloader.
			spl_autoload_register( 'cmb2_autoload_classes' );

			// Kick the whole thing off.
			require_once( cmb2_dir( 'bootstrap.php' ) );
			cmb2_bootstrap();
		}

		/**
		 * Registers CMB2 text domain path
		 *
		 * @since  2.0.0
		 */
		public function l10ni18n() {

			$loaded = load_plugin_textdomain( 'cmb2', false, '/languages/' );

			if ( ! $loaded ) {
				$loaded = load_muplugin_textdomain( 'cmb2', '/languages/' );
			}

			if ( ! $loaded ) {
				$loaded = load_theme_textdomain( 'cmb2', get_stylesheet_directory() . '/languages/' );
			}

			if ( ! $loaded ) {
				$locale = apply_filters( 'plugin_locale', function_exists( 'determine_locale' ) ? determine_locale() : get_locale(), 'cmb2' );
				$mofile = dirname( __FILE__ ) . '/languages/cmb2-' . $locale . '.mo';
				load_textdomain( 'cmb2', $mofile );
			}

		}

	}

	// Make it so...
	CMB2_Bootstrap_290_Develop::initiate();

}// End if().
