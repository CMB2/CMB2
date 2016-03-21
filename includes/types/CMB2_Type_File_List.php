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
		$field = $this->field;

		$meta_value = $field->escaped_value();
		$name       = $this->_name();
		$img_size   = $field->args( 'preview_size' );
		$query_args = $field->args( 'query_args' );
		$output     = '';

		$output .= parent::render( array(
			'type'  => 'hidden',
			'class' => 'cmb2-upload-file cmb2-upload-list',
			'size'  => 45, 'desc'  => '', 'value'  => '',
			'data-previewsize' => is_array( $img_size ) ? sprintf( '[%s]', implode( ',', $img_size ) ) : 50,
			'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
			'js_dependencies'  => 'media-editor',
		) );

		$output .= parent::render( array(
			'type'  => 'button',
			'class' => 'cmb2-upload-button button cmb2-upload-list',
			'value'  => esc_html( $this->_text( 'add_upload_files_text', __( 'Add or Upload Files', 'cmb2' ) ) ),
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
						'image'    => wp_get_attachment_image( $id, $img_size ),
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
