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

/* Load customizer Options */
add_action( 'cmb2_init', 'cmb2_customizer_init', 1, 1 );
function cmb2_customizer_init() {
    add_action( 'customize_register', 'cmb2_customizer_start', 10, 1 );
    add_action( 'customize_controls_enqueue_scripts', 'cmb2_customizer_enqueue' );
}
function cmb2_customizer_start( $customizer ) {
    new CMB2_Customizer( $customizer );
}
function cmb2_customizer_enqueue() {
    wp_enqueue_script( 'jquery-ui-datetimepicker', cmb2_utils()->url( 'js/jquery-ui-timepicker-addon.min.js' ), array( 'jquery-ui-core', 'jquery-ui-slider', 'jquery-ui-datepicker' ), CMB2_VERSION, true );
    wp_enqueue_script( 'cmb2-scripts', cmb2_utils()->url( "js/cmb2.js" ), array( 'jquery-ui-datetimepicker' ), CMB2_VERSION, true );
    CMB2_JS::localize( false );
    CMB2_hookup::enqueue_cmb_css();
}


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
