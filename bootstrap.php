<?php
/**
 * Bootstraps the CMB2 process
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

/**
 * Function to encapsulate the CMB2 bootstrap process.
 * @since  2.2.0
 * @return void
 */
function cmb2_bootstrap() {

	if ( is_admin() ) {
		/**
		 * Fires on the admin side when CMB2 is included/loaded.
		 *
		 * In most cases, this should be used to add metaboxes. See example-functions.php
		 */
		do_action( 'cmb2_admin_init' );
	}

	/**
	 * Fires when CMB2 is included/loaded
	 *
	 * Can be used to add metaboxes if needed on the front-end or WP-API (or the front and backend).
	 */
	do_action( 'cmb2_init' );

	/**
	 * For back-compat. Does the dirty-work of instantiating all the
	 * CMB2 instances for the cmb2_meta_boxes filter
	 * @since  2.0.2
	 */
	$cmb_config_arrays = apply_filters( 'cmb2_meta_boxes', array() );
	foreach ( (array) $cmb_config_arrays as $cmb_config ) {
		new CMB2( $cmb_config );
	}

	/**
	 * Fires after all CMB2 instances are created
	 */
	do_action( 'cmb2_init_before_hookup' );

	/**
	 * Get all created metaboxes, and instantiate CMB2_hookup
	 * on metaboxes which require it.
	 * @since  2.0.2
	 */
	foreach ( CMB2_Boxes::get_all() as $cmb ) {

		if ( $cmb->prop( 'hookup' ) ) {
			$hookup = new CMB2_hookup( $cmb );
			$hookup->universal_hooks();
		}

		if ( $cmb->prop( 'show_in_rest' ) && function_exists( 'register_api_field' ) ) {
			$rest = new CMB2_REST( $cmb );
			$rest->universal_hooks();
		}

	}

	/**
	 * Fires after CMB2 initiation process has been completed
	 */
	do_action( 'cmb2_after_init' );
}

// End. That's it, folks! //
