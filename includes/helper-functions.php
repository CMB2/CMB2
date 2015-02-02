<?php

/**
 * Autoloads files with CMB2 classes when needed
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested
 */
function cmb2_autoload_classes( $class_name ) {
	if ( class_exists( $class_name, false ) || false === strpos( $class_name, 'CMB2_' ) ) {
		return;
	}

	$file = cmb2_dir( "includes/{$class_name}.php" );
	if ( file_exists( $file ) ) {
		@include_once( $file );
	}
}
spl_autoload_register( 'cmb2_autoload_classes' );

/**
 * Get instance of the CMB2_Utils class
 * @since  2.0.0
 * @return CMB2_Utils object CMB utilities class
 */
function cmb2_utils() {
	static $cmb2_utils;
	$cmb2_utils = $cmb2_utils ? $cmb2_utils : new CMB2_Utils();
	return $cmb2_utils;
}

/**
 * Get instance of the CMB2_Ajax class
 * @since  2.0.0
 * @return CMB2_Ajax object CMB utilities class
 */
function cmb2_ajax( $args = array() ) {
	static $cmb2_ajax;
	$cmb2_ajax = $cmb2_ajax ? $cmb2_ajax : new CMB2_Ajax();
	return $cmb2_ajax;
}

/**
 * Get instance of the CMB2_Option class for the passed metabox ID
 * @since  2.0.0
 * @return CMB2_Option object Options class for setting/getting options for metabox
 */
function cmb2_options( $key ) {
	return CMB2_Options::get( $key );
}

/**
 * Get a cmb oEmbed. Handles oEmbed getting for non-post objects
 * @since  2.0.0
 * @param  array   $args Arguments. Accepts:
 *
 *         'url'         - URL to retrieve the oEmbed from,
 *         'object_id'   - $post_id,
 *         'object_type' - 'post',
 *         'oembed_args' - $embed_args, // array containing 'width', etc
 *         'field_id'    - false,
 *         'cache_key'   - false,
 *
 * @return string        oEmbed string
 */
function cmb2_get_oembed( $args = array() ) {
	return cmb2_ajax()->get_oembed( $args );
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
 * A helper function to update an option in a CMB options array
 * @since  2.0.0
 * @param  string  $option_key Option key
 * @param  string  $field_id   Option array field key
 * @param  mixed   $value      Value to update data with
 * @param  boolean $single     Whether data should not be an array
 * @return boolean             Success/Failure
 */
function cmb2_update_option( $option_key, $field_id, $value, $single = true ) {
	if ( cmb2_options( $option_key )->update( $field_id, $value, false, $single ) ) {
		return cmb2_options( $option_key )->set();
	}

	return false;
}

/**
 * Get a CMB field object.
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array
 * @param  array  $field_id    Field ID or all field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page)
 * @return CMB2_Field|null     CMB2_Field object unless metabox config cannot be found
 */
function cmb2_get_field( $meta_box, $field_id, $object_id = 0, $object_type = 'post' ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb = ( $meta_box instanceof CMB2 ) ? $meta_box : cmb2_get_metabox( $meta_box, $object_id );

	if ( ! $cmb ) {
		return;
	}

	$object_type = $object_type ? $object_type : $cmb->mb_object_type();
	$cmb->object_type( $object_type );

	if ( is_array( $field_id ) && isset( $field_id['id'] ) ) {
		return new CMB2_Field( array(
			'field_args'  => $field_id,
			'object_id'   => $object_id,
			'object_type' => $object_type,
		) );
	}

	$fields = (array) $cmb->prop( 'fields' );
	foreach ( $fields as $field ) {
		if ( $field['id'] == $field_id || $field['name'] == $field_id ) {
			// Send back field object
			return new CMB2_Field( array(
				'field_args'  => $field,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			) );

		}
	}
}

/**
 * Get a field's value.
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array
 * @param  array  $field_id    Field ID or all field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page)
 * @return mixed               Maybe escaped value
 */
function cmb2_get_field_value( $meta_box, $field_id, $object_id = 0, $object_type = 'post' ) {
	$field = cmb2_get_field( $meta_box, $field_id, $object_id, $object_type );
	return $field->escaped_value();
}

/**
 * Retrieve a CMB instance by the metabox ID
 * @since  2.0.0
 * @param  array $meta_box  Metabox ID or Metabox config array
 * @return CMB2 object
 */
function cmb2_get_metabox( $meta_box, $object_id = 0 ) {

	if ( $meta_box instanceof CMB2 ) {
		return $meta_box;
	}

	if ( is_string( $meta_box ) ) {
		$cmb = CMB2_Boxes::get( $meta_box );
	} else {
		// See if we already have an instance of this metabox
		$cmb = CMB2_Boxes::get( $meta_box['id'] );
		// If not, we'll initate a new metabox
		$cmb = $cmb ? $cmb : new CMB2( $meta_box, $object_id );
	}

	if ( $cmb && $object_id ) {
		$cmb->object_id( $object_id );
	}
	return $cmb;
}

/**
 * Retrieve a metabox form
 * @since  2.0.0
 * @param  array   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 * @return string             CMB html form markup
 */
function cmb2_get_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb       = cmb2_get_metabox( $meta_box, $object_id );

	ob_start();
	// Get cmb form
	cmb2_print_metabox_form( $cmb, $object_id, $args );
	$form = ob_get_contents();
	ob_end_clean();

	return apply_filters( 'cmb2_get_metabox_form', $form, $object_id, $cmb );
}

/**
 * Display a metabox form & save it on submission
 * @since  1.0.0
 * @param  array   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 */
function cmb2_print_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb = cmb2_get_metabox( $meta_box, $object_id );

	// if passing a metabox ID, and that ID was not found
	if ( ! $cmb ) {
		return;
	}

	// Set object type to what is declared in the metabox (rather than trying to guess from context)
	$cmb->object_type( $cmb->mb_object_type() );

	// Save the metabox if it's been submitted
	// check permissions
	// @todo more hardening?
	if (
		// check nonce
		isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST[ $cmb->nonce() ] )
		&& wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() )
		&& $object_id && $_POST['object_id'] == $object_id
	) {
		$cmb->save_fields( $object_id, $cmb->object_type(), $_POST );
	}

	// Enqueue JS/CSS
	if ( $cmb->prop( 'cmb_styles' ) ) {
		CMB2_hookup::enqueue_cmb_css();
	}
	CMB2_hookup::enqueue_cmb_js();

	$args = wp_parse_args( $args, array(
		'form_format' => '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-cmb" value="%4$s" class="button-primary"></form>',
		'save_button' => __( 'Save', 'cmb2' ),
	) );

	$form_format = apply_filters( 'cmb2_get_metabox_form_format', $args['form_format'], $object_id, $cmb );

	$format_parts = explode( '%3$s', $form_format );

	// Show cmb form
	printf( $format_parts[0], $cmb->cmb_id, $object_id );
	$cmb->show_form();

	if ( isset( $format_parts[1] ) && $format_parts[1] ) {
		printf( str_ireplace( '%4$s', '%1$s', $format_parts[1] ), $args['save_button'] );
	}

}

/**
 * Display a metabox form (or optionally return it) & save it on submission
 * @since  1.0.0
 * @param  array   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 */
function cmb2_metabox_form( $meta_box, $object_id = 0, $args = array() ) {
	if ( ! isset( $args['echo'] ) || $args['echo'] ) {
		cmb2_print_metabox_form( $meta_box, $object_id, $args );
	} else {
		return cmb2_get_metabox_form( $meta_box, $object_id, $args );
	}
}
