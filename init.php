<?php
/**
 * Script Name:  Custom Metaboxes and Fields
 * Contributors: WebDevStudios (@webdevstudios / webdevstudios.com)
 *               Justin Sternberg (@jtsternberg / dsgnwrks.pro)
 *               Jared Atchison (@jaredatch / jaredatchison.com)
 *               Bill Erickson (@billerickson / billerickson.net)
 *               Andrew Norcross (@norcross / andrewnorcross.com)
 * Description:  This will create metaboxes with custom fields that will blow your mind.
 * Version:      2.0.0-beta
 *
 * Text Domain:  cmb2
 * Domain Path:  /languages
 */

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
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

/************************************************************************
                  You should not edit the code below
                  (or any code in the included files)
                  or things might explode!
*************************************************************************/

if ( ! class_exists( 'cmb2_bootstrap_200beta' ) ) {

	/**
	 * Check for newest version of CMB
	 */
	class cmb2_bootstrap_200beta {

		/**
		 * Current version number
		 * @var   string
		 * @since 1.0.0
		 */
		const VERSION = '2.0.0';

		/**
		 * Current version hook priority
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 2.0.0
		 */
		const PRIORITY = 9999;

		public static $single = null;

		public static function go() {
			if ( null === self::$single ) {
				self::$single = new self();
			}
			return self::$single;
		}

		private function __construct() {
			add_action( 'init', array( $this, 'include_cmb' ), self::PRIORITY );
		}

		public function include_cmb() {
			if ( ! class_exists( 'CMB2' ) ) {
				if ( ! defined( 'CMB2_VERSION' ) ) {
					define( 'CMB2_VERSION', self::VERSION );
				}
				$this->l10ni18n();
				require_once 'CMB2.php';
			}
		}

		/**
		 * Load CMB text domain
		 * @since  2.0.0
		 */
		public function l10ni18n() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'cmb2' );
			load_textdomain( 'cmb2', WP_LANG_DIR . '/cmb2/cmb2-' . $locale . '.mo' );
			load_plugin_textdomain( 'cmb2', false, dirname( __FILE__ ) . '/languages/' );
		}

	}
	cmb2_bootstrap_200beta::go();

} // class exists check
