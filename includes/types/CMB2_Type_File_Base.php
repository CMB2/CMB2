<?php
/**
 * CMB File base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_File_Base extends CMB2_Type_Text {

	/**
	 * Determines if a file has a valid image extension
	 *
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return bool         Whether file has a valid image extension
	 */
	public function is_valid_img_ext( $file, $blah = false ) {
		$file_ext = CMB2_Utils::get_file_ext( $file );

		$valid_types = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'ico', 'icon' );

		$allowed = get_allowed_mime_types();
		if ( ! empty( $allowed ) ) {
			foreach ( (array) $allowed as $type => $mime) {
				if ( 0 === strpos( $mime, 'image/' ) ) {
					$types = explode( '|', $type );
					$valid_types = array_merge( $valid_types, $types );
				}
			}
			$valid_types = array_unique( $valid_types );
		}

		/**
		 * Which image types are considered valid image file extensions.
		 *
		 * @since 2.0.9
		 *
		 * @param array $valid_types The valid image file extensions.
		 */
		$is_valid_types = apply_filters( 'cmb2_valid_img_types', $valid_types );
		$is_valid = $file_ext && in_array( $file_ext, (array) $is_valid_types );
		$field_id = $this->field->id();

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
		return (bool) apply_filters( "cmb2_{$field_id}_is_valid_img_ext", $is_valid, $file, $file_ext );
	}

	/**
	 * file/file_list image wrap
	 *
	 * @since  2.0.2
	 * @param  array $args Array of arguments for output
	 * @return string       Image wrap output
	 */
	public function img_status_output( $args ) {
		return sprintf( '<%1$s class="img-status cmb2-media-item">%2$s<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button"%3$s>%4$s</a></p>%5$s</%1$s>',
			$args['tag'],
			$args['image'],
			isset( $args['cached_id'] ) ? ' rel="' . esc_attr( $args['cached_id'] ) . '"' : '',
			esc_html( $this->_text( 'remove_image_text', esc_html__( 'Remove Image', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

	/**
	 * file/file_list file wrap
	 *
	 * @since  2.0.2
	 * @param  array $args Array of arguments for output
	 * @return string       File wrap output
	 */
	public function file_status_output( $args ) {
		return sprintf( '<%1$s class="file-status cmb2-media-item"><span>%2$s <strong>%3$s</strong></span>&nbsp;&nbsp; (<a href="%4$s" target="_blank" rel="external">%5$s</a> / <a href="#" class="cmb2-remove-file-button"%6$s>%7$s</a>)%8$s</%1$s>',
			$args['tag'],
			esc_html( $this->_text( 'file_text', esc_html__( 'File:', 'cmb2' ) ) ),
			esc_html( CMB2_Utils::get_file_name_from_path( $args['value'] ) ),
			esc_url( $args['value'] ),
			esc_html( $this->_text( 'file_download_text', esc_html__( 'Download', 'cmb2' ) ) ),
			isset( $args['cached_id'] ) ? ' rel="' . esc_attr( $args['cached_id'] ) . '"' : '',
			esc_html( $this->_text( 'remove_text', esc_html__( 'Remove', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

	/**
	 * Outputs the file/file_list underscore Javascript templates in the footer.
	 *
	 * @since  2.2.4
	 * @return void
	 */
	public static function output_js_underscore_templates() {
		?>
		<script type="text/html" id="tmpl-cmb2-single-image">
			<div class="img-status cmb2-media-item">
				<img width="{{ data.sizeWidth }}" height="{{ data.sizeHeight }}" src="{{ data.sizeUrl }}" class="cmb-file-field-image" alt="{{ data.filename }}" title="{{ data.filename }}" />
				<p><a href="#" class="cmb2-remove-file-button" rel="{{ data.mediaField }}">{{ data.stringRemoveImage }}</a></p>
			</div>
		</script>
		<script type="text/html" id="tmpl-cmb2-single-file">
			<div class="file-status cmb2-media-item">
				<span>{{ data.stringFile }} <strong>{{ data.filename }}</strong></span>&nbsp;&nbsp; (<a href="{{ data.url }}" target="_blank" rel="external">{{ data.stringDownload }}</a> / <a href="#" class="cmb2-remove-file-button" rel="{{ data.mediaField }}">{{ data.stringRemoveFile }}</a>)
			</div>
		</script>
		<script type="text/html" id="tmpl-cmb2-list-image">
			<li class="img-status cmb2-media-item">
				<img width="{{ data.sizeWidth }}" height="{{ data.sizeHeight }}" src="{{ data.sizeUrl }}" class="cmb-file_list-field-image" alt="{{ data.filename }}">
				<p><a href="#" class="cmb2-remove-file-button" rel="{{ data.mediaField }}[{{ data.id }}]">{{ data.stringRemoveImage }}</a></p>
				<input type="hidden" id="filelist-{{ data.id }}" data-id="{{ data.id }}" name="{{ data.mediaFieldName }}[{{ data.id }}]" value="{{ data.url }}">
			</li>
		</script>
		<script type="text/html" id="tmpl-cmb2-list-file">
			<li class="file-status cmb2-media-item">
				<span>{{ data.stringFile }} <strong>{{ data.filename }}</strong></span>&nbsp;&nbsp; (<a href="{{ data.url }}" target="_blank" rel="external">{{ data.stringDownload }}</a> / <a href="#" class="cmb2-remove-file-button" rel="{{ data.mediaField }}[{{ data.id }}]">{{ data.stringRemoveFile }}</a>)
				<input type="hidden" id="filelist-{{ data.id }}" data-id="{{ data.id }}" name="{{ data.mediaFieldName }}[{{ data.id }}]" value="{{ data.url }}">
			</li>
		</script>
		<?php
	}

	/**
	 * Utility method to return an array of meta data for a registered image size
	 *
	 * Uses CMB2_Utils::get_named_size() to get the closest available named size
	 * from an array of width and height values and CMB2_Utils::get_available_image_sizes()
	 * to get the meta data associated with a named size.
	 *
	 * @since  2.2.4
	 * @param  array|string $img_size  Image size. Accepts an array of width and height (in that order)
	 * @param  string       $fallback  Size to use if the supplied named size doesn't exist
	 * @return array                   Array containing the image size meta data
	 *    $size = (
	 *      'width'  => (int) image size width
	 *      'height' => (int) image size height
	 *      'name'   => (string) e.g. 'thumbnail'
	 *    )
	 */
	static function get_image_size_data( $img_size = '', $fallback = 'thumbnail' ) {
		$data = array();

		if ( is_array( $img_size ) ) {
			$data['width']  = intval( $img_size[0] );
			$data['height'] = intval( $img_size[1] );
			$data['name']   = '';

			// Try and get the closest named size from our array of dimensions
			if ( $named_size = CMB2_Utils::get_named_size( $img_size ) ) {
				$data['name'] = $named_size;
			}
		} else {

			$image_sizes = CMB2_Utils::get_available_image_sizes();

			// The 'thumb' alias, which works elsewhere, doesn't work in the wp.media uploader
			if ( 'thumb' == $img_size ) {
				$img_size = 'thumbnail';
			}

			// Named size doesn't exist, use $fallback
			if ( ! array_key_exists( $img_size, $image_sizes ) ) {
				$img_size = $fallback;
			}

			// Get image dimensions from named sizes
			$data['width']  = intval( $image_sizes[ $img_size ]['width'] );
			$data['height'] = intval( $image_sizes[ $img_size ]['height'] );
			$data['name']   = $img_size;
		}

		return $data;
	}

	/**
	 * Filters attachment data prepared for JavaScript.
	 *
	 * Adds the url, width, height, and orientation for custom sizes to the JavaScript
	 * object returned by the wp.media uploader. Hooked to 'wp_prepare_attachment_for_js'.
	 *
	 * @since  2.2.4
	 * @param  array      $response   Array of prepared attachment data
	 * @param  int|object $attachment Attachment ID or object
	 * @param  array      $meta       Array of attachment meta data ( from wp_get_attachment_metadata() )
	 * @return array      filtered $response array
	 */
	public static function prepare_image_sizes_for_js( $response, $attachment, $meta ) {
		foreach ( CMB2_Utils::get_available_image_sizes() as $size => $info ) {

			// registered image size exists for this attachment
			if ( isset( $meta['sizes'][ $size ] ) ) {

				$attachment_url = wp_get_attachment_url( $attachment->ID );
				$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );
				$size_meta = $meta['sizes'][ $size ];

				$response['sizes'][ $size ] = array(
					'url'         => $base_url . $size_meta['file'],
					'height'      => $size_meta['height'],
					'width'       => $size_meta['width'],
					'orientation' => $size_meta['height'] > $size_meta['width'] ? 'portrait' : 'landscape',
				);
			}
		}

		return $response;
	}

}
