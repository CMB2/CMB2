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
 * Fires when CMB2 is included/loaded
 *
 * Should be used to add metaboxes. See example-functions.php
 */
do_action( 'cmb2_init' );

/**
 * For back-compat. Does the dirtywork of instantiating all the
 * CMB2 instances for the cmb2_meta_boxes filter
 * @since  2.0.2
 */
$all_meta_boxes_config = apply_filters( 'cmb2_meta_boxes', array() );
foreach ( (array) $all_meta_boxes_config as $meta_box_config ) {
	new CMB2( $meta_box_config );
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
	}
}

/**
 * Fires after CMB2 initiation process has been completed
 */
do_action( 'cmb2_after_init' );

// End. That's it, folks! //
