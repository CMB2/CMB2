<?php
// Include & setup custom metabox and fields
$prefix = '_cmb_'; // start with an underscore to hide fields from custom fields list
add_filter( 'cmb_meta_boxes', 'be_sample_metaboxes' );
function be_sample_metaboxes( $meta_boxes ) {
	global $prefix;
	$meta_boxes[] = array(
		'id' => 'test_metabox',
		'title' => 'Test Metabox',
		'pages' => array('page'), // post type
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => 'Test Text',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_text',
				'type' => 'text'
			),
			array(
				'name' => 'Test Text Small',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textsmall',
				'type' => 'text_small'
			),
			array(
				'name' => 'Test Text Medium',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textmedium',
				'type' => 'text_medium'
			),
			array(
				'name' => 'Test Date Picker',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textdate',
				'type' => 'text_date'
			),
			array(
				'name' => 'Test Date Picker (UNIX timestamp)',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textdate_timestamp',
				'type' => 'text_date_timestamp'
			),			
			array(
	            'name' => 'Test Time',
	            'desc' => 'field description (optional)',
	            'id' => $prefix . 'test_time',
	            'type' => 'text_time'
	        ),			
			array(
				'name' => 'Test Money',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textmoney',
				'type' => 'text_money'
			),
			array(
				'name' => 'Test Text Area',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textarea',
				'type' => 'textarea'
			),
			array(
				'name' => 'Test Text Area Small',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textareasmall',
				'type' => 'textarea_small'
			),
			array(
				'name' => 'Test Text Area Code',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_textarea_code',
				'type' => 'textarea_code'
			),
			array(
				'name' => 'Test Title Weeeee',
				'desc' => 'This is a title description',
				'type' => 'title',
				'id' => $prefix . 'test_title'
			),
			array(
				'name' => 'Test Select',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_select',
				'type' => 'select',
				'options' => array(
					array('name' => 'Option One', 'value' => 'standard'),
					array('name' => 'Option Two', 'value' => 'custom'),
					array('name' => 'Option Three', 'value' => 'none')				
				)
			),
			array(
				'name' => 'Test Radio inline',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_radio',
				'type' => 'radio_inline',
				'options' => array(
					array('name' => 'Option One', 'value' => 'standard'),
					array('name' => 'Option Two', 'value' => 'custom'),
					array('name' => 'Option Three', 'value' => 'none')				
				)
			),
			array(
				'name' => 'Test Radio',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_radio',
				'type' => 'radio',
				'options' => array(
					array('name' => 'Option One', 'value' => 'standard'),
					array('name' => 'Option Two', 'value' => 'custom'),
					array('name' => 'Option Three', 'value' => 'none')				
				)
			),
			array(
				'name' => 'Test Taxonomy Radio',
				'desc' => 'Description Goes Here',
				'id' => $prefix . 'text_taxonomy_radio',
				'taxonomy' => '', //Enter Taxonomy Slug
				'type' => 'taxonomy_radio',	
			),
			array(
				'name' => 'Test Taxonomy Select',
				'desc' => 'Description Goes Here',
				'id' => $prefix . 'text_taxonomy_select',
				'taxonomy' => '', //Enter Taxonomy Slug
				'type' => 'taxonomy_select',	
			),
			array(
				'name' => 'Test Checkbox',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_checkbox',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Test Multi Checkbox',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_multicheckbox',
				'type' => 'multicheck',
				'options' => array(
					'check1' => 'Check One',
					'check2' => 'Check Two',
					'check3' => 'Check Three',
				)
			),
			array(
				'name' => 'Test wysiwyg',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_wysiwyg',
				'type' => 'wysiwyg',
				'options' => array(
					'textarea_rows' => 5,
				)
			),
			array(
				'name' => 'Test Image',
				'desc' => 'Upload an image or enter an URL.',
				'id' => $prefix . 'test_image',
				'type' => 'file'
			),
		)
	);

	$meta_boxes[] = array(
		'id' => 'about_page_metabox',
		'title' => 'About Page Metabox',
		'pages' => array('page'), // post type
		'show_on' => array( 'key' => 'id', 'value' => array( 2 ) ), // specific post ids to display this metabox
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => 'Test Text',
				'desc' => 'field description (optional)',
				'id' => $prefix . 'test_text',
				'type' => 'text'
			),
		)
	);
	
	return $meta_boxes;
}


// Initialize the metabox class
add_action( 'init', 'be_initialize_cmb_meta_boxes', 9999 );
function be_initialize_cmb_meta_boxes() {
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( 'init.php' );
	}
}