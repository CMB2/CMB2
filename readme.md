# Custom Metaboxes and Fields for WordPress

**Contributors**:

* Justin Sternberg ( [@jtsternberg](http://twitter.com/jtsternberg ) / [webdevstudios.com](http://webdevstudios.com) )
* Jared Atchison ( [@jaredatch](http://twitter.com/jaredatch ) / [jaredatchison.com](http://jaredatchison.com/) )
* Bill Erickson ( [@billerickson](http://twitter.com/billerickson ) / [billerickson.net](http://billerickson.net/) )
* Andrew Norcross ( [@norcross](http://twitter.com/norcross ) / [andrewnorcross.com](http://andrewnorcross.com/) )

**Version**: 1.0.1  
**Requires at least**: 3.5  
**Tested up to**: 3.8  
**License**: GPLv2  

## Description

Custom Metaboxes and Fields (CMB for short) will create metaboxes with custom fields that will blow your mind.

New in version 1.0.0:

* Bring your metaboxes to the frontend.
* Create metaboxes to handle user meta and display them on user profile add/edit pages. Or even on the front-end.

##### Links
* [Github project page](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress)
* [Documentation (GitHub wiki)](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki)

##### Field Types:
* text (optionally repeatable)
* text small (optionally repeatable)
* text medium (optionally repeatable)
* text url (optionally repeatable)
* text email (optionally repeatable)
* text money (optionally repeatable)
* date picker
* date picker (unix timestamp)
* date time picker combo (unix timestamp)
* date time picker with time zone combo (serialized DateTime object)
* time zone dropdown
* time picker
* color picker
* textarea
* textarea small
* textarea code
* select
* radio
* radio inline
* taxonomy radio
* taxonomy select
* checkbox
* multicheck
* WYSIWYG/TinyMCE
* Image/file upload
* oEmbed

[More on field types (GitHub wiki)](https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types)

## Installation

This script is easy to install. If you can't figure it out you probably shouldn't be using it.

1. Place `metabox` directory inside of your (activated) theme. E.g. inside `/themes/twentyten/lib/metabox/`.
2. Include `init.php` (preferably on the 'init' WordPress hook).
3. See `example-functions.php` for further guidance.
4. Profit.

## Known Issues

* Problem inserting file url inside field for image with caption (issue #50) May be fixed, needs testing.
* `CMB_META_BOX_URL` does not define properly in WAMP/XAMP (Windows) (issue #31) May be fixed, needs testing.
* Metabox containing WYSIWYG editor cannot be moved (this is a TinyMCE issue)

## To-do
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

## Changelog

### 1.0.1

**Bug Fixes**

* Fixed wysiwyg editor button padding. props [@corvannoorloos](https://github.com/corvannoorloos)
* A few php < 5.3 errors were addressed.
* Fields with quotation marks no longer break the input/textarea fields.
* metaboxes for Attachment pages now save correctly. Thanks [@nciske](https://github.com/nciske) for reporting.
* Occasionally fields wouldn't save because of the admin show_on filter.
* Smaller images loaded to the file field type will no longer be blown up larger than their dimensions.

**Enhancements**

* Now works with option pages and site settings. ([view example in wiki](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Using-CMB-to-create-an-Admin-Theme-Options-Page))
* two filters to override the setting and getting of options, `cmb_override_option_get_$option_key` and `cmb_override_option_save_$option_key` respectively. Handy for using plugins like [WP Large Options](https://github.com/voceconnect/wp-large-options/) ([also here](http://vip.wordpress.com/plugins/wp-large-options/)).
* Improved styling on taxonomy (\*tease\*) and options pages and for new 3.8 admin UI.
* New sanitization class to sanitize data when saved.
* New callback field parameter, `sanitization_cb`, for performing your own sanitization.
* new `cmb_Meta_Box_types::esc()` method that handles escaping data for display.
* New callback field parameter, `escape_cb`, for performing your own data escaping, as well as a new filter, `'cmb_types_esc_'. $field['type']`.


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
