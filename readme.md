# CMB2

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/WebDevStudios/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Travis](https://img.shields.io/travis/WebDevStudios/CMB2.svg)](https://travis-ci.org/WebDevStudios/CMB2/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/WebDevStudios/CMB2.svg)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/WebDevStudios/CMB2.svg)](https://scrutinizer-ci.com/g/WebDevStudios/CMB2/?branch=trunk)
[![Dockunit Status](https://dockunit.io/svg/WebDevStudios/CMB2?trunk)](https://dockunit.io/projects/WebDevStudios/CMB2#trunk)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

![CMB2](https://plugins.trac.wordpress.org/export/HEAD/cmb2/assets/banner-1544x500.png)

**Contributors:**      [webdevstudios](https://github.com/webdevstudios), [jtsternberg](https://github.com/jtsternberg), [gregrickaby](https://github.com/gregrickaby), [tw2113](https://github.com/tw2113), [patrickgarman](https://github.com/pmgarman), [JPry](https://github.com/JPry)
**Donate link:**       [http://webdevstudios.com](http://webdevstudios.com)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      4.4.2  
**Stable tag:**        2.2.1  
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
* [CMB Field Type: Tags](https://github.com/florianbeck/cmb2-field-type-tags): WordPress-Tags-like field type for CMB2. _note: this does not set the post tags, but simply provides a unique text input_

#### Other Helpful Resources
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.
* [WDS-Simple-Page-Builder](https://github.com/WebDevStudios/WDS-Simple-Page-Builder): Uses existing template parts in the currently-active theme to build a customized page with rearrangeable elements. Built with CMB2.
* [CMB2 Example Theme](https://github.com/WebDevStudios/CMB2-Example-Theme): Demonstrate how to include CMB2 in your theme, as well as some cool tips and tricks.
* [facetwp-cmb2](https://github.com/FacetWP/facetwp-cmb2): FacetWP integration with CMB2.
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for WordPress CMB2 library that allows the creation of columns for a better layout in the admin.
* [CMB2 Metatabs Options](https://github.com/rogerlos/cmb2-metatabs-options) from [rogerlos](https://github.com/rogerlos/): CMO makes it easy to create options pages with multiple metaboxes--and optional WordPress admin tabs.
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

### 2.2.1 - 2016-02-29

#### Bug Fixes

* Fixes back-compatibility issue which could allow multiple CMB2 instances to load (causing fatal errors). ([#520](https://github.com/WebDevStudios/CMB2/pull/520))

### 2.2.0

#### Enhancements

* Term Meta! As of WordPress 4.4, [WordPress will have the ability to use term metadata](https://make.wordpress.org/core/2015/10/23/4-4-taxonomy-roundup/). CMB2 will work with term meta out of the box. To do so, see the example cmb registration in the `yourprefix_register_taxonomy_metabox` function in [example-functions.php](https://github.com/WebDevStudios/CMB2/blob/master/example-functions.php).
* New hooks which hook in after save field action: `'cmb2_save_field'` and `"cmb2_save_field_{$field_id}"`. Props [wpsmith](https://github.com/wpsmith) ([#475](https://github.com/WebDevStudios/CMB2/pull/475)).
* The "cmb2_sanitize_{$field_type}" hook now runs for every field type (not just custom types) so you can override the sanitization for all field types via a filter.
* `CMB2::show_form()` is now composed of 3 smaller methods, `CMB2::render_form_open()`, `CMB2::render_field()`, `CMB2::render_form_close()` ([#506](https://github.com/WebDevStudios/CMB2/pull/506)).
* RTL Style generated. Props [@devinsays](https://github.com/devinsays) ([#510](https://github.com/WebDevStudios/CMB2/pull/510)).
* Properly scope date/time-pickers styling by adding a class to only cmb2 picker instances. ([#527](https://github.com/WebDevStudios/CMB2/pull/527))
* Allow per-field overrides for the date/time/color picker options (wiki documentation: [Modify Field Date, Time, or Color Picker options](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#modify-field-date-time-or-color-picker-options))
* Fix some inline documentation issues. Props [@jrfnl](https://github.com/jrfnl) ([#579](https://github.com/WebDevStudios/CMB2/pull/579)).
* Include `.gitattributes` file for excluding development resources when using Composer. Props [@benoitchantre](https://github.com/benoitchantre) ([#575](https://github.com/WebDevStudios/CMB2/pull/575), [#53](https://github.com/WebDevStudios/CMB2/pull/53)).

#### Bug Fixes

* Fixed issue with `'taxonomy_select'` field type where a term which evaluated falsey would not be displayed properly. Props [adamcapriola](https://github.com/adamcapriola) ([#477](https://github.com/WebDevStudios/CMB2/pull/477)).
* Fix issue with colorpickers not changing when sorting groups.
* `'show_option_none'` field parameter now works on taxonomy fields when explicitly setting to false.
* Fix so the date/time-picker javascript respects the `'date_format'` and `'time_format'` field parameters. Props [@yivi](https://github.com/yivi) ([#39](https://github.com/WebDevStudios/CMB2/pull/39), [#282](https://github.com/WebDevStudios/CMB2/pull/282), [#300](https://github.com/WebDevStudios/CMB2/pull/300), [#318](https://github.com/WebDevStudios/CMB2/pull/318), [#330](https://github.com/WebDevStudios/CMB2/pull/330), [#446](https://github.com/WebDevStudios/CMB2/pull/446), [#498](https://github.com/WebDevStudios/CMB2/pull/498)).
* Fix a sometimes-broken unit test. Props [JPry](https://github.com/JPry) ([#539](https://github.com/WebDevStudios/CMB2/pull/539)).
* Fix issue with oembed fields not working correctly on options pages. ([#542](https://github.com/WebDevStudios/CMB2/pull/542)).
* Fix issue with repeatable field <button> elements stealing focus from "submit" button.

**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

