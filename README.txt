=== Custom Metaboxes and Fields for WordPress===

Contributors:
	Andrew Norcross (@norcross / andrewnorcross.com)
	Jared Atchison (@jaredatch / jaredatchison.com)
	Bill Erickson (@billerickson / billerickson.net)
Version: 0.8
Requires at least: 3.3
Tested up to: 3.3
License: GPLv2

== Description ==

Custom Metaboxes and Fields (CMB for short) will create metaboxes with custom fields that will blow your mind.

Field Types:
* text
* text small
* text medium
* text money
* date picker
* date picker timestamp
* time picker
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


== Installation ==

This script is easy to install. If you can't figure it out you probably shouldn't be using it.

1. Place `metabox` directory inside of your (activated) theme. E.g. inside `/themes/twentyten/lib/metabox/`.
2. Include `init.php`.
3. See `example-functions.php` for further guidance.
4. Profit.

== Known Issues ==

* Problem inserting file url inside field for image with caption (issue #50)
* Multicheck saves new values incorrectly (issue #48)
* `CMB_META_BOX_URL` does not define properly in WAMP/XAMP (Windows) (issue #31)
* datepicker triggers extra space below footer with Firefox (issue #14)
* Metabox container WYSIWYG editor cannot be moved

== To-do ==
* Fix known issues (above)
* Ditch packaged datepicker, use jquery UI with 3.3
* clean up enqueued scripts
* improve inline documentation

== Changelog ==

= 0.8 =
* Added jQuery timepicker, props @norcross
* Added 'raw' textarea to convert special HTML entities back to characters, props @norcross
* Added missing examples on example-functions.php, props @norcross

= 0.7 =
* Added the new wp_editor() function for the WYSIWYG dialog box, props @jcpry
* Created 'cmb_show_on' filter to define your own Show On Filters, props @billerickson
* Added page template show_on filter, props @billerickson
* Improvements to the 'file' field type, props @randyhoyt
* Allow for default values on 'radio' and 'radio_inline' field types, props @billerickson

= 0.6.1 =
* Enabled the ability to define your own custom field types (issue #28). props @randyhoyt

= 0.6 =
* Added the ability to limit metaboxes to certain posts by id. props @billerickson

= 0.5 =
* Fixed define to prevent notices. props @destos 
* Added text_date_timestap option. props @andrewyno 
* Fixed WYSIWYG paragraph breaking/spacing bug. props @wpsmith 
* Added taxonomy_radio and taxonomies_select options. props @c3mdigital
* Fixed script causing the dashboard widgets to not be collapsible.
* Fixed various spacing and whitespace inconsistencies 

= 0.4 =
* Think we have a release that is mostly working. We'll say the initial release :) 