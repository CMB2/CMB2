<?php
/**
 * CMB taxonomy_multicheck field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Taxonomy_Multicheck extends CMB2_Type_Taxonomy_Base {

	public function render() {
		$field = $this->field;
		$names = $this->get_object_terms();

		$saved_terms = is_wp_error( $names ) || empty( $names )
			? $field->get_default()
			: wp_list_pluck( $names, 'slug' );
		$terms       = $this->get_terms();
		$name        = $this->_name() . '[]';
		$options     = ''; $i = 1;

		if ( ! $terms ) {
			$options .= sprintf( '<li><label>%s</label></li>', esc_html( $this->_text( 'no_terms_text', esc_html__( 'No terms', 'cmb2' ) ) ) );
		} else {

			foreach ( $terms as $term ) {
				$args = array(
					'value' => $term->slug,
					'label' => $term->name,
					'type' => 'checkbox',
					'name' => $name,
				);

				if ( is_array( $saved_terms ) && in_array( $term->slug, $saved_terms ) ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->types->list_input( $args, $i );
				$i++;
			}
		}

		$classes = false === $field->args( 'select_all_button' )
			? 'cmb2-checkbox-list no-select-all cmb2-list'
			: 'cmb2-checkbox-list cmb2-list';

		return $this->rendered(
			$this->types->radio( array( 'class' => $classes, 'options' => $options ), 'taxonomy_multicheck' )
		);
	}
}
