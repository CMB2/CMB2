# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased][unreleased]

* 

## 2.0.1 - 2015-02-02

2.0.1 is the official version after beta, and includes all the changes from 2.0.0 (beta).

## 2.0.0(beta) - 2014-08-16

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

## 1.3.0 - (never released, merged into CMB2)

### Enhancements
 
* Localize Date, Time, and Color picker defaults so that they can be overridden via the `cmb_localized_data` filter. ([#528](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/528))
* Change third parameter for 'cmb_metabox_form' to be an args array. Optional arguments include `echo`, `form_format`, and `save_button`.
* Add support for `show_option_none` argument for `taxonomy_select` and `taxonomy_radio` field types. Also adds the following filters: `cmb_all_or_nothing_types`, `cmb_taxonomy_select_default_value`, `cmb_taxonomy_select_{$this->_id()}_default_value`, `cmb_taxonomy_radio_{$this->_id()}_default_value`, `cmb_taxonomy_radio_default_value`. Props [@pmgarman](https://github.com/pmgarman), ([#569](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/569)).
* Make the list items in the `file_list` field type drag & drop sortable. Props [twoelevenjay](https://github.com/twoelevenjay), ([#603](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/603)).

### Bug Fixes  

* Fixed typo in closing `</th>` tag. Props [@CivicImages](https://github.com/CivicImages). ([#616](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/616))

## 1.2.0 - 2014-05-03

### Enhancements
 
* Add support for custom date/time formats. Props [@Scrent](https://github.com/Scrent). ([#506](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/506))
* Simplify `wysiwyg` escaping and allow it to be overridden via the `escape_cb` parameter. ([#491](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/491))
* Add a 'Select/Deselect all' button for the `multicheck` field type.
* Add title option for [repeatable groups](https://github.com/WebDevStudios/CMB2/wiki/Field-Types#group). Title field takes an optional replacement hash, "{#}" that will be replaced by the row number.
* New field parameter, `show_on_cb`, allows you to conditionally display a field via a callback. ([#47](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/47))
* Unit testing (the beginning). Props [@brichards](https://github.com/brichards) and [@camdensegal](https://github.com/camdensegal).

### Bug Fixes  

* Fixed issue where remove file button wouldn't clear the url field. ([#514](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/514))
* `wysiwyg` fields now allow underscores. Fixes some wysiwyg display issues in WordPress 3.8. Props [@lswilson](https://github.com/lswilson). ([#491](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/491))
* Nonce field should only be added once per page. ([#521](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/521))
* Fix `in_array` issue when a post does not have any saved terms for a taxonomy multicheck. ([#527](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/527))
* Fixed error: 'Uninitialized string offset: 0 in cmb_Meta_Box_field.php...`. Props [@DevinWalker](https://github.com/DevinWalker). ([#539](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/539), [#549](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/549)))
* Fix missing `file` field description. ([#543](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/543), [#547](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/547))



## 1.1.3 - 2014-04-07

### Bug Fixes  

* Update `cmb_get_field_value` function as it was passing the parameters to `cmb_get_field` in the wrong order.
* Fix repeating fields not working correctly if meta key or prefix contained an integer. ([#503](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/503))

## 1.1.2 - 2014-04-05

### Bug Fixes  

* Fix issue with `cmb_Meta_Box_types.php` calling a missing method, `image_id_from_url`. ([#502](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/502))


## 1.1.1 - 2014-04-03

### Bug Fixes

* Radio button values were not showing saved value. ([#500](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/500))

## 1.1.0 - 2014-04-02

### Enhancements

* [Repeatable groups](https://github.com/WebDevStudios/CMB2/wiki/Field-Types#group)
* Support for more fields to be repeatable, including oEmbed field, and date, time, and color picker fields, etc.
* Codebase has been revamped to be more modular and object-oriented. 
* New filter, `"cmb_{$element}_attributes"	` for modifying an element's attributes.
* Every field now supports an `attributes` parameter that takes an array of attributes. [Read more](https://github.com/WebDevStudios/CMB2/wiki/Field-Types#attributes).
* Removed `cmb_std_filter` in favor of `cmb_default_filter`. **THIS IS A BREAKING CHANGE**
* Better handling of labels in sidebar. They are now placed on top of the input rather than adjacent.
* Added i18n compatibility to text_money. props [@ArchCarrier](https://github.com/ArchCarrier), ([#485](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/485))
* New helper functions: `cmb_get_field` and `cmb_get_field_value` for getting access to CMB's field object and/or value.
* New JavaScript events, `cmb_add_row` and `cmb_remove_row` for hooking in and manipulating the new row's data.
* New filter, `cmb_localized_data`, for modifiying localized data passed to the CMB JS.

### Bug Fixes
* Resolved occasional issue where only the first character of the label/value was diplayed. props [@mustardBees](https://github.com/mustardBees), ([#486](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/486))


## 1.0.2 - 2014-03-03

### Enhancements

* Change the way the `'cmb_validate_{$field['type']}'` filter works.
It is now passed a null value vs saved value. If null is returned, default sanitization will follow. **THIS IS A BREAKING CHANGE**. If you're already using this filter, take note.
* All field types that take an option array have been simplified to take `key => value` pairs (vs `array( 'name' => 'value', 'value' => 'key', )`). This effects the 'select', 'radio', 'radio_inline' field types. The 'multicheck' field type was already using the `key => value` format. Backwards compatibility has been maintained for those using the older style.
* Added default value option for `taxonomy_select` field type. props [@darlantc](https://github.com/darlantc), ([#473](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/473))
* Added `preview_size` parameter for `file_list` field type. props [@IgorCode](https://github.com/IgorCode), ([#471](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/471))
* Updated `file_list` images to be displayed horizontally instead of vertically. props [@IgorCode](https://github.com/IgorCode), ([#467](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/467))
* Use `get_the_terms` where possible since the data is cached.

### Bug Fixes

* Fixed wysiwyg escaping slashes. props [@gregrickaby](https://github.com/gregrickaby), ([#465](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/465))
* Replaced `__DIR__`, as `dirname( __FILE__ )` is easier to maintain back-compatibility.
* Fixed missing table styling on new posts. props [@mustardBees](https://github.com/mustardBees), ([#438](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/438))
* Fix undeclared JS variable. [@veelen](https://github.com/veelen), ([#451](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/451))
* Fix `file_list` errors when removing all files and saving.
* Set correct `object_id` to be used later in `cmb_show_on` filter. [@lauravaq](https://github.com/lauravaq), ([#445](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/445))
* Fix sanitization recursion memeory issues.

## 1.0.1 - 2014-01-24

### Enhancements

* Now works with option pages and site settings. ([view example in wiki](https://github.com/WebDevStudios/CMB2/wiki/Using-CMB-to-create-an-Admin-Theme-Options-Page))
* two filters to override the setting and getting of options, `cmb_override_option_get_$option_key` and `cmb_override_option_save_$option_key` respectively. Handy for using plugins like [WP Large Options](https://github.com/voceconnect/wp-large-options/) ([also here](http://vip.wordpress.com/plugins/wp-large-options/)).
* Improved styling on taxonomy (\*tease\*) and options pages and for new 3.8 admin UI.
* New sanitization class to sanitize data when saved.
* New callback field parameter, `sanitization_cb`, for performing your own sanitization.
* new `cmb_Meta_Box_types::esc()` method that handles escaping data for display.
* New callback field parameter, `escape_cb`, for performing your own data escaping, as well as a new filter, `'cmb_types_esc_'. $field['type']`.

### Bug Fixes

* Fixed wysiwyg editor button padding. props [@corvannoorloos](https://github.com/corvannoorloos), ([#391](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/391))
* A few php < 5.3 errors were addressed.
* Fields with quotation marks no longer break the input/textarea fields.
* metaboxes for Attachment pages now save correctly. Thanks [@nciske](https://github.com/nciske) for reporting. ([#412](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/issues/412))
* Occasionally fields wouldn't save because of the admin show_on filter.
* Smaller images loaded to the file field type will no longer be blown up larger than their dimensions.

## 1.0.0 - 2013-11-30

* Added `text_datetime_timestamp_timezone` type, a datetime combo field with an additional timezone drop down, props [@dessibelle](https://github.com/dessibelle)
* Added `select_timezone` type, a standalone time zone select dropdown. The time zone select can be used with standalone `text_datetime_timestamp` if desired. Props [@dessibelle](https://github.com/dessibelle)
* Added `text_url` type, a basic url field. Props [@dessibelle](https://github.com/dessibelle)
* Added `text_email` type, a basic email field. Props [@dessibelle](https://github.com/dessibelle)
* Added ability to display metabox fields in frontend. Default is true, but can be overriden using the `cmb_allow_frontend filter`. If set to true, an entire metabox form can be output with the `cmb_metabox_form( $meta_box, $object_id, $echo )` function. Props [@dessibelle](https://github.com/dessibelle), [@messenlehner](https://github.com/messenlehner) & [@jtsternberg](https://github.com/jtsternberg).
* Added hook `cmb_after_table` after all metabox output. Props [@wpsmith](https://github.com/wpsmith)
* `file_list` now works like a repeatable field. Add as many files as you want. Props [@coreymcollins](https://github.com/coreymcollins)
* `text`, `text_small`, `text_medium`, `text_url`, `text_email`, & `text_money` fields now all have the option to be repeatable. Props [@jtsternberg](https://github.com/jtsternberg)
* Custom metaboxes can now be added for user meta. Add them on the user add/edit screen, or in a custom user profile edit page on the front-end. Props [@tw2113](https://github.com/tw2113), [@jtsternberg](https://github.com/jtsternberg)

## 0.9.4

* Added field "before" and "after" options for each field. Solves issue with '$' not being the desired text_money monetary symbol, props [@GaryJones](https://github.com/GaryJones)
* Added filter for 'std' default fallback value, props [@messenlehner](https://github.com/messenlehner)
* Ensure oEmbed videos fit in their respective metaboxes, props [@jtsternberg](https://github.com/jtsternberg)
* Fixed issue where an upload field with 'show_names' disabled wouldn't have the correct button label, props [@jtsternberg](https://github.com/jtsternberg)
* Better file-extension check for images, props [@GhostToast](https://github.com/GhostToast)
* New filter, `cmb_valid_img_types`, for whitelisted image file-extensions, props [@jtsternberg](https://github.com/jtsternberg)

## 0.9.3
* Added field type and field id classes to each cmb table row, props [@jtsternberg](https://github.com/jtsternberg)

## 0.9.2
* Added post type comparison to prevent storing null values for taxonomy selectors, props [@norcross](https://github.com/norcross)

## 0.9.1
* Added `oEmbed` field type with ajax display, props [@jtsternberg](https://github.com/jtsternberg)

## 0.9
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

## 0.8 - 2012-01-19
* Added jQuery timepicker, props [@norcross](https://github.com/norcross)
* Added 'raw' textarea to convert special HTML entities back to characters, props [@norcross](https://github.com/norcross)
* Added missing examples on example-functions.php, props [@norcross](https://github.com/norcross)

## 0.7
* Added the new wp_editor() function for the WYSIWYG dialog box, props [@jcpry](https://github.com/jcpry)
* Created 'cmb_show_on' filter to define your own Show On Filters, props [@billerickson](https://github.com/billerickson)
* Added page template show_on filter, props [@billerickson](https://github.com/billerickson)
* Improvements to the 'file' field type, props [@randyhoyt](https://github.com/randyhoyt)
* Allow for default values on 'radio' and 'radio_inline' field types, props [@billerickson](https://github.com/billerickson)

## 0.6.1
* Enabled the ability to define your own custom field types (issue #28). props [@randyhoyt](https://github.com/randyhoyt)

## 0.6
* Added the ability to limit metaboxes to certain posts by id. props [@billerickson](https://github.com/billerickson)

## 0.5
* Fixed define to prevent notices. props [@destos](https://github.com/destos)
* Added text_date_timestap option. props [@andrewyno](https://github.com/andrewyno)
* Fixed WYSIWYG paragraph breaking/spacing bug. props [@wpsmith](https://github.com/wpsmith)
* Added taxonomy_radio and taxonomies_select options. props [@c3mdigital](https://github.com/c3mdigital)
* Fixed script causing the dashboard widgets to not be collapsible.
* Fixed various spacing and whitespace inconsistencies

## 0.4
* Think we have a release that is mostly working. We'll say the initial release :)
