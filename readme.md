# Custom Metaboxes and Fields for WordPress

**Contributors**:

* WebDevStudios ( [@webdevstudios](http://twitter.com/webdevstudios ) / [webdevstudios.com](http://webdevstudios.com) )
* Justin Sternberg ( [@jtsternberg](http://twitter.com/jtsternberg ) / [webdevstudios.com](http://webdevstudios.com) )
* Jared Atchison ( [@jaredatch](http://twitter.com/jaredatch ) / [jaredatchison.com](http://jaredatchison.com/) )
* Bill Erickson ( [@billerickson](http://twitter.com/billerickson ) / [billerickson.net](http://billerickson.net/) )
* Andrew Norcross ( [@norcross](http://twitter.com/norcross ) / [andrewnorcross.com](http://andrewnorcross.com/) )

**Version**: 1.2.0 
**Requires at least**: 3.5  
**Tested up to**: 3.9  
**License**: GPLv2  

## Description

Custom Metaboxes and Fields (CMB for short) will create metaboxes and forms with custom fields that will blow your mind.

##### Features:

* Create metaboxes to be used on post edit screens.
* Create forms to be used on options pages.
* Create forms to handle user meta and display them on user profile add/edit pages.
* Flexible API that allows you to use CMB forms almost anywhere, even on the front-end.
* Several field types are included and are [listed below](#field-types).
* Custom API hook that allows you to create your own field types.
* There are numerous hooks and filters, allowing you to modify many aspects of the library (without editing it directly).
* Repeatable fields for most field types are supported, as well as repeatable field groups.

##### Field Types:
1. [`title`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#title) An arbitrary title field *
1. [`text`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text)
1. [`text_small`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_small)
1. [`text_medium`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_medium)
1. [`text_email`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_email)
1. [`text_url`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_url)
1. [`text_money`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_money)
1. [`textarea`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#textarea)
1. [`textarea_small`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#textarea_small)
1. [`textarea_code`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#textarea_code)
1. [`text_date`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_date) Date Picker
1. [`text_time`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_time) Time picker
1. [`select_timezone`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#select_timezone) Time zone dropdown
1. [`text_date_timestamp`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_date_timestamp) Date Picker (UNIX timestamp)
1. [`text_datetime_timestamp`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_datetime_timestamp) Test Date/Time Picker Combo (UNIX timestamp)
1. [`text_datetime_timestamp_timezone`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#text_datetime_timestamp_timezone) Test Date/Time Picker/Time zone Combo (serialized DateTime object)
1. [`colorpicker`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#colorpicker) Color picker
1. [`radio`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#radio) *
1. [`radio_inline`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#radio_inline) *
1. [`taxonomy_radio`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#taxonomy_radio) *
1. [`taxonomy_radio_inline`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#taxonomy_radio_inline) *
1. [`select`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#select)
1. [`taxonomy_select`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#taxonomy_select) *
1. [`checkbox`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#checkbox) *
1. [`multicheck`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#multicheck)
1. [`taxonomy_multicheck`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#taxonomy_multicheck) *
1. [`taxonomy_multicheck_inline`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#taxonomy_multicheck_inline)
1. [`wysiwyg`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#wysiwyg) (TinyMCE) *
1. [`file`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#file) Image/File upload *†
1. [`file_list`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#file_list) Image/File list upload
1. [`oembed`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#oembed) Converts oembed urls (instagram, twitter, youtube, etc. [oEmbed in the Codex](https://codex.wordpress.org/Embeds))
1. [`group`](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#group) Hybrid field that supports adding other fields as a repeatable group. *
1. [Create your own custom field type](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#Custom)

\* Not available as a repeatable field  
† Use `file_list` for repeatable  

[More on field types (GitHub wiki)](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types)

##### 3rd Party Resources
* [CMB Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) from [coreymcollins](https://github.com/coreymcollins): Custom field type for attaching posts to a page.
* [CMB Field Type: Google Maps](https://github.com/mustardBees/cmb_field_map) from [mustardBees](https://github.com/mustardBees): Custom field type for Google Maps.
	> The `pw_map` field stores the latitude/longitude values which you can then use to display a map in your theme.
* [CMB Field Type: Select2](https://github.com/mustardBees/cmb-field-select2) from [mustardBees](https://github.com/mustardBees): Custom field types which use the [Select2](http://ivaynberg.github.io/select2/) script:

	> 1. The `pw_select field` acts much like the default select field. However, it adds typeahead-style search allowing you to quickly make a selection from a large list
	> 2. The `pw_multiselect` field allows you to select multiple values with typeahead-style search. The values can be dragged and dropped to reorder
* [Taxonomy_MetaData](https://github.com/jtsternberg/Taxonomy_MetaData#to-use-taxonomy_metadata-with-custom-metaboxes-and-fields): WordPress Helper Class for saving pseudo-metadata for taxonomy terms. Includes an extended class for using CMB to generate the actual form fields.

##### Contribution
All contributions welcome. If you would like to submit a pull request, please check out the [trunk branch](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/tree/trunk) and pull request against it.

##### Links
* [Github project page](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress)
* [Documentation (GitHub wiki)](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki)


## Installation

1. Place the CMB directory inside of your theme or plugin.
2. Copy (and rename if desired) `example-functions.php` into a folder *above* the CMB directory OR copy the entirety of its contents to your theme's `functions.php` file.
2. Edit to only include the fields you need and rename the functions (CMB directory should be left unedited in order to easily update the library).
4. Profit.

## Changelog

### 1.2.0

**Enhancements**
 
* Add support for custom date/time formats. Props [@Scrent](https://github.com/Scrent). ([#506](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/506))
* Simplify `wysiwyg` escaping and allow it to be overridden via the `escape_cb` parameter. ([#491](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/491))
* Add a 'Select/Deselect all' button for the `multicheck` field type.
* Add title option for [repeatable groups](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#group). Title field takes an optional replacement hash, "{#}" that will be replaced by the row number.
* New field parameter, `show_on_cb`, allows you to conditionally display a field via a callback. ([#47](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/47))
* Unit testing (the beginning). Props [@brichards](https://github.com/brichards) and [@camdensegal](https://github.com/camdensegal).

**Bug Fixes**  

* Fixed issue where remove file button wouldn't clear the url field. ([#514](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/514))
* `wysiwyg` fields now allow underscores. Fixes some wysiwyg display issues in WordPress 3.8. Props [@lswilson](https://github.com/lswilson). ([#491](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/491))
* Nonce field should only be added once per page. ([#521](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/521))
* Fix `in_array` issue when a post does not have any saved terms for a taxonomy multicheck. ([#527](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/527))
* Fixed error: 'Uninitialized string offset: 0 in cmb_Meta_Box_field.php...`. Props [@DevinWalker](https://github.com/DevinWalker). ([#539](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/539), [#549](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/549)))
* Fix missing `file` field description. ([#543](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/543), [#547](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/547))



### 1.1.3
**Bug Fixes**  

* Update `cmb_get_field_value` function as it was passing the parameters to `cmb_get_field` in the wrong order.
* Fix repeating fields not working correctly if meta key or prefix contained an integer. ([#503](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/503))

### 1.1.2

**Bug Fixes**  

* Fix issue with `cmb_Meta_Box_types.php` calling a missing method, `image_id_from_url`. ([#502](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/502))


### 1.1.1

**Bug Fixes**

* Radio button values were not showing saved value. ([#500](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/500))

### 1.1.0

**Enhancements**

* [Repeatable groups](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#group)
* Support for more fields to be repeatable, including oEmbed field, and date, time, and color picker fields, etc.
* Codebase has been revamped to be more modular and object-oriented. 
* New filter, `"cmb_{$element}_attributes"	` for modifying an element's attributes.
* Every field now supports an `attributes` parameter that takes an array of attributes. [Read more](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types#attributes).
* Removed `cmb_std_filter` in favor of `cmb_default_filter`. **THIS IS A BREAKING CHANGE**
* Better handling of labels in sidebar. They are now placed on top of the input rather than adjacent.
* Added i18n compatibility to text_money. props [@ArchCarrier](https://github.com/ArchCarrier), ([#485](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/485))
* New helper functions: `cmb_get_field` and `cmb_get_field_value` for getting access to CMB's field object and/or value.
* New JavaScript events, `cmb_add_row` and `cmb_remove_row` for hooking in and manipulating the new row's data.
* New filter, `cmb_localized_data`, for modifiying localized data passed to the CMB JS.

**Bug Fixes**
* Resolved occasional issue where only the first character of the label/value was diplayed. props [@mustardBees](https://github.com/mustardBees), ([#486](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/486))


### 1.0.2

**Enhancements**

* Change the way the `'cmb_validate_{$field['type']}'` filter works.
It is now passed a null value vs saved value. If null is returned, default sanitization will follow. **THIS IS A BREAKING CHANGE**. If you're already using this filter, take note.
* All field types that take an option array have been simplified to take `key => value` pairs (vs `array( 'name' => 'value', 'value' => 'key', )`). This effects the 'select', 'radio', 'radio_inline' field types. The 'multicheck' field type was already using the `key => value` format. Backwards compatibility has been maintained for those using the older style.
* Added default value option for `taxonomy_select` field type. props [@darlantc](https://github.com/darlantc), ([#473](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/473))
* Added `preview_size` parameter for `file_list` field type. props [@IgorCode](https://github.com/IgorCode), ([#471](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/471))
* Updated `file_list` images to be displayed horizontally instead of vertically. props [@IgorCode](https://github.com/IgorCode), ([#467](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/467))
* Use `get_the_terms` where possible since the data is cached.

**Bug Fixes**

* Fixed wysiwyg escaping slashes. props [@gregrickaby](https://github.com/gregrickaby), ([#465](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/465))
* Replaced `__DIR__`, as `dirname( __FILE__ )` is easier to maintain back-compatibility.
* Fixed missing table styling on new posts. props [@mustardBees](https://github.com/mustardBees), ([#438](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/438))
* Fix undeclared JS variable. [@veelen](https://github.com/veelen), ([#451](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/451))
* Fix `file_list` errors when removing all files and saving.
* Set correct `object_id` to be used later in `cmb_show_on` filter. [@lauravaq](https://github.com/lauravaq), ([#445](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/445))
* Fix sanitization recursion memeory issues.

### 1.0.1

**Enhancements**

* Now works with option pages and site settings. ([view example in wiki](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Using-CMB-to-create-an-Admin-Theme-Options-Page))
* two filters to override the setting and getting of options, `cmb_override_option_get_$option_key` and `cmb_override_option_save_$option_key` respectively. Handy for using plugins like [WP Large Options](https://github.com/voceconnect/wp-large-options/) ([also here](http://vip.wordpress.com/plugins/wp-large-options/)).
* Improved styling on taxonomy (\*tease\*) and options pages and for new 3.8 admin UI.
* New sanitization class to sanitize data when saved.
* New callback field parameter, `sanitization_cb`, for performing your own sanitization.
* new `cmb_Meta_Box_types::esc()` method that handles escaping data for display.
* New callback field parameter, `escape_cb`, for performing your own data escaping, as well as a new filter, `'cmb_types_esc_'. $field['type']`.

**Bug Fixes**

* Fixed wysiwyg editor button padding. props [@corvannoorloos](https://github.com/corvannoorloos), ([#391](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/391))
* A few php < 5.3 errors were addressed.
* Fields with quotation marks no longer break the input/textarea fields.
* metaboxes for Attachment pages now save correctly. Thanks [@nciske](https://github.com/nciske) for reporting. ([#412](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/412))
* Occasionally fields wouldn't save because of the admin show_on filter.
* Smaller images loaded to the file field type will no longer be blown up larger than their dimensions.

### 1.0.0
* Added `text_datetime_timestamp_timezone` type, a datetime combo field with an additional timezone drop down, props [@dessibelle](https://github.com/dessibelle)
* Added `select_timezone` type, a standalone time zone select dropdown. The time zone select can be used with standalone `text_datetime_timestamp` if desired. Props [@dessibelle](https://github.com/dessibelle)
* Added `text_url` type, a basic url field. Props [@dessibelle](https://github.com/dessibelle)
* Added `text_email` type, a basic email field. Props [@dessibelle](https://github.com/dessibelle)
* Added ability to display metabox fields in frontend. Default is true, but can be overriden using the `cmb_allow_frontend filter`. If set to true, an entire metabox form can be output with the `cmb_metabox_form( $meta_box, $object_id, $echo )` function. Props [@dessibelle](https://github.com/dessibelle), [@messenlehner](https://github.com/messenlehner) & [@jtsternberg](https://github.com/jtsternberg).
* Added hook `cmb_after_table` after all metabox output. Props [@wpsmith](https://github.com/wpsmith)
* `file_list` now works like a repeatable field. Add as many files as you want. Props [@coreymcollins](https://github.com/coreymcollins)
* `text`, `text_small`, `text_medium`, `text_url`, `text_email`, & `text_money` fields now all have the option to be repeatable. Props [@jtsternberg](https://github.com/jtsternberg)
* Custom metaboxes can now be added for user meta. Add them on the user add/edit screen, or in a custom user profile edit page on the front-end. Props [@tw2113](https://github.com/tw2113), [@jtsternberg](https://github.com/jtsternberg)

### 0.9.4
* Added field "before" and "after" options for each field. Solves issue with '$' not being the desired text_money monetary symbol, props [@GaryJones](https://github.com/GaryJones)
* Added filter for 'std' default fallback value, props [@messenlehner](https://github.com/messenlehner)
* Ensure oEmbed videos fit in their respective metaboxes, props [@jtsternberg](https://github.com/jtsternberg)
* Fixed issue where an upload field with 'show_names' disabled wouldn't have the correct button label, props [@jtsternberg](https://github.com/jtsternberg)
* Better file-extension check for images, props [@GhostToast](https://github.com/GhostToast)
* New filter, `cmb_valid_img_types`, for whitelisted image file-extensions, props [@jtsternberg](https://github.com/jtsternberg)

### 0.9.3
* Added field type and field id classes to each cmb table row, props [@jtsternberg](https://github.com/jtsternberg)

### 0.9.2
* Added post type comparison to prevent storing null values for taxonomy selectors, props [@norcross](https://github.com/norcross)

### 0.9.1
* Added `oEmbed` field type with ajax display, props [@jtsternberg](https://github.com/jtsternberg)

### 0.9
* __Note: This release requires WordPress 3.3+__
* Cleaned up scripts being queued, props [@jaredatch](https://github.com/jaredatch)
* Cleaned up and reorganized jQuery, props [@GaryJones](https://github.com/GaryJones)
* Use $pagenow instead of custom $current_page, props [@jaredatch](https://github.com/jaredatch)
* Fixed CSS, removed inline styles, now all in style.css, props [@jaredatch](https://github.com/jaredatch)
* Fixed multicheck issues (issue #48), props [@jaredatch](https://github.com/jaredatch)
* Fixed jQuery UI datepicker CSS conflicting with WordPress UI elements, props [@jaredatch](https://github.com/jaredatch)
* Fixed zeros not saving in fields, props [@GaryJones](https://github.com/GaryJones)
* Fixed improper labels on radio and multicheck fields, props [@jaredatch](https://github.com/jaredatch)
* Fixed fields not rendering properly when in sidebar, props [@jaredatch](https://github.com/jaredatch)
* Fixed bug where datepicker triggers extra space after footer in Firefox (issue #14), props [@jaredatch](https://github.com/jaredatch)
* Added jQuery UI datepicker packaged with 3.3 core, props [@jaredatch](https://github.com/jaredatch)
* Added date time combo picker, props [@jaredatch](https://github.com/jaredatch)
* Added color picker, props [@jaredatch](https://github.com/jaredatch)
* Added readme.md markdown file, props [@jaredatch](https://github.com/jaredatch)

### 0.8
* Added jQuery timepicker, props [@norcross](https://github.com/norcross)
* Added 'raw' textarea to convert special HTML entities back to characters, props [@norcross](https://github.com/norcross)
* Added missing examples on example-functions.php, props [@norcross](https://github.com/norcross)

### 0.7
* Added the new wp_editor() function for the WYSIWYG dialog box, props [@jcpry](https://github.com/jcpry)
* Created 'cmb_show_on' filter to define your own Show On Filters, props [@billerickson](https://github.com/billerickson)
* Added page template show_on filter, props [@billerickson](https://github.com/billerickson)
* Improvements to the 'file' field type, props [@randyhoyt](https://github.com/randyhoyt)
* Allow for default values on 'radio' and 'radio_inline' field types, props [@billerickson](https://github.com/billerickson)

### 0.6.1
* Enabled the ability to define your own custom field types (issue #28). props [@randyhoyt](https://github.com/randyhoyt)

### 0.6
* Added the ability to limit metaboxes to certain posts by id. props [@billerickson](https://github.com/billerickson)

### 0.5
* Fixed define to prevent notices. props [@destos](https://github.com/destos)
* Added text_date_timestap option. props [@andrewyno](https://github.com/andrewyno)
* Fixed WYSIWYG paragraph breaking/spacing bug. props [@wpsmith](https://github.com/wpsmith)
* Added taxonomy_radio and taxonomies_select options. props [@c3mdigital](https://github.com/c3mdigital)
* Fixed script causing the dashboard widgets to not be collapsible.
* Fixed various spacing and whitespace inconsistencies

### 0.4
* Think we have a release that is mostly working. We'll say the initial release :)

## Known Issues

* Problem inserting file url inside field for image with caption (issue #50) May be fixed, needs testing.
* `CMB_META_BOX_URL` does not define properly in WAMP/XAMP (Windows) (issue #31) May be fixed, needs testing.
* Metabox containing WYSIWYG editor cannot be moved (this is a TinyMCE issue)

## To-do
**Enhancements**

* Fix known issues (above)
* move timepicker and datepicker jQuery inline
* support for multiple configurable timepickers/datepickers
* add ability to save fields in a single custom field
* add ability to mark fields as required
* repeatable fields (halfway there)
* look at possiblity of tabs
* look at preserving taxonomy hierarchies
* Add input attributes filter
* Always load newest version of CMB
* Helper function to easily get oembed from stored oEmbed field

