=== Custom Metaboxes and Fields ===
Contributors:	Andrew Norcross (@norcross / andrewnorcross.com)
				Jared Atchison (@jaredatch / jaredatchison.com)
				Bill Erickson (@billerickson / billerickson.net)
Version: 0.7
Requires at least: 3.0
Tested up to: 3.3 Beta 2
 
== Description ==

This will create metaboxes with custom fields that will blow your mind.

== Installation ==

This script is easy to install. If you can't figure it out you probably shouldn't be using it.

1. Place metabox directory inside of your (activated) theme. E.g. inside /themes/twentyten/lib/metabox/.
2. Include init.php.
3. See example-functions.php for further guidance.
4. Profit.

== Frequently Asked Questions ==

Coming soon.

== TODO ==
* Security & best practices audit
* File handling improvement and fixes

== Changelog ==

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