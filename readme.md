# CMB2

```php
//Sample PHP for Customizer 
add_action( 'cmb2_init', function() {
    $cmb = new_cmb2_box( array(
        'id'            => 'address',
        'title'         => __( 'My Address', 'cmb2' ),
        'object_types'  => 'customizer', // Post type
        'context'       => 'normal',
        'priority'      => 800,
        'show_names'    => true, // Show field names on the left
    ) );
    // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Street Address', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_street_address',
        'type'       => 'text',
    ) );
    // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'File', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_file_1',
        'type'       => 'file',
    ) );
    // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Text URL', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_text_url',
        'type'       => 'text_url',
    ) );
     // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Text File', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_file_2',
        'type'       => 'file',
    ) );
    // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'Color', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_color',
        'type'       => 'colorpicker',
    ) );
     // Regular text field
    $cmb->add_field( array(
        'name'       => __( 'My Checkbox', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_check',
        'type'       => 'checkbox',
    ) );
    
    $cmb2 = new_cmb2_box( array(
        'id'            => 'my_custom',
        'title'         => __( 'My Custom Textarea', 'cmb2' ),
        'object_types'  => 'customizer', // Post type
        'context'       => 'normal',
        'priority'      => 200,
        'show_names'    => true, // Show field names on the left
    ) );
    // Regular text field
    $cmb2->add_field( array(
        'name'       => __( 'My Textarea', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_textarea',
        'type'       => 'textarea',
    ) );
    $cmb2->add_field( array(
        'name'       => __( 'My Textarea Small', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_textarea_small',
        'type'       => 'textarea_small',
    ) );
    $cmb2->add_field( array(
    'name'             => 'Test Radio',
    'id'               => 'wiki_test_radio',
    'type'             => 'radio',
    'show_option_none' => true,
    'options'          => array(
        'standard' => __( 'Option One', 'cmb' ),
        'custom'   => __( 'Option Two', 'cmb' ),
        'none'     => __( 'Option Three', 'cmb' ),
    ),
    ) );
    $cmb2->add_field( array(
    'name'             => 'Test Radio',
    'id'               => 'wiki_my_radio',
    'type'             => 'radio_inline',
    'show_option_none' => true,
    'options'          => array(
        'standard' => __( 'Option One', 'cmb' ),
        'custom'   => __( 'Option Two', 'cmb' ),
        'none'     => __( 'Option Three', 'cmb' ),
    ),
    ) );
    $cmb2->add_field( array(
    'name'     => 'Test Taxonomy Radio',
    'desc'     => 'Description Goes Here',
    'id'       => 'wiki_test_taxonomy_radio',
    'taxonomy' => 'category', // Enter Taxonomy Slug
    'type'     => 'taxonomy_radio',
    // Optional:
    'options' => array(
        'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
        ),
    ) );
    $cmb->add_field( array(
    'name'     => 'Test Taxonomy Radio Inline',
    'desc'     => 'Description Goes Here',
    'id'       => 'wiki_test_taxonomy_radio_inline',
    'taxonomy' => 'category', // Enter Taxonomy Slug
    'type'     => 'taxonomy_radio_inline',
    // Optional:
    'options' => array(
        'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
        ),
    ) );
    
    /* Pauli LOC */
    $pauli = new_cmb2_box( array(
        'id'            => 'pauli_custom',
        'title'         => __( 'Info for Pauli', 'cmb2' ),
        'object_types'  => array( 'customizer' ), // Post type
        'context'       => 'normal',
        'priority'      => 200,
        'show_names'    => true, // Show field names on the left
    ) );
    // Regular text field
    $pauli->add_field( array(
        'name'       => __( 'Age', 'cmb2' ),
        'desc'       => __( 'field description (optional)', 'cmb2' ),
        'id'         => 'cmb_pauli_age',
        'type'       => 'text_url',
    ) );
    $pauli->add_field( array(
        'name'     => 'Test Taxonomy Select',
        'desc'     => 'Description Goes Here',
        'id'       => 'wiki_test_taxonomy_select',
        'taxonomy' => 'category', //Enter Taxonomy Slug
        'type'     => 'taxonomy_select',
    ) );
    $pauli->add_field( array(
        'name'             => 'Test Select',
        'desc'             => 'Select an option',
        'id'               => 'wiki_test_select',
        'type'             => 'select',
        'show_option_none' => true,
        'default'          => 'custom',
        'options'          => array(
            'standard' => __( 'Option One', 'cmb' ),
            'custom'   => __( 'Option Two', 'cmb' ),
            'none'     => __( 'Option Three', 'cmb' ),
        ),
    ) );
    
    /* Pauli LOC */
    $time_custom = new_cmb2_box( array(
        'id'            => 'time_custom',
        'title'         => __( 'Time Custon', 'cmb2' ),
        'object_types'  => array( 'customizer' ), // Post type
        'context'       => 'normal',
        'priority'      => 200,
        'show_names'    => true, // Show field names on the left
    ) );
    $time_custom->add_field( array(
        'name' => 'Test Date Picker',
        'id' => 'wiki_test_texttime',
        'type' => 'text_time',
        'time_format' => 'h:i:s A',
    ) );
    $time_custom->add_field( array(
        'name'    => 'Test Multi Checkbox',
        'desc'    => 'field description (optional)',
        'id'      => 'wiki_test_multicheckbox',
        'type'    => 'multicheck',
        'options' => array(
            'check1' => 'Check One',
            'check2' => 'Check Two',
            'check3' => 'Check Three',
        )
    ) );
    $time_custom->add_field( array(
        'name'    => 'Test Multi Checkbox',
        'desc'    => 'field description (optional)',
        'id'      => 'wiki_test_multicheckboxes',
        'type'    => 'multicheck_inline',
        'options' => array(
            'check1' => 'Check One',
            'check2' => 'Check Two',
            'check3' => 'Check Three',
        )
    ) );
    
    /* Pauli LOC */
    $tax_multi = new_cmb2_box( array(
        'id'            => 'tax_multi',
        'title'         => __( 'Taxonomy Multi', 'cmb2' ),
        'object_types'  => array( 'customizer' ), // Post type
        'context'       => 'normal',
        'priority'      => 200,
        'show_names'    => true, // Show field names on the left
    ) );
    $tax_multi->add_field( array(
        'name'     => 'Test Taxonomy Multicheck',
        'desc'     => 'Description Goes Here',
        'id'       => 'wiki_test_taxonomy_multicheck',
        'taxonomy' => 'category', //Enter Taxonomy Slug
        'type'     => 'taxonomy_multicheck',
        // Optional:
        'options' => array(
            'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
        ),
    ) );

} );

```

```php
/* Test Outputs */
<?php echo get_option( 'cmb_street_address', 'd' ); ?>
<br />
<?php echo get_option( 'cmb_street_field', 'd' ); ?>
<br />
<?php echo get_option( 'cmb_check' ); ?>
<br />
<?php echo get_option( 'cmb_textarea' ); ?>
<br />
<?php echo get_option( 'cmb_textarea_small' ); ?>
<br />
<?php echo get_option( 'wiki_test_radio' ); ?>
<br />
<?php echo get_option( 'wiki_test_taxonomy_radio' ); ?>
<br />
<?php echo get_option( 'wiki_test_taxonomy_radio_inline' ); ?>
<br />
<?php echo get_option( 'cmb_pauli_age' ); ?>
<br />
<?php echo get_option( 'wiki_test_taxonomy_select' ); ?>
<br />
<?php echo get_option( 'wiki_test_select' ); ?>
```

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/WebDevStudios/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Travis](http://img.shields.io/travis/WebDevStudios/CMB2.svg?style=flat)](https://travis-ci.org/WebDevStudios/CMB2)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/WebDevStudios/CMB2.svg?style=flat)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

**Contributors:**      [webdevstudios](https://github.com/webdevstudios), [jtsternberg](https://github.com/jtsternberg), [gregrickaby](https://github.com/gregrickaby), [tw2113](https://github.com/tw2113), [patrickgarman](https://github.com/pmgarman), [JPry](https://github.com/JPry)
**Donate link:**       [http://webdevstudios.com](http://webdevstudios.com)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      4.3  
**Stable tag:**        2.1.0  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Wordpress plugin](http://img.shields.io/wordpress/plugin/v/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)
[![Wordpress](http://img.shields.io/wordpress/plugin/dt/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)
[![Wordpress rating](http://img.shields.io/wordpress/plugin/r/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)

Complete contributors list found here: [github.com/WebDevStudios/CMB2/graphs/contributors](https://github.com/WebDevStudios/CMB2/graphs/contributors)

## Description

CMB2 is a developer's toolkit for building metaboxes, custom fields, and forms for WordPress that will blow your mind.

**[Download plugin on wordpress.org](http://wordpress.org/plugins/cmb2/)**

CMB2 is a complete rewrite of [Custom Metaboxes and Fields for WordPress](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress). To get started, please follow the examples in the included `example-functions.php` file and have a look at the [basic usage instructions](https://github.com/WebDevStudios/CMB2/wiki/Basic-Usage).

You can see a list of available field types [here](https://github.com/WebDevStudios/CMB2/wiki/Field-Types#types).

## Features:

* Create metaboxes to be used on post edit screens.
* [Create forms to be used on an options pages](https://github.com/WebDevStudios/CMB2/wiki/Using-CMB-to-create-an-Admin-Theme-Options-Page).
* Create forms to handle user meta and display them on user profile add/edit pages.
* [Flexible API that allows you to use CMB forms almost anywhere, even on the front-end](https://github.com/WebDevStudios/CMB2/wiki/Bringing-Metaboxes-to-the-Front-end).
* [Several field types are included](https://github.com/WebDevStudios/CMB2/wiki/Field-Types).
* [Custom API hook that allows you to create your own field types](https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-field-types).
* There are numerous hooks and filters, allowing you to modify many aspects of the library (without editing it directly).
* Repeatable fields for most field types are supported, as well as repeatable field groups.
* CMB2 is safe to bundle with any project. It will only load the newest version in the system.

## Translation
* Thanks to many in the CMB2 community and to our friends at [wp-translations.org](http://wp-translations.org/project/cmb2/), we have a good start on several translations for CMB2. Please feel free to [work with wp-translations.org](http://wp-translations.org/project/cmb2/) to provide even more!

## 3rd Party Resources

#### Custom Field Types
* [CMB2 Field Type: CMB Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) from [coreymcollins](https://github.com/coreymcollins): `custom_attached_posts`, for attaching posts to a page.
* [CMB2 Field Type: CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field): `post_search_text` adds a post-search dialog for searching/attaching other post IDs.
* [CMB2 Field Type: CMB2 RGBa Colorpicker](https://github.com/JayWood/CMB2_RGBa_Picker) from [JayWood](https://github.com/JayWood): `rgba_colorpicker ` adds a color picker that supports RGBa, (RGB with transparency (alpha) value).
* [CMB2 Field Type: Google Maps](https://github.com/mustardBees/cmb_field_map) from [mustardBees](https://github.com/mustardBees): Custom field type for Google Maps.
	> The `pw_map` field stores the latitude/longitude values which you can then use to display a map in your theme.

* [CMB2 Field Type: Select2](https://github.com/mustardBees/cmb-field-select2) from [mustardBees](https://github.com/mustardBees): Custom field types which use the [Select2](http://ivaynberg.github.io/select2/) script:

	> 1. The `pw_select field` acts much like the default select field. However, it adds typeahead-style search allowing you to quickly make a selection from a large list
	> 2. The `pw_multiselect` field allows you to select multiple values with typeahead-style search. The values can be dragged and dropped to reorder

* [CMB Field Type: Slider](https://github.com/qmatt/cmb2-field-slider) from [mattkrupnik](https://github.com/mattkrupnik/): Adds a jQuery UI Slider field.
* [WDS CMB2 Date Range Field](https://github.com/WebDevStudios/CMB2-Date-Range-Field) from [dustyf](https://github.com/dustyf) of [WebDevStudios](https://github.com/WebDevStudios): Adds a date range field.
* [CMB2 Remote Image Select](https://github.com/WebDevStudios/CMB2-Remote-Image-Select-Field) from [JayWood](https://github.com/JayWood) of [WebDevStudios](https://github.com/WebDevStudios): Allows users to enter a URL in a text field and select a single image for use in post meta. Similar to Facebook's featured image selector.
* [CMB Field Type: Sorter](https://wordpress.org/plugins/cmb-field-type-sorter/): This plugin gives you two CMB field types based on the Sorter script.

#### Other Helpful Resources
* [Taxonomy_MetaData](https://github.com/jtsternberg/Taxonomy_MetaData#to-use-taxonomy_metadata-with-custom-metaboxes-and-fields): WordPress Helper Class for saving pseudo-metadata for taxonomy terms. Includes an extended class for using CMB to generate the actual form fields.
* [CMB2 Taxonomy](https://github.com/jcchavezs/cmb2-taxonomy) from [jcchavezs](https://github.com/jcchavezs/): Similar to Taxonomy_MetaData, but uses a custom table for taxonomy term meta storage.
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.
* [WDS-Simple-Page-Builder](https://github.com/WebDevStudios/WDS-Simple-Page-Builder): Uses existing template parts in the currently-active theme to build a customized page with rearrangeable elements. Built with CMB2.
* [CMB2 Example Theme](https://github.com/WebDevStudios/CMB2-Example-Theme): Demonstrate how to include CMB2 in your theme, as well as some cool tips and tricks.
* [facetwp-cmb2](https://github.com/FacetWP/facetwp-cmb2): FacetWP integration with CMB2.
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for Wordpress CMB2 library that allows the creation of columns for a better layout in the admin.

## Contribution
All contributions welcome. If you would like to submit a pull request, please check out the [trunk branch](https://github.com/WebDevStudios/CMB2/tree/trunk) and pull request against it. Please read the [CONTRIBUTING](https://github.com/WebDevStudios/CMB2/blob/master/CONTRIBUTING.md) doc for more details.

A complete list of all our awesome contributors found here: [github.com/WebDevStudios/CMB2/graphs/contributors](https://github.com/WebDevStudios/CMB2/graphs/contributors)

## Links
* [Project Homepage](http://cmb2.io)
* [Github project page](https://github.com/webdevstudios/CMB2)
* [Documentation (GitHub wiki)](https://github.com/webdevstudios/CMB2/wiki)

## Installation

If installing the plugin from wordpress.org:

1. Upload the entire `/CMB2` directory to the `/wp-content/plugins/` directory.
2. Activate CMB2 through the 'Plugins' menu in WordPress.
2. Copy (and rename if desired) `example-functions.php` into to your theme or plugin's directory.
2. Edit to only include the fields you need and rename the functions.
4. Profit.

If including the library in your plugin or theme:

1. Place the CMB2 directory inside of your theme or plugin.
2. Copy (and rename if desired) `example-functions.php` into a folder *above* the CMB2 directory OR copy the entirety of its contents to your theme's `functions.php` file.
2. Edit to only include the fields you need and rename the functions (CMB2 directory should be left unedited in order to easily update the library).
4. Profit.

## Most Recent Changes

### 2.1.0

#### Bug Fixes

* Fix user fields not saving. Props [achavez](https://github.com/achavez), ([#417](https://github.com/WebDevStudios/CMB2/pull/417)).

### 2.0.9

#### Enhancements

* Updated/Added many translations. Props [fxbenard](https://github.com/fxbenard), ([#203](https://github.com/WebDevStudios/CMB2/pull/344)) and [Mte90](https://github.com/Mte90) for the Italian translation.
* Updated `'file_list'` field type to have a more intutive selection in the media library, and updated the 'Use file' text in the button. Props [SteveHoneyNZ](https://github.com/SteveHoneyNZ) ([#357](https://github.com/WebDevStudios/CMB2/pull/357), [#358](https://github.com/WebDevStudios/CMB2/pull/358)).
* `'closed'` group field option parameter introduced in order to set the groups as collapsed by default. Requested in [#391](https://github.com/WebDevStudios/CMB2/issues/391).
* Added `"cmb2_{$object_type}_process_fields_{$cmb_id}"` hook for hooking in and modifying the metabox or fields before the fields are processed/sanitized for saving.
* Added Comment Metabox support. Props [GregLancaster71](https://github.com/GregLancaster71) ([#238](https://github.com/WebDevStudios/CMB2/pull/238), [#244](https://github.com/WebDevStudios/CMB2/pull/244)).
* New "cmb2_{$field_id}_is_valid_img_ext" filter for determining if a field value has a valid image file-type extension.

#### Bug Fixes

* `'multicheck_inline'`, `'taxonomy_radio_inline'`, and `'taxonomy_multicheck_inline'` field types were not outputting anything since it's value was not being returned. Props [ediamin](https://github.com/ediamin), ([#367](https://github.com/WebDevStudios/CMB2/pull/367), ([#405](https://github.com/WebDevStudios/CMB2/pull/405)).
* `'hidden'` type fields were not honoring the `'show_on_cb'` callback. Props [JPry](https://github.com/JPry), ([commits](https://github.com/WebDevStudios/CMB2/compare/5a4146eec546089fbe1a1c859d680dfda3a86ee2...1ef5ef1e3b2260ab381090c4abe9dc7234cfa0a6)).
* Fixed: There was no minified cmb2-front.min.css file.
* Fallback for fatal error with invalid timezone. Props [ryanduff](https://github.com/ryanduff) ([#385](https://github.com/WebDevStudios/CMB2/pull/385)).
* Fix issues with deleting a row from repeatable group. Props [yuks](https://github.com/yuks) ([#387](https://github.com/WebDevStudios/CMB2/pull/387)).
* Ensure value passed to `strtotime` in `make_valid_time_stamp` is cast to a string. Props [vajrasar](https://github.com/vajrasar) ([#389](https://github.com/WebDevStudios/CMB2/pull/389)).
* Fixed issue with Windows IIS and bundling CMB2 in the theme. Props [DevinWalker](https://github.com/DevinWalker), ([#400](https://github.com/WebDevStudios/CMB2/pull/400), [#401](https://github.com/WebDevStudios/CMB2/pull/401))

**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

