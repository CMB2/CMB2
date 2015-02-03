# CMB 2.0

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
**Stable tag:**        2.0.1  
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

* [CMB Field Type: Gallery](https://github.com/mustardBees/cmb-field-gallery) from [mustardBees](https://github.com/mustardBees): Adds a WordPress gallery field.

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

1. Place the CMB directory inside of your theme or plugin.
2. Copy (and rename if desired) `example-functions.php` into a folder *above* the CMB directory OR copy the entirety of its contents to your theme's `functions.php` file.
2. Edit to only include the fields you need and rename the functions (CMB directory should be left unedited in order to easily update the library).
4. Profit.

## 2.0.0(beta)-2.0.1 Changelog

2.0.0 is the official version number for the transition to CMB2, and 2.0.1 is the official version after beta. It is a complete rewrite. Improvements and fixes are listed below. __Note: This release requires WordPress 3.8+__
 
### Enhancements

* Converted `<table>` markup to more generic `<div>` markup to be more extensible and allow easier styling.
* Much better handling and display of repeatable groups.
* Entirely translation-ready [with full translations](http://wp-translations.org/project/cmb2/) in Spanish, French (Props [@fredserva](https://github.com/fredserva) - [#127](https://github.com/WebDevStudios/CMB2/pull/127)), Finnish (Props [@onnimonni](https://github.com/onnimonni) - [#108](https://github.com/WebDevStudios/CMB2/pull/108)), Swedish (Props [@EyesX](https://github.com/EyesX) - [#141](https://github.com/WebDevStudios/CMB2/pull/141)), and English.
* Add cmb fields to new user page. Props [GioSensation](https://github.com/GioSensation), ([#616](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/616)).
* Improved and additional [helper-functions](https://github.com/WebDevStudios/CMB2/blob/master/includes/helper-functions.php).
* Added new features and translation for datepicker. Props [kalicki](https://github.com/kalicki), ([#657](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/657)).
* General code standards cleanup. Props [gregrickaby](https://github.com/gregrickaby), ([#17](https://github.com/WebDevStudios/CMB2/pull/17) & others).
* Use SASS for development. Props [gregrickaby](https://github.com/gregrickaby), ([#18](https://github.com/WebDevStudios/CMB2/pull/18)).
* New `hidden` field type.
* [Ability to override text strings in fields via field options parameter](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#override-text-strings-in-field).
* Added composer.json. Props [nlemoine](https://github.com/nlemoine), ([#19](https://github.com/WebDevStudios/CMB2/pull/19)).
* New field 'hooks' can take [static text/html](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#inject-static-content-in-a-field) or a [callback](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#inject-dynamic-content-in-a-field-via-a-callback).
* New `preview_size` parameter for `file` field type. Takes an array or named image size.
* Empty index.php file to all folders (for more security). Props [brunoramalho](https://github.com/brunoramalho), ([#41](https://github.com/WebDevStudios/CMB2/pull/41)).
* Clean up styling. Props [brunoramalho](https://github.com/brunoramalho), ([#43](https://github.com/WebDevStudios/CMB2/pull/43)) and [senicar](https://github.com/senicar).
* Collapsible field groups. Props [cluke009](https://github.com/cluke009), ([#59](https://github.com/WebDevStudios/CMB2/pull/59)).
* Allow for override of update/remove for CMB2_Field. Props [sc0ttkclark](https://github.com/sc0ttkclark), ([#65](https://github.com/WebDevStudios/CMB2/pull/65)).
* Use class button-disabled instead of disabled="disabled" for <a> buttons. Props [sc0ttkclark](https://github.com/sc0ttkclark), ([#66](https://github.com/WebDevStudios/CMB2/pull/66)).
* [New before/after dynamic form hooks](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#using-the-dynamic-beforeafter-form-hooks).
* Larger unit test coverage. Props to [@pmgarman](https://github.com/pmgarman) for assistance. ([#90](https://github.com/WebDevStudios/CMB2/pull/90) and [#91](https://github.com/WebDevStudios/CMB2/pull/91))
* Added helper function to update an option. Props [mAAdhaTTah](https://github.com/mAAdhaTTah), ([#110](https://github.com/WebDevStudios/CMB2/pull/110)).
* More JS hooks during repeat group shifting. Props [AlchemyUnited](https://github.com/AlchemyUnited), ([#125](https://github.com/WebDevStudios/CMB2/pull/125)). 
* [New metabox config option for defaulting to closed](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#setting-a-metabox-to-closed-by-default).
* New hooks, [`cmb2_init`](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#using-cmb2-helper-functions-and-cmb2_init) and `cmb2_init_{$cmb_id}`.

### Bug Fixes

* New mechanism to ensure CMB2 only loads the most recent version of CMB2 in your system. This fixes the issue where another bundled version could conflict or take precendent over your up-to-date version.
* Fix issue with field labels being hidden. Props [mustardBees](https://github.com/mustardBees), ([#48](https://github.com/WebDevStudios/CMB2/pull/48)).
* Address issues with autoloading before autoloader is setup. Props [JPry](https://github.com/JPry), ([#56](https://github.com/WebDevStudios/CMB2/pull/56)).
* Fixed 'show_on_cb' for field groups. Props [marcusbattle](https://github.com/marcusbattle), ([#98](https://github.com/WebDevStudios/CMB2/pull/98)).
* Make get_object_terms work with and without object caching. Props [joshlevinson](https://github.com/joshlevinson), ([#105](https://github.com/WebDevStudios/CMB2/pull/105)).
* Don't use `__DIR__` in example-functions.php to ensure PHP 5.2 compatibility. Props [bryceadams](https://github.com/bryceadams), ([#129](https://github.com/WebDevStudios/CMB2/pull/129)).
* Added support for radio input swapping in repeatable fields. Props [DevinWalker](https://github.com/DevinWalker), ([#138](https://github.com/WebDevStudios/CMB2/pull/138), [#149](https://github.com/WebDevStudios/CMB2/pull/149)).
* Fix metabox form not being returned to caller. Props [akshayagarwal](https://github.com/akshayagarwal), ([#145](https://github.com/WebDevStudios/CMB2/pull/145)).
* Run stripslashes before saving data, since WordPress forces magic quotes. Props [clifgriffin](https://github.com/clifgriffin), ([#162](https://github.com/WebDevStudios/CMB2/pull/162)).

**[View complete changelog](https://github.com/WebDevStudios/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* The CMB2 url (for css/js resources) does not define properly in all WAMP/XAMP (Windows) environments.
* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).

