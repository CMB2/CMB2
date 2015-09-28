<?php
/**
 * Class CMB2_Customize_Taxonomy_Control
 */
class CMB2_Customize_Taxonomy_Control extends CMB2_Customize_Control {

	/**
	 * Setup choices and render content
	 */
	public function render_content() {

		$this->choices = $this->get_term_choices();

		parent::render_content();

	}

}