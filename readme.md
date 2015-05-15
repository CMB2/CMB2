# CMB2

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/WebDevStudios/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Travis](http://img.shields.io/travis/WebDevStudios/CMB2.svg?style=flat)](https://travis-ci.org/WebDevStudios/CMB2)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/WebDevStudios/CMB2.svg?style=flat)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

**Contributors:**      [webdevstudios](https://github.com/webdevstudios), [jtsternberg](https://github.com/jtsternberg), [gregrickaby](https://github.com/gregrickaby), [tw2113](https://github.com/tw2113), [patrickgarman](https://github.com/pmgarman)  
**Donate link:**       [http://webdevstudios.com](http://webdevstudios.com)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      4.2.1  
**Stable tag:**        2.0.6  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Wordpress plugin](http://img.shields.io/wordpress/plugin/v/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)
[![Wordpress](http://img.shields.io/wordpress/plugin/dt/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)
[![Wordpress rating](http://img.shields.io/wordpress/plugin/r/cmb2.svg?style=flat)](https://wordpress.org/plugins/cmb2/)

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
* [CMB2 Taxonomy](https://github.com/jcchavezs/cmb2-taxonomy) from [jcchavezs](https://github.com/jcchavezs/): Similar to Taxonomy_MetaData, but uses a custom table for taxonomy term meta storage.
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

## Most Recent Changes - 2.0.6

#### Enhancements

* New metabox/form parameter, `show_on_cb`, allows you to conditionally display a cmb metabox/form via a callback. The `$cmb` object gets passed as a parameter to the callback. This complements the `'show_on_cb'` parameter that already exists for individual fields. Using this callback is similar to using the `'cmb2_show_on'` filter, but only applies to that specific metabox and it is recommended to use this callback instead as it minimizes th risk that your filter will affect other metaboxes.
* Taxonomy types no longer save a value. The value getting saved was causing confusion and is not meant to be used. To use the saved taxonomy data, you need to use the WordPress term api, `get_the_terms `, `get_the_term_list`, etc.
* Add `'multiple'` field parameter to store values in individual rows instead of serialized array. Will only work if field is not repeatable or a repeatable group. Props [JohnyGoerend](https://github.com/JohnyGoerend). ([#262](https://github.com/WebDevStudios/CMB2/pull/262), [#206](https://github.com/WebDevStudios/CMB2/issues/206), [#45](https://github.com/WebDevStudios/CMB2/issues/45)).
* Portuguese (Brazil) translation provided by [@lucascdsilva](https://github.com/lucascdsilva) - [#293](https://github.com/WebDevStudios/CMB2/pull/293).
* Spanish (Spain) translation updated by [@yivi](https://github.com/yivi) - [#272](https://github.com/WebDevStudios/CMB2/pull/272).
* Added group field callback parameters, `'before_group'`, `'before_group_row'`, `'after_group_row'`, `'after_group'` to complement the `'before_row'`, `'before'`, `'after'`, `'after_row'` field parameters.
* Better styling for `title` fields and `title` descriptions on options pages.
* Add a `sanitization_cb` field parameter check for the `group` field type.
* Better function/file doc-blocks to provide better documentation for automated documentation tools. See: [cmb2.io/api](http://cmb2.io/api/).
* `cmb2_print_metabox_form`, `cmb2_metabox_form`, and `cmb2_get_metabox_form` helper functions now accept two new parameters:
	* an `'object_type'` parameter to explictly set that in the `$cmb` object.
	* an `'enqueue_js'` parameter to explicitly disable the CMB JS enqueue. This is handy if you're not planning on using any of the fields which require JS (like color/date pickers, wysiwyg, file, etc).

#### Bug Fixes

* Fix issue with oembed fields in repeatable groups where changing video changed it for all fields in a group.
* Fix empty arrays (like in the group field) saving as a value.
* Move `'cmb2_override_meta_value'` and `"cmb2_override_{$field_id}_meta_value"` filters to the `CMB2_Field::get_data()` method so that the filters are applied every time the data is requested. **THIS IS A BREAKING CHANGE:** The parameters for those filters have changed a bit. Previously, the filters accepted 5 arguments, `$value`, `$object_id`, `$field_args`, `$object_type`, `$field`. They have changed to accept 4 arguments instead, `$value`, `$object_id`, `$args`, `$field`, where `$args` is an array that contains the following:
	* @type string $type     The current object type
	* @type int    $id       The current object ID
	* @type string $field_id The ID of the field being requested
	* @type bool   $repeat   Whether current field is repeatable
	* @type bool   $single   Whether current field is a single database row

**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

