<?php
/**
 * CMB file_list field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_File_List extends CMB2_Type_File_Base {

	public function render() {

		$field       = $this->field;
		$meta_value  = $field->escaped_value();
		$name        = $this->_name();
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
				$img_size = $named_size;
			}
		}

		if ( ! is_array( $img_size ) ) {

			$image_sizes = CMB2_Utils::get_available_image_sizes();

			// The 'thumb' alias, which works elsewhere, doesn't work in the wp.media uploader
			if ( 'thumb' == $img_size ) {
				$img_size = 'thumbnail';
			}

			// Named size doesn't exist, use 'thumbnail'
			if ( ! array_key_exists( $img_size, $image_sizes ) ) {
				$img_size = 'thumbnail';
			}

			// Get image dimensions from named sizes
			$size_width  = $image_sizes[ $img_size ]['width'];
			$size_height = $image_sizes[ $img_size ]['height'];
			$size_name   = $img_size;
		}

		$output .= parent::render( array(
			'type'  => 'hidden',
			'class' => 'cmb2-upload-file cmb2-upload-list',
			'size'  => 45, 'desc'  => '', 'value'  => '',
			'data-previewsize' => sprintf( '[%s,%s]', $size_width, $size_height ),
			'data-sizename'    => $size_name,
			'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
			'js_dependencies'  => 'media-editor',
		) );

		$output .= parent::render( array(
			'type'  => 'button',
			'class' => 'cmb2-upload-button button cmb2-upload-list',
			'value' => esc_attr( $this->_text( 'add_upload_files_text', esc_html__( 'Add or Upload Files', 'cmb2' ) ) ),
			'name'  => '', 'id'  => '',
		) );

		$output .= '<ul id="' . $this->_id( '-status' ) . '" class="cmb2-media-status cmb-attach-list">';

		if ( $meta_value && is_array( $meta_value ) ) {

			foreach ( $meta_value as $id => $fullurl ) {
				$id_input = parent::render( array(
					'type'    => 'hidden',
					'value'   => $fullurl,
					'name'    => $name . '[' . $id . ']',
					'id'      => 'filelist-' . $id,
					'data-id' => $id,
					'desc'    => '',
					'class'   => false,
				) );

				if ( $this->is_valid_img_ext( $fullurl ) ) {

					$output .= $this->img_status_output( array(
						'image'    => wp_get_attachment_image( $id, $img_size, null, array( 'class' => 'cmb-file_list-field-image', 'style' => 'height: auto;' ) ),
						'tag'      => 'li',
						'id_input' => $id_input,
					) );

				} else {

					$output .= $this->file_status_output( array(
						'value'    => $fullurl,
						'tag'      => 'li',
						'id_input' => $id_input,
					) );

				}
			}
		}

		$output .= '</ul>';

		return $this->rendered( $output );
	}

}
