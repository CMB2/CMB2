<?php

/**
 * Helper function to provide directory path to CMB
 * @since  2.0.0
 * @param  string  $path Path to append
 * @return string        Directory with optional path appended
 */
function cmb2_dir( $path = '' ) {
	static $cmb2_dir = null;
	if ( is_null( $cmb2_dir ) ) {
		$cmb2_dir = trailingslashit( dirname( __FILE__ ) );
	}
	return $cmb2_dir . $path;
}

/**
 * Include helper functions,
 * and more importantly, the class/file autoloader
 */
require_once cmb2_dir( 'includes/helper-functions.php' );

/**
 * Fires when CMB2 is included/loaded
 *
 * Should be used to to add metaboxes. See example-functions.php
 */
do_action( 'cmb2_init' );

/**
 * For back-compat. Does the dirtywork of instantiatiating all the
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
