<?php
/*
Plugin Name: CMB2 Integration Tests Fields
Plugin Author: CMB2
Description: Configures test fields for CMB2's internal integration tests.
*/

// Disable Gutenberg block editor for E2E tests — CMB2 metaboxes render
// reliably in the classic editor without complex panel/scroll handling.
add_filter( 'use_block_editor_for_post', '__return_false' );

// DRY up the creation of the fields with some type-derived defaults, which can be overridden.
class CMB2_Integration_Box {
	protected $box;
	protected $hook;

	public function __construct($args = []) {
		$this->hook = 'cmb2_integration_tests_handle_box_' . md5(wp_generate_password());
		add_action('cmb2_admin_init', function () use ($args) {
			$this->box = new_cmb2_box($args);
			do_action($this->hook, $this->box);
		});
	}

	public function addField($type, $args = []) {
		$args = array_merge([
			'type' => $type,
			'id' => 'cmb2_integration_tests_field_' . $type,
			'name' => $type,
			'desc' => $type . ' Description',
		], $args);

		add_action($this->hook, function($box) use ($args) {
			$box->add_field($args);
		});
	}
}

add_action('plugins_loaded', function () {
	$prefix = 'cmb2_integration_tests_';

	$closed = new CMB2_Integration_Box([
		'id'            => $prefix . 'default_closed',
		'title'         => 'Default Closed',
		'object_types'  => [ 'post' ],
		'closed' => true,
	]);
	$closed->addField('text');
});
