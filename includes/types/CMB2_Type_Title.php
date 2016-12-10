<?php
/**
 * CMB title field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Title extends CMB2_Type_Base {

	/**
	 * Handles outputting an 'title' element
	 * @return string Heading element
	 */
	public function render() {
		$name = $this->field->args( 'name' );
		$tag  = 'span';

		if ( ! empty( $name ) ) {
			$tag = $this->field->object_type == 'post' ? 'h5' : 'h3';
		}

		$a = $this->parse_args( 'title', array(
			'tag'   => $tag,
			'class' => empty( $name ) ? 'cmb2-metabox-title-anchor' : 'cmb2-metabox-title',
			'name'  => $name,
			'desc'  => $this->_desc( true ),
			'id'    => str_replace( '_', '-', sanitize_html_class( $this->field->id() ) ),
		) );

		return $this->rendered(
			sprintf(
				'<%1$s %2$s>%3$s</%1$s>%4$s',
				$a['tag'],
				$this->concat_attrs( $a, array( 'tag', 'name', 'desc' ) ),
				$a['name'],
				$a['desc']
			)
		);
	}

}
