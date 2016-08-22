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
**Stable tag:**        2.2.2.1  
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

If you are coming from the original "Custom Metaboxes and Fields for WordPress" plugin, [please read this post](https://webdevstudios.com/2015/02/02/cmb2-wordpress-plugin/) for the CMB2 background story.

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
* [CMB Field Type: Link Picker](https://wordpress.org/plugins/link-picker-for-cmb2/): Using the Link Picker for CMB2 control, you can choose a link from your WordPress site, or manually enter a link. You can also identify if the link should open in a new window, or not.
* [CMB Field Type: MultidatesPicker](https://github.com/origgami/cmb2-multidates-picker): Creates a CMB2 field type that enables a multiple date calendar. It uses a plugin called [MultiDatesPicker v1.6.3 for jQuery UI](http://multidatespickr.sourceforge.net/).
* [CMB Field Type: CMB2-radio-image](https://github.com/satwinderrathore/CMB2-radio-image): Image as radio buttons.
* [CMB2 Term Select](https://github.com/florianbeck/cmb2-field-type-tags): Special CMB2 Field that allows users to define an autocomplete text field for terms. _Note: this will set the taxonomy terms, but has the option (`'apply_term' => false`) to disable and save the term ids as data instead (like for options pages, etc)._
* [CMB2 Related Links](https://github.com/jtsternberg/CMB2-Related-Links): Allows users to add a related links via a repeating field group. Field inputs are powered by the [CMB2 Field Type: CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field) documented above, and so each link can be populated with existing WordPress content by clicking on the search button. _Note: this is not a standard field type, but instead a function you use in combination with CMB2::add_field()._

#### Other Helpful Resources
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.
* [WDS-Simple-Page-Builder](https://github.com/WebDevStudios/WDS-Simple-Page-Builder): Uses existing template parts in the currently-active theme to build a customized page with rearrangeable elements. Built with CMB2.
* [CMB2 Example Theme](https://github.com/WebDevStudios/CMB2-Example-Theme): Demonstrate how to include CMB2 in your theme, as well as some cool tips and tricks.
* [facetwp-cmb2](https://github.com/FacetWP/facetwp-cmb2): FacetWP integration with CMB2.
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for WordPress CMB2 library that allows the creation of columns for a better layout in the admin.
* [CMB2 Metatabs Options](https://github.com/rogerlos/cmb2-metatabs-options) from [rogerlos](https://github.com/rogerlos/): CMO makes it easy to create options pages with multiple metaboxes--and optional WordPress admin tabs.
* [CMB2 Conditionals](https://github.com/jcchavezs/cmb2-conditionals) from [jcchavezs](https://github.com/jcchavezs/): Allows developers to relate fields so the display of one is conditional on the value of another.
* [CMB2 Metabox Code Generator](http://willthemoor.github.io/cmb2-metabox-generator/) from [willthemoor](https://github.com/willthemoor/): Skip the boring bits. Use this generator to create fully functional CMB2 metaboxes easily. Now with bulk entry!

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

### 2.2.2.1 - 2016-06-27

#### Bug Fixes

* Fix issue that kept CMB2 stylesheet from being enqueued when using the [options-page snippets](https://github.com/WebDevStudios/CMB2-Snippet-Library/tree/master/options-and-settings-pages).
* Fix issue which caused the CMB2 column display styles to be enqueued in the wrong pages. Now only enqueues on admin pages with columns.

### 2.2.2 - 2016-06-27

#### Enhancements

* You can now set admin post-listing columns with an extra field parameter, `'column' => true,`. If you want to dictate what position the column is, use `'column' => array( 'position' => 2 ),`. If you want to dictate the column title (instead of using the field `'name'` value), use `'column' => array( 'name' => 'My Column' ),`. If you need to specify the column display callback, set the `'display_cb'` parameter to [a callback function](https://github.com/WebDevStudios/CMB2/wiki/Field-Parameters#render_row_cb). Columns work for post (all post-types), comment, user, and term object types.
* Updated Datepicker styles using JJJ's "jQuery UI Datepicker CSS for WordPress", so props Props [@stuttter](https://github.com/stuttter), [@johnjamesjacoby](https://github.com/johnjamesjacoby). Also cleaned up the timepicker styles (specifically the buttons) to more closely align with the datepicker and WordPress styles.
* CMB2 is now a lot more intelligent about where it is located in your installation. This update should solve almost all of the reasons to use the `'cmb2_meta_box_url'` filter (thought it will continue to work as expected). ([#27](https://github.com/WebDevStudios/CMB2/issues/27), [#118](https://github.com/WebDevStudios/CMB2/issues/118), [#432](https://github.com/WebDevStudios/CMB2/issues/432), [related wiki item](https://github.com/WebDevStudios/CMB2/wiki/Troubleshooting#cmb2-urls-issues))
* Implement CMB2_Ajax as a singleton. Props [jrfnl](https://github.com/jrfnl) ([#602](https://github.com/WebDevStudios/CMB2/pull/602)).
* Add `classes` and `classes_cb` CMB2 box params which allows you to add additional classes to the cmb-wrap. The `classes` parameter can take a string or array, and the `classes_cb` takes a callback which returns a string or array. The callback will receive `$cmb` as an argument. These classes are also passed through a new filter, `'cmb2_wrap_classes'`, which receives the array of classes as the first argument, and the CMB2 object as the second. Reported/requested in [#364](https://github.com/WebDevStudios/CMB2/issues/364#issuecomment-213223692).
* Make the `'title'` field type accept extra arguments. Props [@vladolaru](https://github.com/vladolaru), [@pixelgrade](https://github.com/pixelgrade) ([#656](https://github.com/WebDevStudios/CMB2/pull/656)).
* Updated `cmb2_get_oembed()` function to NOT return the "remove" link, as it's intended for outputting the oembed only. **This is a backwards-compatibility concern.** If you were depending on the "remove" link, use `cmb2_ajax()->get_oembed( $args )` instead.
* New function, `cmb2_do_oembed()`', which is hooked to `'cmb2_do_oembed'`, so you can use `do_action( 'cmb2_do_oembed', $args )` in your themes without `function_exists()` checks.
* New method, `CMB2:set_prop( $property, $value )`, for setting a CMB2 metabox object property.
* The `CMB2_Field` object instances will now have a `cmb_id` property and a `get_cmb` method to enable access to the field's `CMB2` parent object's instance, in places like field callbacks and filters (e.g. `$cmb = $field->get_cmb();`).
* Add a `data-fieldtype` attribute to the field rows for simpler identification in Javascript.
* Moved each type in `CMB2_Types` to it's own class so that each field type can handle it's own field display, and added the infrastructure to maintainn back-compatibility.
* New `CMB2_Utils` methods, `notempty()` and `filter_empty()`, both of which consider `null`, `''` and `false` as empty, but allow `0` (for saving `0` as a field value).
* New `CMB2_Utils` public methods, `get_url_from_dir()`, `get_file_ext()`, `get_file_name_from_path()`, and `wp_at_least()`.
* Add a `cmb_pre_init` Javascript event to allow overriding CMB2 defaults via JS.

#### Bug Fixes
* Fix issue with 'default' callback not being applied in all instances. Introduced new `CMB2_Field::get_default()` method, and `'default_cb'` field parameter. Using the `'default'` field parameter with a callback will be deprecated in the next few releases. ([#572](https://github.com/WebDevStudios/CMB2/issues/572)).
* Be sure to call `CMB2_Field::row_classes()` for group field rows. Also, update CSS to use the "cmb-type-group" classname instead of "cmb-repeat-group-wrap".
* Introduce new `'text'` and `'text_cb'` field parameters for overriding CMB2 text strings instead of using the `'options'` array. ([#630](https://github.com/WebDevStudios/CMB2/pull/630))
* Fix bug where the value of '0' could not be saved in group fields.
* Fix bug where a serialized empty array value in the database for a repeatable field would output as "Array".
* Allow for optional/empty money field. Props [@jrfnl](https://github.com/jrfnl) ([#577](https://github.com/WebDevStudios/CMB2/pull/577)).
* The `CMB2::$updated` parameter (which contains field ids for all fields updated during a save) now also correctly adds group field ids to the array.

## Known Issues

* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

