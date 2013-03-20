# Custom Metaboxes and Fields for WordPress

**Contributors**:

* Andrew Norcross ( [@norcross](http://twitter.com/norcross ) / [andrewnorcross.com](http://andrewnorcross.com/) )
* Jared Atchison ( [@jaredatch](http://twitter.com/jaredatch ) / [jaredatchison.com](http://jaredatchison.com/) )
* Bill Erickson ( [@billerickson](http://twitter.com/billerickson ) / [billerickson.net](http://billerickson.net/) )
* Justin Sternberg ( [@jtsternberg](http://twitter.com/jtsternberg ) / [dsgnwrks.pro](http://dsgnwrks.pro) )

**Version**: 0.9.2
**Requires at least**: 3.3
**Tested up to**: 3.5
**License**: GPLv2

## Description

Custom Metaboxes and Fields (CMB for short) will create metaboxes with custom fields that will blow your mind.

##### Links
* [Github project page](http://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress)
* [Documentation (GitHub wiki)](http://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress/wiki)

##### Field Types:
* text
* text small
* text medium
* text money
* date picker
* date picker (unix timestamp)
* date time picker combo (unix timestamp)
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

[More on field types (GitHub wiki)](https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types)

## Installation

This script is easy to install. If you can't figure it out you probably shouldn't be using it.

1. Place `metabox` directory inside of your (activated) theme. E.g. inside `/themes/twentyten/lib/metabox/`.
2. Include `init.php` (preferably on the 'init' WordPress hook).
3. See `example-functions.php` for further guidance.
4. Profit.

## Known Issues

* Problem inserting file url inside field for image with caption (issue #50)
* `CMB_META_BOX_URL` does not define properly in WAMP/XAMP (Windows) (issue #31)
* Metabox containing WYSIWYG editor cannot be moved (this is a TinyMCE issue)

## To-do
* Fix known issues (above)
* clean up code
* improve inline documentation
* move timepicker and datepicker jQuery inline
* support for multiple configurable timepickers/datepickers
* add ability to save fields in a single custom field
* add ability to mark fields as required
* add ability to define `placeholder` text
* repeatable fields
* look at possiblity of tabs
* look at preserving taxonomy hierarchies

## Changelog

### 0.9.2
* Added post type comparison to prevent storing null values for taxonomy selectors, props @norcross

### 0.9.1
* Added 'oEmbed' field type with ajax display, props @jtsternberg

### 0.9
* __Note: This release requires WordPress 3.3+__
* Cleaned up scripts being queued, props @jaredatch
* Cleaned up and reorganized jQuery, props @GaryJones
* Use $pagenow instead of custom $current_page, props @jaredatch
* Fixed CSS, removed inline styles, now all in style.css, props @jaredatch
* Fixed multicheck issues (issue #48), props @jaredatch
* Fixed jQuery UI datepicker CSS conflicting with WordPress UI elements, props @jaredatch
* Fixed zeros not saving in fields, props @GaryJones
* Fixed improper labels on radio and multicheck fields, props @jaredatch
* Fixed fields not rendering properly when in sidebar, props @jaredatch
* Fixed bug where datepicker triggers extra space after footer in Firefox (issue #14), props @jaredatch
* Added jQuery UI datepicker packaged with 3.3 core, props @jaredatch
* Added date time combo picker, props @jaredatch
* Added color picker, props @jaredatch
* Added readme.md markdown file, props @jaredatch

### 0.8
* Added jQuery timepicker, props @norcross
* Added 'raw' textarea to convert special HTML entities back to characters, props @norcross
* Added missing examples on example-functions.php, props @norcross

### 0.7
* Added the new wp_editor() function for the WYSIWYG dialog box, props @jcpry
* Created 'cmb_show_on' filter to define your own Show On Filters, props @billerickson
* Added page template show_on filter, props @billerickson
* Improvements to the 'file' field type, props @randyhoyt
* Allow for default values on 'radio' and 'radio_inline' field types, props @billerickson

### 0.6.1
* Enabled the ability to define your own custom field types (issue #28). props @randyhoyt

### 0.6
* Added the ability to limit metaboxes to certain posts by id. props @billerickson

### 0.5
* Fixed define to prevent notices. props @destos
* Added text_date_timestap option. props @andrewyno
* Fixed WYSIWYG paragraph breaking/spacing bug. props @wpsmith
* Added taxonomy_radio and taxonomies_select options. props @c3mdigital
* Fixed script causing the dashboard widgets to not be collapsible.
* Fixed various spacing and whitespace inconsistencies

### 0.4
* Think we have a release that is mostly working. We'll say the initial release :)
