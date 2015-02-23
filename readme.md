# CMB2

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/WebDevStudios/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Wordpress plugin](http://img.shields.io/wordpress/plugin/v/cmb2.svg?style=flat)]()
[![Wordpress](http://img.shields.io/wordpress/plugin/dt/cmb2.svg?style=flat)]()
[![Wordpress rating](http://img.shields.io/wordpress/plugin/r/cmb2.svg?style=flat)]()
[![Travis](http://img.shields.io/travis/WebDevStudios/CMB2.svg?style=flat)]()
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/WebDevStudios/CMB2.svg?style=flat)]()
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=master)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

**Contributors:**      [webdevstudios](https://github.com/webdevstudios), [jtsternberg](https://github.com/jtsternberg), [gregrickaby](https://github.com/gregrickaby), [tw2113](https://github.com/tw2113), [patrickgarman](https://github.com/pmgarman)  
**Donate link:**       [http://webdevstudios.com](http://webdevstudios.com)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      4.1.0  
**Stable tag:**        2.0.2  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

Complete contributors list found here: [github.com/WebDevStudios/CMB2/graphs/contributors](https://github.com/WebDevStudios/CMB2/graphs/contributors)

## Description

CMB2 is a metabox, custom fields, and forms library for WordPress that will blow your mind.

**[Plugin available on wordpress.org](http://wordpress.org/plugins/cmb2/)**

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

* [CMB2 Field Type: Gallery](https://github.com/mustardBees/cmb-field-gallery) from [mustardBees](https://github.com/mustardBees): Adds a WordPress gallery field.
* [CMB Field Type: Slider](https://github.com/qmatt/cmb2-field-slider) from [mattkrupnik](https://github.com/mattkrupnik/): Adds a jQuery UI Slider field.

#### Other Helpful Resources
* [Taxonomy_MetaData](https://github.com/jtsternberg/Taxonomy_MetaData#to-use-taxonomy_metadata-with-custom-metaboxes-and-fields): WordPress Helper Class for saving pseudo-metadata for taxonomy terms. Includes an extended class for using CMB to generate the actual form fields.
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.

## Contribution
All contributions welcome. If you would like to submit a pull request, please check out the [trunk branch](https://github.com/WebDevStudios/CMB2/tree/trunk) and pull request against it. Please read the [CONTRIBUTING](https://github.com/WebDevStudios/CMB2/blob/master/CONTRIBUTING.md) doc for more details.

A complete list of all our awesome contributors found here: [github.com/WebDevStudios/CMB2/graphs/contributors](https://github.com/WebDevStudios/CMB2/graphs/contributors)

## Links
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

## 2.0.2

### Enhancements

* Use the more appropriate `add_meta_boxes` hook for hooking in metaboxes to post-edit screen. Thanks [@inspiraaz](https://github.com/inspiraaz) for reporting. ([#161](https://github.com/WebDevStudios/CMB2/issues/161))
* Add a `row_classes` field param which allows you to add additional classes to the cmb-row wrap. This parameter can take a string, or array, or can take a callback that returns a string or array. The callback will receive `$field_args` as the first argument, and the CMB2_Field `$field` object as the second argument. Reported/requested in [#68](https://github.com/WebDevStudios/CMB2/issues/68).
* New constant, `CMB2_LOADED`, which you can use to check if CMB2 is loaded for your plugins/themes with CMB2 dependency.
* New hooks, [`cmb2_init_before_hookup` and `cmb2_after_init`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions).
* New API for adding metaboxes and fields, demonstrated in [`example-functions.php`](https://github.com/WebDevStudios/CMB2/blob/master/example-functions.php). In keeping with backwards-compatibility, the `cmb2_meta_boxes` filter method will still work, but is not recommended. New API includes `new_cmb2_box` helper function to generate a new metabox, and returns a `$cmb` object to add new fields (via the `CMB2::add_field()` and `CMB2::add_group_field()` methods).
* New CMB2 method, [`CMB2::remove_field()`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions/cmb2_init_%24cmb_id-remove-field.php).
* New CMB2_Boxes method, [`CMB2_Boxes::remove()`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions/cmb2_init_before_hookup-remove-cmb2-metabox.php).
* When clicking on a file/image in the `file`, or `file_list` type, the media modal will open with that image selected. Props [johnsonpaul1014](https://github.com/johnsonpaul1014), ([#120](https://github.com/WebDevStudios/CMB2/pull/120)).


**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

