<?php
/**
 * CMB file field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_File extends CMB2_Type_File_Base {

	public function render() {

		$field       = $this->field;
		$meta_value  = $field->escaped_value();
		$options     = (array) $field->options();
		$img_size    = $field->args( 'preview_size' );
		$size_width  = '';
		$size_height = '';
		$size_name   = '';
		$query_args  = $field->args( 'query_args' );
		$output      = '';

		if ( is_array( $img_size ) ) {
			$size_width  = $img_size[0];
			$size_height = $img_size[1];
			
			// Try and get the closest named size from our array of dimensions
			if ( $named_size = CMB2_Utils::get_named_size( $img_size ) ) {
				$size_name = $named_size;
			}
		} else {
			
			$image_sizes = CMB2_Utils::get_available_image_sizes();

			// The 'thumb' alias, which works elsewhere, doesn't work in the wp.media uploader
			if ( 'thumb' == $img_size ) {
				$img_size = 'thumbnail';
			}

			// Named size doesn't exist, use 'large'
			if ( ! array_key_exists( $img_size, $image_sizes ) ) {
				$img_size = 'large';
			}

			// Get image dimensions from named sizes
			$size_width  = $image_sizes[ $img_size ]['width'];
			$size_height = $image_sizes[ $img_size ]['height'];
			$size_name   = $img_size;
		}

		// if options array and 'url' => false, then hide the url field
		$input_type = array_key_exists( 'url', $options ) && false === $options['url'] ? 'hidden' : 'text';

		$output .= parent::render( array(
			'type'  => $input_type,
			'class' => 'cmb2-upload-file regular-text',
			'size'  => 45,
			'desc'  => '',
			'data-previewsize' => sprintf( '[%s,%s]', $size_width, $size_height ),
			'data-sizename'    => $size_name,
			'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
			'js_dependencies'  => 'media-editor',
		) );

		$output .= sprintf( '<input class="cmb2-upload-button button" type="button" value="%s" />', esc_attr( $this->_text( 'add_upload_file_text', esc_html__( 'Add or Upload File', 'cmb2' ) ) ) );

		$output .= $this->_desc( true );

		$cached_id = $this->_id();

		// Reset field args for attachment ID
		$args = array(
			// If we're looking at a file in a group, we need to get the non-prefixed id
			'id' => ( $field->group ? $field->args( '_id' ) : $cached_id ) . '_id',
		);

		// And get new field object
		// (Need to set it to the types field property)
		$this->types->field = $field = $field->get_field_clone( $args );

		// Get ID value
		$_id_value = $field->escaped_value( 'absint' );

		// We don't want to output "0" as a value.
		if ( ! $_id_value ) {
			$_id_value = '';
		}

		// If there is no ID saved yet, try to get it from the url
		if ( $meta_value && ! $_id_value ) {
			$_id_value = CMB2_Utils::image_id_from_url( esc_url_raw( $meta_value ) );
		}

		$output .= parent::render( array(
			'type'  => 'hidden',
			'class' => 'cmb2-upload-file-id',
			'value' => $_id_value,
			'desc'  => '',
		) );
		$output .= '<div id="' . $this->_id( '-status' ) . '" class="cmb2-media-status">';
		if ( ! empty( $meta_value ) ) {

			if ( $this->is_valid_img_ext( $meta_value ) ) {

				if ( $_id_value ) {
					$image = wp_get_attachment_image( $_id_value, $img_size, null, array( 'class' => 'cmb-file-field-image', 'style' => 'height: auto;' ) );
				} else {
					$image = '<img style="width: auto; height: auto;" src="' . $meta_value . '" class="cmb-file-field-image" alt="" />';
				}

				$output .= $this->img_status_output( array(
					'image'     => $image,
					'tag'       => 'div',
					'cached_id' => $cached_id,
				) );

			} else {

				$output .= $this->file_status_output( array(
					'value'     => $meta_value,
					'tag'       => 'div',
					'cached_id' => $cached_id,
				) );

			}
		}
		$output .= '</div>';

		return $this->rendered( $output );
	}

}
