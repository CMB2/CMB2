<?php
/**
 * CMB File base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_File_Base extends CMB2_Type_Text {

	/**
	 * Determines if a file has a valid image extension
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return bool         Whether file has a valid image extension
	 */
	public function is_valid_img_ext( $file, $blah = false ) {
		$file_ext = cmb2_utils()->get_file_ext( $file );

		$valid_types = array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' );

		/**
		 * Which image types are considered valid image file extensions.
		 *
		 * @since 2.0.9
		 *
		 * @param array $valid_types The valid image file extensions.
		 */
		$is_valid_types = apply_filters( 'cmb2_valid_img_types', $valid_types );
		$is_valid = $file_ext && in_array( $file_ext, (array) $is_valid_types );

		/**
		 * Filter for determining if a field value has a valid image file-type extension.
		 *
		 * The dynamic portion of the hook name, $field_id, refers to the field id attribute.
		 *
		 * @since 2.0.9
		 *
		 * @param bool   $is_valid Whether field value has a valid image file-type extension.
		 * @param string $file     File url.
		 * @param string $file_ext File extension.
		 */
		return (bool) apply_filters( 'cmb2_' . $this->field->id() . '_is_valid_img_ext', $is_valid, $file, $file_ext );
	}

	/**
	 * file/file_list image wrap
	 * @since  2.0.2
	 * @param  array  $args Array of arguments for output
	 * @return string       Image wrap output
	 */
	public function img_status_output( $args ) {
		return sprintf( '<%1$s class="img-status">%2$s<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button"%3$s>%4$s</a></p>%5$s</%1$s>',
			$args['tag'],
			$args['image'],
			isset( $args['cached_id'] ) ? ' rel="' . $args['cached_id'] . '"' : '',
			esc_html( $this->_text( 'remove_image_text', __( 'Remove Image', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

	/**
	 * file/file_list file wrap
	 * @since  2.0.2
	 * @param  array  $args Array of arguments for output
	 * @return string       File wrap output
	 */
	public function file_status_output( $args ) {
		return sprintf( '<%1$s class="file-status"><span>%2$s <strong>%3$s</strong></span>&nbsp;&nbsp; (<a href="%4$s" target="_blank" rel="external">%5$s</a> / <a href="#" class="cmb2-remove-file-button"%6$s>%7$s</a>)%8$s</%1$s>',
			$args['tag'],
			esc_html( $this->_text( 'file_text', __( 'File:', 'cmb2' ) ) ),
			cmb2_utils()->get_file_name_from_path( $args['value'] ),
			$args['value'],
			esc_html( $this->_text( 'file_download_text', __( 'Download', 'cmb2' ) ) ),
			isset( $args['cached_id'] ) ? ' rel="' . $args['cached_id'] . '"' : '',
			esc_html( $this->_text( 'remove_text', __( 'Remove', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

}
