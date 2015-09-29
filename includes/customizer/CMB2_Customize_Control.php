<?php
/**
 * Class CMB2_Customize_Control
 */
class CMB2_Customize_Control extends WP_Customize_Control {

	/**
	 * @var CMB2_Field
	 */
	public $cmb2_field;

	/**
	 * @var bool
	 */
	public $cmb2_rendered = false;

	/**
	 * Render content
	 */
	public function render_content() {

		// These are CMB2 field types that use ->options() for things that aren't choices
		$non_options = array(
			'wysiwyg',
		);

		// Setup choices
		if ( empty( $this->choices ) && ! in_array( $this->cmb2_field->type(), $non_options ) ) {
			$this->choices = $this->cmb2_field->options();
		}

		$this->cmb2_field->value = $this->value();

		$field_type = $this->cmb2_field->type();
		$setting_id = $this->cmb2_field->_id();

		$args = array(
			'data-customize-setting-link' => $setting_id,
		);

		// Override default CMB2 input
		add_filter( 'cmb2_types_input', array( $this, 'cmb2_input' ), 10, 3 );
		add_filter( 'cmb2_types_input_wrap', array( $this, 'cmb2_input' ), 10, 3 );

		$this->cmb2_rendered = false;

		$this_type = new CMB2_Types( $this->cmb2_field );
		$this_type->{$field_type}( $args );

		// Remove override
		remove_filter( 'cmb2_types_input', array( $this, 'cmb2_input' ) );
		remove_filter( 'cmb2_types_input_wrap', array( $this, 'cmb2_input' ) );

		$this->cmb2_rendered = false;

	}

	/**
	 * Override CMB2 input with Customizer input
	 *
	 * @param string $input
	 * @param array $args
	 * @param string $field_type
	 */
	public function cmb2_input( $input, $args, $field_type ) {

		// When hooking into wrapping function, we don't normally get the
		// same final $args to work with, so we'll use the first input instead
		if ( $this->cmb2_rendered ) {
			return $this->cmb2_rendered;
		}

		if ( $field_type !== $this->cmb2_field->type() ) {
			return $input;
		}

		// Add classes to input
		$this->input_attrs['class'] = $args['class'];

		ob_start();

		// Render base customizer input
		parent::render_content();

		$this->cmb2_rendered = ob_get_clean();

		return $this->cmb2_rendered;

	}

	/**
	 * Get term choices for taxonomy fields
	 *
	 * @return array Term choices
	 */
	public function get_term_choices() {

		$choices = array();

		$taxonomy = $this->cmb2_field->args( 'taxonomy' );

		if ( ! empty( $taxonomy ) ) {
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $index => $term ) {
					$value = $term->term_id;
					$label = $term->name;

					// @todo Handle hierarchical choice display
					$choices[ $value ] = $label;
				}
			}
		}

		return $choices;

	}

}