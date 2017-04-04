<?php
/**
 * CMB wysiwyg field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @method string _id()
 * @method string _desc()
 */
class CMB2_Type_Wysiwyg extends CMB2_Type_Textarea {

	/**
	 * Handles outputting a 'wysiwyg' element
	 * @since  1.1.0
	 * @return string Form wysiwyg element
	 */
	public function render() {
		$field = $this->field;
		$a = $this->parse_args( 'wysiwyg', array(
			'id'      => $this->_id(),
			'value'   => $field->escaped_value( 'stripslashes' ),
			'desc'    => $this->_desc( true ),
			'options' => $field->options(),
		) );

		if ( ! $field->group ) {
			return $this->rendered( $this->get_wp_editor( $a ) . $a['desc'] );
		}

		// wysiwyg fields in a group need some special handling.

		$field->add_js_dependencies( 'wp-util' );
		$field->add_js_dependencies( 'cmb2-wysiwyg' );

		// Hook in our template-output to the footer.
		add_action( is_admin() ? 'admin_footer' : 'wp_footer', array( $this, 'add_wysiwyg_template_for_group' ) );

		return $this->rendered(
			sprintf( '<div class="cmb2-wysiwyg-wrap">%s', parent::render( array(
				'class'         => 'cmb2_textarea cmb2-wysiwyg-placeholder',
				'data-groupid'  => $field->group->id(),
				'data-iterator' => $field->group->index,
				'data-fieldid'  => $field->id( true ),
				'desc'          => '</div>' . $this->_desc( true ),
			) ) )
		);
	}

	protected function get_wp_editor( $args ) {
		ob_start();
		wp_editor( $args['value'], $args['id'], $args['options'] );
		return ob_get_clean();
	}

	public function add_wysiwyg_template_for_group() {
		$group_id = $this->field->group->id();
		$field_id = $this->field->id( true );
		$options  = $this->field->options();
		$options['textarea_name'] = 'cmb2_n_' . $group_id . $field_id;

		// Initate the editor with special id/value/name so we can retrieve the options in JS.
		$editor = $this->get_wp_editor( array(
			'value'   => 'cmb2_v_' . $group_id . $field_id,
			'id'      => 'cmb2_i_' . $group_id . $field_id,
			'options' => $options,
		) );

		// Then replace the special id/value/name with underscore placeholders.
		$editor = str_replace( array(
			'cmb2_n_' . $group_id . $field_id,
			'cmb2_v_' . $group_id . $field_id,
			'cmb2_i_' . $group_id . $field_id,
			), array(
			'{{ data.name }}',
			'{{{ data.value }}}',
			'{{ data.id }}',
		), $editor );

		// And put the editor instance in a JS template wrapper.
		echo '<script type="text/template" id="tmpl-cmb2-wysiwyg-' . $group_id . '-' . $field_id . '">';
		// Need to wrap the template in a wrapper div w/ specific data attributes which will be used when adding/removing rows.
		echo '<div class="cmb2-wysiwyg-inner-wrap" data-iterator="{{ data.iterator }}" data-groupid="' . $group_id . '" data-id="' . $field_id . '">' . $editor . '</div>';
		echo '</script>';
	}

}
