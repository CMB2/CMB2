<?php

/**
 * Autoloads files with CMB2 classes when needed
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested
 */
function cmb2_autoload_classes( $class_name ) {
	if ( class_exists( $class_name, false ) || false === stripos( $class_name, 'CMB2_' ) ) {
		return;
	}

	$file = cmb2_dir( "includes/{$class_name}.php" );
	if ( file_exists( $file ) ) {
		@include_once( $file );
	}
}
spl_autoload_register( 'cmb2_autoload_classes' );

function cmb2_utils() {
	static $cmb2_utils;
	$cmb2_utils = $cmb2_utils ? $cmb2_utils : new CMB2_Utils();
	return $cmb2_utils;
}

function cmb2_options( $key ) {
	static $cmb2_options;
	$cmb2_options = $cmb2_options ? $cmb2_options : new CMB2_Options();
	return $cmb2_options->get( $key );
}

/**
 * A helper function to get an option from a CMB options array
 * @since  1.0.1
 * @param  string  $option_key Option key
 * @param  string  $field_id   Option array field key
 * @return array               Options array or specific field
 */
function cmb2_get_option( $option_key, $field_id = '' ) {
	return cmb2_options( $option_key )->get( $field_id );
}

/**
 * Get a CMB field object.
 * @since  1.1.0
 * @param  array  $field_args  Field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
 * @return object              CMB2_field object
 */
function cmb2_get_field( $field_args, $object_id = 0, $object_type = 'post' ) {
	// Default to the loop post ID
	$object_id = $object_id ? $object_id : get_the_ID();
	CMB2::set_object_id( $object_id );
	CMB2::set_object_type( $object_type );
	// Send back field object
	return new CMB2_field( $field_args );
}

/**
 * Get a field's value.
 * @since  1.1.0
 * @param  array  $field_args  Field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page)
 * @return mixed               Maybe escaped value
 */
function cmb2_get_field_value( $field_args, $object_id = 0, $object_type = 'post' ) {
	$field = cmb2_get_field( $field_args, $object_id, $object_type );
	return $field->escaped_value();
}


// function cmb2_get_metabox( $meta_box_id ) {
// 	return CMB2_Base::get_metabox( $meta_box_id );
// }

/**
 * Output a metabox
 * @since 1.0.0
 * @param array $meta_box  Metabox config array
 * @param int   $object_id Object ID
 */
function cmb2_print_metabox( $meta_box, $object_id ) {
	$cmb = new CMB2( $meta_box );
	if ( $cmb ) {

		CMB2::set_object_id( $object_id );

		if ( ! wp_script_is( 'cmb-scripts', 'registered' ) )
			$cmb->register_scripts();

		wp_enqueue_script( 'cmb-scripts' );

		cmb2_utils()->enqueue_cmb_css( $meta_box );

		CMB2::show_form( $meta_box );
	}

}

/**
 * Display a metabox form & save it on submission
 * @since  1.0.0
 * @param  array   $meta_box  Metabox config array
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 * @param  boolean $return    Whether to return or echo form
 * @return string             CMB html form markup
 */
function cmb2_metabox_form( $meta_box, $object_id, $args = array() ) {

	// Backwards compatibility
	if ( is_bool( $args ) ) {
		$args = array( 'echo' => $args );
	}

	$args = wp_parse_args( $args, array(
		'echo'        => true,
		'form_format' => '<form class="cmb-form" method="post" id="%s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%s">%s<input type="submit" name="submit-cmb" value="%s" class="button-primary"></form>',
		'save_button' => __( 'Save' ),
	) );

	$meta_box = CMB2::set_mb_defaults( $meta_box );

	// Make sure form should be shown
	if ( ! apply_filters( 'cmb2_show_on', true, $meta_box ) )
		return '';

	// Make sure that our object type is explicitly set by the metabox config
	CMB2::set_object_type( CMB2::set_mb_type( $meta_box ) );

	// Save the metabox if it's been submitted
	// check permissions
	// @todo more hardening?
	if (
		// check nonce
		isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST['wp_meta_box_nonce'] )
		&& wp_verify_nonce( $_POST['wp_meta_box_nonce'], $cmb->nonce() )
		&& $_POST['object_id'] == $object_id
	)
		CMB2::save_fields( $meta_box, $object_id );

	// Show specific metabox form

	// Get cmb form
	ob_start();
	cmb2_print_metabox( $meta_box, $object_id );
	$form = ob_get_contents();
	ob_end_clean();

	$form_format = apply_filters( 'cmb2_frontend_form_format', $args['form_format'], $object_id, $meta_box, $form );

	$form = sprintf( $form_format, $meta_box['id'], $object_id, $form, $args['save_button'] );

	if ( $args['echo'] )
		echo $form;

	return $form;
}
