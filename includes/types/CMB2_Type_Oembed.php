<?php
/**
 * CMB oembed field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Oembed extends CMB2_Type_Text {

	public function render() {
		$field = $this->field;

		$meta_value = trim( $field->escaped_value() );

		$oembed = ! empty( $meta_value )
			? cmb2_ajax()->get_oembed( array(
				'url'         => $field->escaped_value(),
				'object_id'   => $field->object_id,
				'object_type' => $field->object_type,
				'oembed_args' => array( 'width' => '640' ),
				'field_id'    => $this->_id(),
			) )
			: '';

		return parent::render( array(
			'class'           => 'cmb2-oembed regular-text',
			'data-objectid'   => $field->object_id,
			'data-objecttype' => $field->object_type,
		) )
		. '<p class="cmb-spinner spinner" style="display:none;"></p>'
		. '<div id="'. $this->_id( '-status' ). '" class="cmb2-media-status ui-helper-clearfix embed_wrap">' . $oembed . '</div>';
	}

}
