# CMB2

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/WebDevStudios/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Travis](https://img.shields.io/travis/WebDevStudios/CMB2.svg)](https://travis-ci.org/WebDevStudios/CMB2/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/WebDevStudios/CMB2.svg)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/WebDevStudios/CMB2.svg)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Dockunit Status](https://dockunit.io/svg/WebDevStudios/CMB2?trunk)](https://dockunit.io/projects/WebDevStudios/CMB2#trunk)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

**Contributors:**      [webdevstudios](https://github.com/webdevstudios), [jtsternberg](https://github.com/jtsternberg), [gregrickaby](https://github.com/gregrickaby), [tw2113](https://github.com/tw2113), [patrickgarman](https://github.com/pmgarman), [JPry](https://github.com/JPry)
**Donate link:**       [http://webdevstudios.com](http://webdevstudios.com)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      4.3.1  
**Stable tag:**        2.1.2  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Wordpress plugin](http://img.shields.io/wordpress/plugin/v/cmb2.svg)](https://wordpress.org/plugins/cmb2/)
[![Wordpress](http://img.shields.io/wordpress/plugin/dt/cmb2.svg)](https://wordpress.org/plugins/cmb2/)
[![Wordpress rating](http://img.shields.io/wordpress/plugin/r/cmb2.svg)](https://wordpress.org/plugins/cmb2/)

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
* [CMB2 Field Type: CMB2 User Search field](https://github.com/Mte90/CMB2-User-Search-field) from [Mte90](https://github.com/Mte90): `user_search_text` adds a user-search dialog for searching/attaching other User IDs.
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
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for WordPress CMB2 library that allows the creation of columns for a better layout in the admin.
* [CMB2 Conditionals](https://github.com/jcchavezs/cmb2-conditionals) from [jcchavezs](https://github.com/jcchavezs/): Allows developers to relate fields so the display of one is conditional on the value of another.
* [CMB2 Metabox Code Generator](http://hasinhayder.github.io/cmb2-metabox-generator/) from [hasinhayder](https://github.com/hasinhayder/): Use this code generator to generate fully functional CMB2 metaboxes by visually adding fields in the designer.

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

### 2.1.2

#### Bug Fixes

* Fixes back-compatibility issue when adding fields array to the metabox registration. ([#472](https://github.com/WebDevStudios/CMB2/pull/472))

### 2.1.1

#### Enhancements

* Make all CMB2::save_fields arguments optional to fall-back to `$_POST` data. Props [JPry](https://github.com/JPry).
* New filter, `cmb2_non_repeatable_fields` for adding additional fields to the blacklist of repeatable field-types. Props [JPry](https://github.com/JPry) ([#430](https://github.com/WebDevStudios/CMB2/pull/430)).
* New recommended hoook for adding metaboxes, `cmb2_admin_init`. Most metabox registration only needs to happen if in wp-admin, so there is no reason to register them when loading the front-end (and increase the memory usage). `cmb2_init` still exists to register metaboxes that will be used on the front-end or used on both the front and back-end. Instances of `cmb2_init` in example-functions.php have been switched to `cmb2_admin_init`.
* Add `'render_row_cb'` field parameter for overriding the field render method.
* Add `'label_cb'` field parameter for overriding the field label render method.
* Allow `CMB2_Types::checkbox()` method to be more flexible for extending by taking an args array and an `$is_checked` second argument.
* More thorough unit tests. Props [pglewis](https://github.com/pglewis), ([#447](https://github.com/WebDevStudios/CMB2/pull/447),[#448](https://github.com/WebDevStudios/CMB2/pull/448)).
* Update `CMB2_Utils::image_id_from_url` to be more reliable. Props [wpscholar](https://github.com/wpscholar), ([#453](https://github.com/WebDevStudios/CMB2/pull/453)).
* `cmb2_get_option` now takes a default fallback value as a third parameter.

#### Bug Fixes

* Address issue where `'file'` and `'file_list'` field results were getting mixed. Props [augustuswm](https://github.com/augustuswm) ([#382](https://github.com/WebDevStudios/CMB2/pull/382), [#250](https://github.com/WebDevStudios/CMB2/pull/250), [#296](https://github.com/WebDevStudios/CMB2/pull/296)).
* Fix long-standing issues with radio and multicheck fields in repeatable groups losing their values when new rows are added. ([#341](https://github.com/WebDevStudios/CMB2/pull/341), [#304](https://github.com/WebDevStudios/CMB2/pull/304), [#263](https://github.com/WebDevStudios/CMB2/pull/263), [#246](https://github.com/WebDevStudios/CMB2/pull/246), [#150](https://github.com/WebDevStudios/CMB2/pull/150))
* Fixes issue where currently logged-in user's profile data would display in the "Add New User" screen fields. ([#427](https://github.com/WebDevStudios/CMB2/pull/427))
* Fixes issue where radio values/selections would not always properly transfer when shifting rows (up/down). Props [jamiechong](https://github.com/jamiechong) ([#429](https://github.com/WebDevStudios/CMB2/pull/429), [#152](https://github.com/WebDevStudios/CMB2/pull/152)).
* Fixes issue where repeatable groups display "Array" as the field values if group is left completely empty. ([#332](https://github.com/WebDevStudios/CMB2/pull/332),[#390](https://github.com/WebDevStudios/CMB2/pull/390)).
* Fixes issue with `'file_list'` fields not saving properly when in repeatable groups display. Props [jamiechong](https://github.com/jamiechong) ([#433](https://github.com/WebDevStudios/CMB2/pull/433),[#187](https://github.com/WebDevStudios/CMB2/pull/187)).
* Update `'taxonomy_radio_inline'` and `'taxonomy_multicheck_inline'` fields sanitization method to use the same method as the non-inline versions. Props [superfreund](https://github.com/superfreund) ([#454](https://github.com/WebDevStudios/CMB2/pull/454)).

**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

