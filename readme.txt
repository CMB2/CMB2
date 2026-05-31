=== CMB2 ===
Contributors:      jtsternberg, webdevstudios, tw2113
Donate link:       https://cmb2.io
Tags:              metaboxes, forms, fields, options, settings
Requires at least: 3.8.0
Requires PHP:      7.4
Tested up to:      7.0
Stable tag:        2.12.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

CMB2 is a metabox, custom fields, and forms library for WordPress that will blow your mind.

== Description ==

CMB2 is a developer's toolkit for building metaboxes, custom fields, and forms for WordPress that will blow your mind. Easily manage meta for posts, terms, users, comments, or create custom option pages.

CMB2 is a complete rewrite of Custom Metaboxes and Fields for WordPress. To get started, please follow the examples in the included `example-functions.php` file and have a look at the [basic usage instructions](https://github.com/CMB2/CMB2/wiki/Basic-Usage).

You can see a list of available field types [here](https://github.com/CMB2/CMB2/wiki/Field-Types#types).

### Contribution
Development occurs on Github, and all contributions welcome. Please read the [CONTRIBUTING](https://github.com/CMB2/CMB2/blob/master/CONTRIBUTING.md) doc for more details.

A complete list of all our awesome contributors found here: [github.com/CMB2/CMB2/graphs/contributors](https://github.com/CMB2/CMB2/graphs/contributors)

### Features:

* Create metaboxes to be used on post edit screens.
* [Create forms to be used on an options pages](https://github.com/CMB2/CMB2/wiki/Using-CMB-to-create-an-Admin-Theme-Options-Page).
* Create forms to handle user meta and display them on user profile add/edit pages.
* Create forms to handle term meta and display wherever your taxonomies are used.
* [Flexible API that allows you to use CMB forms almost anywhere, even on the front-end](https://github.com/CMB2/CMB2/wiki/Bringing-Metaboxes-to-the-Front-end).
* [Several field types are included](https://github.com/CMB2/CMB2/wiki/Field-Types).
* [Custom API hook that allows you to create your own field types](https://github.com/CMB2/CMB2/wiki/Adding-your-own-field-types).
* There are numerous hooks and filters, allowing you to modify many aspects of the library (without editing it directly).
* Repeatable fields for most field types are supported, as well as repeatable field groups.
* CMB2 is safe to bundle with any project. It will only load the newest version in the system.

### Translation
If you are looking to provide language translation files, Please do so via [WordPress Plugin Translations](https://translate.wordpress.org/projects/wp-plugins/cmb2).

### Documentation
* CMB2 documentation can be found at [the CMB2 wiki](https://github.com/CMB2/CMB2/wiki) on github. Also, If you're into reading code and inline documentation, we tried to keep all functions and methods fully inline-documented.

### 3rd Party Resources

##### Custom Field Types
* [CMB2 Field Type: CMB Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) from [coreymcollins](https://github.com/coreymcollins): `custom_attached_posts`, for attaching posts to a page.
* [CMB2 Field Type: Post Search Ajax](https://github.com/alexis-magina/cmb2-field-post-search-ajax) by [alexis-magina](https://github.com/alexis-magina): `post_search_ajax` Attach posts to each other. Same approach as [CMB2 Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) but with Ajax request, multiple/single option, and different UI.
* [CMB2 Field Type: Ajax Search](https://github.com/rubengc/cmb2-field-ajax-search) from [rubengc](https://github.com/rubengc): 3 different fields with the same UI in AJAX to search (with query parameters) to users, post type and taxonomy terms.
* [CMB2 Field Type: CMB2 User Search field](https://github.com/Mte90/CMB2-User-Search-field) from [Mte90](https://github.com/Mte90): `user_search_text` adds a user-search dialog for searching/attaching other User IDs.
* [CMB2 Field Type: Google Maps](https://github.com/mustardBees/cmb_field_map) from [mustardBees](https://github.com/mustardBees): Custom field type for Google Maps.
	> The `pw_map` field stores the latitude/longitude values which you can then use to display a map in your theme.

* [CMB2 Field Type: Leaflet Maps](https://github.com/villeristi/CMB2-field-Leaflet-Geocoder) from [villeristi](https://github.com/villeristi): Custom field type for [Leaflet](https://leafletjs.com/) Maps.
* [CMB2 Field Type: Select2](https://github.com/mustardBees/cmb-field-select2) from [mustardBees](https://github.com/mustardBees): Custom field types which use the [Select2](https://select2.org/) script:

	> 1. The `pw_select field` acts much like the default select field. However, it adds typeahead-style search allowing you to quickly make a selection from a large list
	> 2. The `pw_multiselect` field allows you to select multiple values with typeahead-style search. The values can be dragged and dropped to reorder

* [CMB Field Type: Slider](https://github.com/qmatt/cmb2-field-slider) from [mattkrupnik](https://github.com/mattkrupnik/): Adds a jQuery UI Slider field.
* [WDS CMB2 Date Range Field](https://github.com/CMB2/CMB2-Date-Range-Field) from [dustyf](https://github.com/dustyf) of [WebDevStudios](https://github.com/WebDevStudios): Adds a date range field.
* [CMB2 Remote Image Select](https://github.com/CMB2/CMB2-Remote-Image-Select-Field) from [JayWood](https://github.com/JayWood) of [WebDevStudios](https://github.com/WebDevStudios): Allows users to enter a URL in a text field and select a single image for use in post meta. Similar to Facebook's featured image selector.
* [CMB Field Type: Sorter](https://wordpress.org/plugins/cmb-field-type-sorter/): This plugin gives you two CMB field types based on the Sorter script.
* [CMB Field Type: Tags](https://github.com/florianbeck/cmb2-field-type-tags): WordPress-Tags-like field type for CMB2. _note: this does not set the post tags, but simply provides a unique text input_
* [CMB Field Type: Link Picker](https://wordpress.org/plugins/link-picker-for-cmb2/): Using the Link Picker for CMB2 control, you can choose a link from your WordPress site, or manually enter a link. You can also identify if the link should open in a new window, or not.
* [CMB Field Type: MultidatesPicker](https://github.com/origgami/cmb2-multidates-picker): Creates a CMB2 field type that enables a multiple date calendar. It uses a plugin called [MultiDatesPicker v1.6.3 for jQuery UI](https://dubrox.github.io/Multiple-Dates-Picker-for-jQuery-UI/).
* [CMB Field Type: CMB2-radio-image](https://github.com/satwinderrathore/CMB2-radio-image): Image as radio buttons.
* [CMB2 Term Select](https://github.com/florianbeck/cmb2-field-type-tags): Special CMB2 Field that allows users to define an autocomplete text field for terms. _Note: this will set the taxonomy terms, but has the option (`'apply_term' => false`) to disable and save the term ids as data instead (like for options pages, etc)._
* [CMB2 Related Links](https://github.com/jtsternberg/CMB2-Related-Links): Allows users to add a related links via a repeating field group. Field inputs are powered by the [CMB2 Field Type: CMB2 Post Search field](https://github.com/CMB2/CMB2-Post-Search-field) documented above, and so each link can be populated with existing WordPress content by clicking on the search button. _Note: this is not a standard field type, but instead a function you use in combination with CMB2::add_field()._
* [CMB2 Field Type: Order](https://github.com/rubengc/cmb2-field-order) by [rubengc](https://github.com/rubengc): Allows users to define custom order of predefined options.
* [CMB2 Field Type: Animation](https://github.com/rubengc/cmb2-field-animation) by [rubengc](https://github.com/rubengc): Allows users to pickup an animation from [Animate.css](https://github.com/daneden/animate.css) (includes preview of chosen animation).
* [CMB2 Field Type: Ajax Search](https://github.com/rubengc/cmb2-field-ajax-search) by [rubengc](https://github.com/rubengc): Based on [CMB2 Field Type: Post Search Ajax](https://github.com/alexis-magina/cmb2-field-post-search-ajax), adds the ability to attach posts/users/terms, and the ability to limit the maximum number of attached objects.
* [CMB2 Field Type: Visual Style Editor](https://github.com/rubengc/cmb2-field-visual-style-editor) by [rubengc](https://github.com/rubengc): Custom field for CMB2 which allows customizing style from a small set of controls.
* [CMB2 Field Type: Content Wrap](https://github.com/rubengc/cmb2-field-content-wrap) by [rubengc](https://github.com/rubengc): Custom field for CMB2 to store a content wrap values (padding, margin or border width).
* [CMB2 Field JS Controls](https://github.com/rubengc/cmb2-field-js-controls) by [rubengc](https://github.com/rubengc): Show any field similar to Wordpress publishing actions (Post/Page post_status, visibility and post_date submit box field).
* [CMB2 Field Type: Position](https://github.com/rubengc/cmb2-field-position) by [rubengc](https://github.com/rubengc): CMB2 field type to setup a jquery UI position values.
* [CMB2 Field Type: CMB2 Roadway Segments](https://github.com/pixelwatt/cmb2-roadway-segments) by [pixelwatt](https://github.com/pixelwatt): This plugin adds a new CMB2 fieldtype for drawing roadway segments onto a map and provides a shortcode for display.
* [CMB2 Field Type: Font Awesome](https://github.com/serkanalgur/cmb2-field-faiconselect) by [serkanalgur](https://github.com/serkanalgur): This plugin adds a new CMB2 field type for selecting Font Awesome icons.
* [CMB2 Field Type: Typography](https://github.com/eduplessis/cmb2-typography) by [eduplessis](https://github.com/eduplessis): This plugin adds a new CMB2 field type "Typography" and it use jQuery fontselect for the font-family selection.
* [CMB2 Field Type: Markdown](https://github.com/Rekenna/cmb2-markdown) by [Rekenna](https://github.com/Rekenna): This plugin adds a new CMB2 field type "CMB2 Markdown" where you can type in markdown and view a live preview of the results or convert to html with a button.
* [CMB2 Field Type: Switch Button](https://github.com/themevan/CMB2-Switch-Button) by [themevan](https://github.com/themevan): This plugin adds a Custom Switch Button field type for CMB2.
* [CMB2 Field Type: select_plus](https://github.com/manzoorwanijk/cmb2-select-plus) from [manzoorwanijk](https://github.com/manzoorwanijk/): Select field type which acts much like the default `select` field. However, it adds the support for `optgroup` and saving of values with `multiple` attribute.
* [CMB2 Field Type: Address](https://github.com/scottsawyer/cmb2-field-address) by [scottsawyer](https://github.com/scottsawyer): Just a simple, repeatable address field.  It's really just the snippet from [CMB2 Snippet Library](https://github.com/CMB2/CMB2-Snippet-Library) converted to a plugin.
* [CMB2 Field Type: Link](https://github.com/scottsawyer/cmb2-field-link) by [scottsawyer](https://github.com/scottsawyer): Create a link field with some attributes. Very nice for styling links.
* [CMB2 Field Type: Widget Selector](https://github.com/scottsawyer/cmb2-field-widget-selector) by [scottsawyer](https://github.com/scottsawyer): Need a field that lets you ( or your editor ) select / display an existing widget instance? Then this is the plugin for you.

##### Other Helpful Resources
* [CMB2 WooCommerce HPOS Orders](https://github.com/CMB2/cmb2-woocommerce-hpos-orders): Adds the ability to add custom fields to the new WooCommerce HPOS orders page.
* [CMB2 Admin Extension](https://github.com/twoelevenjay/CMB2-Admin-Extension): Adds a UI to create CMB2 meta boxes from the WordPress admin. Also on [wordpress.org](https://wordpress.org/plugins/cmb2-admin-extension/).
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.
* [WDS-Simple-Page-Builder](https://github.com/WebDevStudios/WDS-Simple-Page-Builder): Uses existing template parts in the currently-active theme to build a customized page with rearrangeable elements. Built with CMB2.
* [CMB2 Example Theme](https://github.com/CMB2/CMB2-Example-Theme): Demonstrate how to include CMB2 in your theme, as well as some cool tips and tricks.
* [facetwp-cmb2](https://github.com//WebDevStudios/facetwp-cmb2): FacetWP integration with CMB2.
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for WordPress CMB2 library that allows the creation of columns for a better layout in the admin.
* [CMB2 Metatabs Options](https://github.com/rogerlos/cmb2-metatabs-options) from [rogerlos](https://github.com/rogerlos/): CMO makes it easy to create options pages with multiple metaboxes--and optional WordPress admin tabs.
* [CMB2 Conditionals](https://github.com/jcchavezs/cmb2-conditionals) from [jcchavezs](https://github.com/jcchavezs/): Allows developers to relate fields so the display of one is conditional on the value of another.
* [CMB2 Metabox Code Generator](https://willthemoor.github.io/cmb2-metabox-generator/) from [willthemoor](https://github.com/willthemoor/): Skip the boring bits. Use this generator to create fully functional CMB2 metaboxes easily. Now with bulk entry!
* [Caldera Metaplate](https://wordpress.org/plugins/caldera-metaplate/) by [CalderaWP](https://calderawp.com/): Not specific to CMB2, but allows creating templates for outputting your custom fields.
* [Yoast CMB2 Field Analysis WP Plugin](https://github.com/alexis-magina/yoast-cmb2-field-analysis) by [alexis-magina](https://github.com/alexis-magina): This plugin adds in a js based method of recalculating Yoast SEO's content scores when updating page content, specifically custom meta fields added via the CMB2 library.
* [Skeleton](https://github.com/awethemes/skeleton) by [awethemes](https://github.com/awethemes): A complete framework for WordPress, uses CMB2 engine.
* [WP Simple Iconfonts](https://wordpress.org/plugins/wp-simple-iconfonts/) by [awethemes](https://github.com/awethemes): An icon fonts manager and provides a font icon picker for CMB2.
* [CMB2 Nav Menus](https://github.com/nsrosenqvist/cmb2-nav-menus) by [nsrosenqvist](https://github.com/nsrosenqvist): Lets you use CMB2 in nav menu entries..

### Links
* [Project Homepage](http://cmb2.io)
* [Github project page](https://github.com/CMB2/CMB2)
* [Documentation (GitHub wiki)](https://github.com/CMB2/CMB2/wiki)
* [Snippet Library](https://github.com/CMB2/CMB2-Snippet-Library/)

**[View CHANGELOG](https://github.com/CMB2/CMB2/blob/master/CHANGELOG.md)**

### Known Issues

* Metabox containing WYSIWYG editor cannot be moved or used in a repeatable way at this time (this is a TinyMCE issue).
* Not all fields work well in a repeatable group.

== Installation ==

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

== Frequently Asked Questions ==

FAQ's usually end up in the [github wiki](https://github.com/CMB2/CMB2/wiki).

== Changelog ==

### 2.12.0

#### Enhancements
* Confirmed compatibility with PHP 8.2 and 8.3, and tested up to WordPress 7.0.
* [Development] Migrated end-to-end testing from Cypress to Playwright and replaced Grunt with npm scripts for the asset build pipeline. ([#1550](https://github.com/CMB2/CMB2/pull/1550)).
* [Development] Moved the local development and test environment to `@wordpress/env` (wp-env) on pinned ports, running both PHPUnit and Playwright through it. ([#1554](https://github.com/CMB2/CMB2/pull/1554), [#1558](https://github.com/CMB2/CMB2/pull/1558)).
* [Development] Replaced Travis CI with GitHub Actions, added a PHPCompatibility check (PHP 7.4+) and a PHPCS/WPCS lint gate, and overhauled `install-wp-tests.sh` for modern WordPress. ([#1555](https://github.com/CMB2/CMB2/pull/1555)).

#### Bug Fixes
* Fixed a PHP 8.1+ deprecation when sanitizing an empty `textarea`-based field (passing `null` to `wp_kses_post()`). Props [@baljindersingh88](https://github.com/baljindersingh88) ([#1537](https://github.com/CMB2/CMB2/pull/1537)).
* Fixed row iterator value desync between the data attribute and jQuery data when reordering repeatable group rows. Props [@angryaxi](https://github.com/angryaxi) ([#1518](https://github.com/CMB2/CMB2/pull/1518)).
* Fixed a PHP 8.3 `ltrim()` deprecation in the taxonomy field display.
* Hardened against PHP 8.2+ dynamic property deprecations by widening `#[AllowDynamicProperties]` to the class roots.
* Removed a focus-background rule on `.cmb2-wrap` inputs that caused unexpected input styling. Fixes [#1556](https://github.com/CMB2/CMB2/issues/1556)/[wordpress.org/support/topic/weird-checkbox-behaviour-on-wp-7](https://wordpress.org/support/topic/weird-checkbox-behaviour-on-wp-7/) ([#1557](https://github.com/CMB2/CMB2/pull/1557)).

### 2.11.0

#### Enhancements
* Package updates.
* Update WordPress Tested up to tag 6.1. Props [@RubenMartins](https://github.com/RubenMartins) ([#1477](https://github.com/CMB2/CMB2/pull/1477)).
* Add filters for setting `object_id` and `mb_object_type` and in `do_scripts` - Allows overriding by plugins/libs. (Added to support the new [CMB2 WooCommerce HPOS Orders](https://github.com/CMB2/cmb2-woocommerce-hpos-orders) extension)
* Added a `cmb2_init_hooks` hook when hookup is called.
* Addressed some security concerns with the unserialization process for the stored serialized `DateTime` field values (`text_datetime_timestamp_timezone` field type only). ([#1510](https://github.com/CMB2/CMB2/pull/1510))
* [Development] Some build script improvements.
* [Development] Added some PHPCS/WPCS config.
* [Development] Added a phpcompatibility action. Props [@jazzsequence](https://github.com/jazzsequence) ([#1499](https://github.com/CMB2/CMB2/pull/1499), [#1500](https://github.com/CMB2/CMB2/pull/1500)).

#### Bug Fixes
* Fix some line-height issues with dashicon buttons. Fixes [#1443](
* Fix issue where image can be attached to wrong group after removing previous group. ([#1473](https://github.com/CMB2/CMB2/pull/1473))
* Fixes issue where Select/Deselect all does not trigger change JS DOM events. Fixes [#1504](https://github.com/CMB2/CMB2/issues/1504).

### 2.10.1

#### Bug Fixes

* Fix issue with date picker formatting. Fixes [#1448](https://github.com/CMB2/CMB2/issues/1448).

### 2.10.0

#### Enhancements
* Sanitize URLs, defaulting to `https`. Props [@paulschreiber](https://github.com/paulschreiber) ([#1413](https://github.com/CMB2/CMB2/pull/1413)).
* Establish Cypress E2E Testing. Props [@markjaquith](https://github.com/markjaquith) ([#1437](https://github.com/CMB2/CMB2/pull/1437)).
* Updated the JS `shiftRows` functionality to be simpler, and fix issues with JS initialization. Fixes [#1426](https://github.com/CMB2/CMB2/issues/1426) and [#1431](https://github.com/CMB2/CMB2/issues/1431).
* Updated various NPM dependencies for security issues.

#### Bug Fixes
* Update to prevent deprecation notice:`Required parameter $i follows optional parameter $args...`. Props [@carloswph](https://github.com/carloswph) ([#1417](https://github.com/CMB2/CMB2/pull/1417)).
* Make each date field more resilient to various date/timestamp values passed in (from REST API).

### 2.9.0

#### Enhancements
* Added `cmb2_tab_group_tabs` filter for adding arbitrary menu page urls to the cmb2 tabs, and move tab markup output to separate method, `CMB2_Options_Hookup::options_page_tab_nav_output()`. Fixes [#1407](https://github.com/CMB2/CMB2/issues/1407).
* Limit use of italic, including removing from field descriptions. Fixes [#1404](https://github.com/CMB2/CMB2/issues/1404).
* Add to list of valid image types from `get_allowed_mime_types()`, which makes SVGs more reliable when using the [Safe SVG](https://wordpress.org/plugins/safe-svg/) plugin. Fixes [#1223](https://github.com/CMB2/CMB2/issues/1223).

#### Bug Fixes
* Fixes PHP warnings on repeatable ColorPicker with an array as default. Props [@rubengc](https://github.com/rubengc) ([#1340](https://github.com/CMB2/CMB2/pull/1340)).
* Address PHP 7.4, compatibility issues with `func_get_args()`. Fixes [#1389](https://github.com/CMB2/CMB2/issues/1389).
* Better generated array key for cached fields, fixes issue where wrong field is found. Fixes [#14053](https://github.com/CMB2/CMB2/issues/14053).
* Fix issue with options-pages being changed to register on a hook priority of `5` instead of the default `10`, causing some back-compatibility issues. Fixes [#1410](https://github.com/CMB2/CMB2/issues/1410).

### 2.8.0

#### Enhancements
* Added [CODE_OF_CONDUCT.md](https://github.com/CMB2/CMB2/blob/develop/CODE_OF_CONDUCT.md) file to meet GitHub Community standards. Props [@RubenMartins](https://github.com/RubenMartins) ([#1331](https://github.com/CMB2/CMB2/pull/1331)).
* Add ability to define the page-registration admin menu hook priority for options pages. Fixes [#1380](https://github.com/CMB2/CMB2/issues/1380).

#### Bug Fixes
* Ensure `enqueue wp-color-picker` is enqueued for color fields. Props [@rubengc](https://github.com/rubengc) ([#1339](https://github.com/CMB2/CMB2/pull/1339)).
* Fix empty name/id attributes on `'file_list'` buttons. Props [@pgroot91](https://github.com/pgroot91) ([#1347](https://github.com/CMB2/CMB2/pull/1347)).
* Fix `wysiwyg` field type not working in a group, by ensuring scripts properly enqueued. Props [@yoren](https://github.com/yoren) ([#1361](https://github.com/CMB2/CMB2/pull/1361)).
* Fix `$object_id` doc block types in helper-functions.php. Fixes [#1365](https://github.com/CMB2/CMB2/issues/1365).
* Fix Metabox toggles visually broken with WP 5.5.x. Fixes [#1382](https://github.com/CMB2/CMB2/issues/1382).
* Fix `PHP Deprecated: Required parameter $field_id follows optional parameter $type`, due to changes in PHP 8.0. Fixes [#1396](https://github.com/CMB2/CMB2/issues/1396).
* Fix PHP notice caused by `deprecated_param` method in PHP 7.4. Props [@jonathanstegall](https://github.com/jonathanstegall) ([#1400](https://github.com/CMB2/CMB2/pull/1400)).

### 2.7.0

#### Enhancements
* Added support for sortable columns by default, with ability to disable with `'column' => array( 'disable_sortable' => true )`. Props [@RubenMartins](https://github.com/RubenMartins) ([#1281](https://github.com/CMB2/CMB2/pull/1281)).
* New field type, `'taxonomy_select_hierarchical'`. Fixes [#751](https://github.com/CMB2/CMB2/issues/751)
* New `text`, `textarea` and `wysiwyg` character counter options. For now, this feature is not available to `wysiwyg` field types within repeatable groups. Props [@gyrus](https://github.com/gyrus) ([#1276](https://github.com/CMB2/CMB2/pull/1276)).
	- The new parameters:
		- `'char_counter'` - Defaults to false, no counter. Set to true, or `words` to count words instead of characters.
		- `'char_max'` - integer. When defined, counter shows remaining characters/words.
		- `'char_max_enforce'` - boolean, default: false. Currently only applied (as maxlength attribute) to `text` and `textarea` fields which use `'characters'` for counter.
	- You can also [override the default text strings](https://github.com/CMB2/CMB2/wiki/Field-Parameters#text) associated with these parameters:
		- `'words_left_text'` - Default: "Words left"
		- `'words_text'` - Default: "Words"
		- `'characters_left_text'` - Default: "Characters left"
		- `'characters_text'` - Default: "Characters"
		- `'characters_truncated_text'` - Default: "Your text may be truncated."
* Update styling to be more compatible with WordPress 5.3. Props [@galengidman](https://github.com/galengidman) ([#1314](https://github.com/CMB2/CMB2/pull/1314))
* Add a new box parameter, `register_rest_field_cb`, which when used allows overriding the way CMB2 handles the `register_rest_field` callbacks, and defining your own REST prefix for your fields. See [this PR comment](https://github.com/CMB2/CMB2/pull/1240#issuecomment-552548488) for more context.
* Cleanup by renaming `CMB2_hookup` to `CMB2_Hookup`. Classes are case-insensitive, so this is a backwards-compatible change. Props [@szepeviktor](https://github.com/szepeviktor) ([#1330](https://github.com/CMB2/CMB2/pull/1330), [#1328](https://github.com/CMB2/CMB2/issues/1328)).
* Validate composer.json for Travis CI. Props [@szepeviktor](https://github.com/szepeviktor) ([#1326](https://github.com/CMB2/CMB2/pull/1326)).
* Added [LICENSE](https://github.com/CMB2/CMB2/blob/develop/LICENSE) file to meet GitHub Community standards. Props [@RubenMartins](https://github.com/RubenMartins) ([#1316](https://github.com/CMB2/CMB2/pull/1316)).
* Add new `"cmb2_display_class_{$fieldtype}"` filter and `'display_class'` field parameter to allow specifying the class to use to display the field (in admin columns, etc).
* Update `CMB2_Types::_id()` to allow not appending the iterator attribute if a repeatable field.
* Added `CMB2_Utils::concat_attrs()` test for nested arrays as data attributes.
* Various updates per [VIP feedback](https://github.com/CMB2/CMB2/issues/1260). Props [@kevinlangleyjr](https://github.com/kevinlangleyjr), [@mikeselander](https://github.com/mikeselander) ([#1255](https://github.com/CMB2/CMB2/pull/1255), [#1257](https://github.com/CMB2/CMB2/pull/1257), [#1259](https://github.com/CMB2/CMB2/pull/1259),  [#1261](https://github.com/CMB2/CMB2/pull/1261) [#1262](https://github.com/CMB2/CMB2/pull/1262), and various direct commits. See [#1260](https://github.com/CMB2/CMB2/issues/1260)).

#### Bug Fixes
* Fix some issues with Travis. Props [@anhskohbo](https://github.com/anhskohbo) ([#1220](https://github.com/CMB2/CMB2/pull/1220)).
* Javascript: Correctly pass the newly created row to the `cmb2_add_row` triggered event.
* Various code-formatting and code documentation improvements. Props [@tw2113](https://github.com/tw2113).
* Don't exclude composer.json from the distribution. Props [@johnbillion](https://github.com/johnbillion) ([#1225](https://github.com/CMB2/CMB2/pull/1225)).
* Ignore some more directories in the distribution package. Props [@johnbillion](https://github.com/johnbillion) ([#1226](https://github.com/CMB2/CMB2/pull/1226)).
* Use `CMB2_Field::get_rest_value()` to get values for fields in the post REST API endpoints ([#1284](https://github.com/CMB2/CMB2/issues/1284)).
* Fix issue where oEmbed fields' live-preview would not work if the field was added within a group, along with some other similarly related issues. Fixes [#1157](https://github.com/CMB2/CMB2/issues/1157).
* Fix issue when using REST API for `file` and `text_datetime_timestamp_timezone` field types, the supporting field data was not provided (e.g. the file id for `file` field, and the `utc` value for the `text_datetime_timestamp_timezone` field). Fixes [https://wordpress.org/support/topic/cmb2-rest-api-image-file-field-as-an-object/](https://wordpress.org/support/topic/cmb2-rest-api-image-file-field-as-an-object/).
* Escaping Improvements to File Base and File Fields. Props [@tomjn](https://github.com/tomjn) ([#1296](https://github.com/CMB2/CMB2/pull/1296), [#1297](https://github.com/CMB2/CMB2/pull/1297)).
* Fix issue where repeatable CodeMirror textareas could not be clicked/draggged to highlight text. Props [@JPry](https://github.com/JPry) ([#1300](https://github.com/CMB2/CMB2/pull/1300)).
* `taxonomy_select_hierarchical` now saves to the correct location, the term relationships table. Props [@latheva](https://github.com/latheva) ([#1307](https://github.com/CMB2/CMB2/pull/1307)).
* Fix issue ([#1158](https://github.com/CMB2/CMB2/issues/1158)) where default REST API endpoints (e.g. `/wp/v2/{post_type}`) would show all boxes for all custom post types even though not registered to the post-type. Props [@Mte90](https://github.com/Mte90) ([#1238](https://github.com/CMB2/CMB2/pull/1238)).
* Update and cull npm dependencis. Fixes [#1308](https://github.com/CMB2/CMB2/issues/1308).
* Fix some jshint issues.
* Updated the WordPress embeds URL references.
* Updates to account for WordPress 5.2 oEmbed changes.
* Fix to ensure date picker fields can have a default value. Fixes [#1245](https://github.com/CMB2/CMB2/issues/1245).
* Added `function_exists( 'add_action' )` check to bootstrap file to ensure compatibility with composer usage. Props [@salcode](https://github.com/salcode) ([#1271](https://github.com/CMB2/CMB2/pull/1271), [#1270](https://github.com/CMB2/CMB2/issues/1270))
* Corrected link to facetwp-cmb2 in README.md. Props [@marcelreschke](https://github.com/marcelreschke) ([#1248](https://github.com/CMB2/CMB2/pull/1248)).
* Use `get_user_locale()` in admin area instead of `get_locale()`. Fixes [#1267](https://github.com/CMB2/CMB2/issues/1267).
* `CMB2::is_box_type()` now also checks for taxonomies if box is registered to "term" object type. This should fix some issues where CMB2 term meta was not showing up in REST API requests to the term endpoints.


### 2.6.0

#### Bug Fixes
* Remove superfluous method definitions. Props [@tnorthcutt](https://github.com/tnorthcutt) ([#1200](https://github.com/CMB2/CMB2/pull/1200)).
* Fix `rest_value_cb` registering of filter. Props [@lipemat](https://github.com/lipemat) ([#1212](https://github.com/CMB2/CMB2/pull/1212)).
* Do not trigger tinyMCE editor save for the activeEditor. Prevents cursor jump in Gutenberg. Fixes [#1202](https://github.com/CMB2/CMB2/issues/1202)
* Fix issue where making a field repeatable would generate a Javascript error because of missing sortable library. Props [@slaFFik](https://github.com/slaFFik) ([#1216](https://github.com/CMB2/CMB2/pull/1216)).
* Ensure value passed to `CMB2_Utils::filter_empty` from `CMB2::save_group_field` is always an array. ([#1026](https://github.com/CMB2/CMB2/issues/1026))
* Fix potential issue with test path location. Props [@quasel](https://github.com/quasel) ([#463](https://github.com/CMB2/CMB2/pull/463)).

#### Enhancements

* Updated PHPUnit version in composer.json. Props [@slaFFik](https://github.com/slaFFik) ([#1204](https://github.com/CMB2/CMB2/pull/1204)).
* Package.json: fix the need of global (old) grunt. Props [@slaFFik](https://github.com/slaFFik) ([#1206](https://github.com/CMB2/CMB2/pull/1206)).
* Add optional confirmation dialog to group field's Remove button. Example [documented in the example functions file](https://github.com/CMB2/CMB2/blob/12036e2dcdeb5b019e844b814eca154bb0eee791/example-functions.php#L525). Props [@slaFFik](https://github.com/slaFFik) ([#1208](https://github.com/CMB2/CMB2/pull/1208)).
* Add 'id' attribute on group field `.postbox` divs to ensure compatibility with scripts which expect ids there. Props [@amans2k](https://github.com/amans2k) ([#1108](https://github.com/CMB2/CMB2/pull/1108)).
* Make `CMB2_Option` properties accessible. ([#1052](https://github.com/CMB2/CMB2/issues/1052))
* New Before/After row hooks: `'cmb2_before_field_row'`, `"cmb2_before_{$field_type}_field_row"`, `"cmb2_after_{$field_type}_field_row"`, `'cmb2_after_field_row'`. Props [@rubengc](https://github.com/rubengc) ([#953](https://github.com/CMB2/CMB2/pull/953)).
* Introduce three new filters to filter field arguments: `'cmb2_field_defaults'`, `'cmb2_field_arguments_raw'`, `'cmb2_field_arguments'`. Props [@jrfnl](https://github.com/jrfnl) ([#588](https://github.com/CMB2/CMB2/pull/588)).

### 2.5.1

#### Bug Fixes

* Fix issue when the `core/editor` object does not exist (is undefined), causing incompatibility issues with Yoast and likely others. Fixes [#1197](https://github.com/CMB2/CMB2/issues/1197)

### 2.5.0

#### Enhancements

* Repeatable fields are now drag-sortable. Props [@lipemat](https://github.com/lipemat) ([#1142](https://github.com/CMB2/CMB2/pull/1142)).
* Update the `sv_SE` translation. Props [@edvind](https://github.com/edvind) ([#370](https://github.com/CMB2/CMB2/pull/370)).
* QA/PHPCS cleanup. Props [@tw2113](https://github.com/tw2113) ([#1179](https://github.com/CMB2/CMB2/pull/1179)).
* Add optional `'mb_callback_args'` CMB2 box property which allows defining the `$callback_args` passed into `add_meta_box()`. This allows using defining the new [Gutenberg/block-editor compatibility parameters](https://wordpress.org/gutenberg/handbook/extensibility/meta-box/). Fixes [#1191](https://github.com/CMB2/CMB2/issues/1191)
* Support any type of markup when customizing repeating group row. Props [@lipemat](https://github.com/lipemat) ([#1187](https://github.com/CMB2/CMB2/pull/1187)).
* Add `cmb_init_pickers` and `cmb_init_code_editors` Javascript events for allowing just-in-time configuration for pickers/editors.
* Fix field descriptions color contrast ratio for better accessibility. h/t [@rianrietveld](https://github.com/rianrietveld). Fixes [#1193](https://github.com/CMB2/CMB2/issues/1193).
* Add `CMB2_Field::get_rest_value()` method for sending value through several filters (`'cmb2_get_rest_value'`, `"cmb2_get_rest_value_{$field_type}"`, `"cmb2_get_rest_value_for_{$field_id}"` ) before sending to REST request.

#### Bug Fixes

* Fix the options page errors when using CMB2 in WordPress prior to 4.7. Props [@manzoorwanijk](https://github.com/manzoorwanijk) ([#1166](https://github.com/CMB2/CMB2/pull/1166)).
* Fix occasonal fatal errors that can occur by using callback functions directly vs `call_user_func`. Props [@manzoorwanijk](https://github.com/manzoorwanijk) ([#1177](https://github.com/CMB2/CMB2/pull/1177)).
* Fix issue where `wysiwyg` fields' visual tab wouldn't save content on Gutenberg/block-editor posts. Props [@staurand](https://github.com/staurand) ([#1190](https://github.com/CMB2/CMB2/pull/1190) fixes [#1156](https://github.com/CMB2/CMB2/issues/1156)).
* Fix issue when `remove_default` wouldn't actually remove the default taxonomy metabox when box registration used an alternate box context. Props [@lipemat](https://github.com/lipemat) ([#1147](https://github.com/CMB2/CMB2/pull/1147)).

### 2.4.2

#### Bug Fixes

* Do not enqueue/register WordPress code editor JS if there are no `textarea_code` fields registered on the page. Fixes [#1110](https://github.com/CMB2/CMB2/issues/1110).
* Do not set repeated `wysiwyg` field values to string "false" when boolean false. Fixes [#1138](https://github.com/CMB2/CMB2/issues/1138) (again!).

### 2.4.1

#### Bug Fixes

* Do not set repeated field values to string "false" when boolean false. Fixes [#1138](https://github.com/CMB2/CMB2/issues/1138).

### 2.4.0

#### Enhancements

* Enable linking options pages via tabbed-navigation. Will output tabbed navigation for options-pages which share the same `'tab_group'` CMB2 box property. [This snippet](https://github.com/CMB2/CMB2-Snippet-Library/blob/master/options-and-settings-pages/options-pages-with-tabs-and-submenus.php) demonstrates how to create a top-level menu options page with multiple submenu pages, each with the tabbed navigation. To specify a different tab title than the options-page title, set the `'tab_title'` CMB2 box property. See [#301](https://github.com/CMB2/CMB2/issues/301), [#627](https://github.com/CMB2/CMB2/issues/627).
* Complete the `zh-CN` translation. Props [@uicestone](https://github.com/uicestone) ([#1089](https://github.com/CMB2/CMB2/issues/1089)).
* Update the `nl_NL` translation. Props [@tammohaannl](https://github.com/tammohaannl) ([#1101](https://github.com/CMB2/CMB2/issues/1101)).
* Better display for white over transparent images (e.g. logos) by using a checkered background for images. ([#1103](https://github.com/CMB2/CMB2/issues/1103))
* Ability to disable the options [autoload parameter](https://codex.wordpress.org/Function_Reference/add_option#Parameters) via filter (`"cmb2_should_autoload_{$options_key}"`) or via a box parameter for `'options-page'` box registrations (`'autoload' => false,`). ([#1093](https://github.com/CMB2/CMB2/issues/1093))
* `'textarea_code'` field type now uses CodeMirror that is [used by WordPress](https://make.wordpress.org/core/2017/10/22/code-editing-improvements-in-wordpress-4-9/) ([#1096](https://github.com/CMB2/CMB2/issues/1096)). A field can opt-out to return to the previous behavior by specifying an `'options'` parameter:
`'options' => array( 'disable_codemirror' => true )`
	As with the other javascript-enabled fields, the code-editor defaults can be overridden via a `data-codeeditor` attribute. E.g:

	```php
	'attributes' => array(
		'data-codeeditor' => json_encode( array(
			'codemirror' => array(
				'mode' => 'css',
			),
		) ),
	),
	```
* Improve/add comment info banners at top of CMB2 CSS files.
* Added `resetBoxes`/`resetBox` Javascript methods for resetting CMB2 box forms.
* Improved styles for fields in the new-term form.
* New `CMB2_Boxes` methods for filtering instances of `CMB2`, `CMB2_Boxes::get_by( $property, $optional_compare )` and `CMB2_Boxes::filter_by( $property, $to_ignore = null )`.

#### Bug Fixes

* Fix the `'taxonomy_*'` fields when used for term fields/meta. Save the value to term-meta.
* Clear the CMB2 fields when a term is added. Fixes [#794](https://github.com/CMB2/CMB2/issues/794).
* Repeated fields now use registered field defaults for values. Fixes [#1137](https://github.com/CMB2/CMB2/issues/1137).
* Fixed the formatting for deprecated messages in the log.
* Prevent opening of media modal when clicking the file "Download" link. Fixes [#1130](https://github.com/CMB2/CMB2/issues/1130).

### 2.3.0

#### Enhancements

* Updated Italian translation. Props [@Mte90](https://github.com/Mte90) ([#1067](https://github.com/CMB2/CMB2/issues/1067)).
* Starting with this release, we are fully switching to the more communicative and standard [Semantic Versioning](https://semver.org/). ([#1061](https://github.com/CMB2/CMB2/issues/1061)).

#### Bug Fixes

* Update for compatibility with PHP 7.2 (e.g. fixes `Fatal error: Declaration of CMB2_Type_Colorpicker::render() must be compatible with CMB2_Type_Text::render($args = Array)...`). ([#1070](https://github.com/CMB2/CMB2/issues/1070), [#1074](https://github.com/CMB2/CMB2/issues/1074), [#1075](https://github.com/CMB2/CMB2/issues/1075)).

### Older versions

For the changelog of versions prior to 2.3.0, see [CHANGELOG.md](https://github.com/CMB2/CMB2/blob/master/CHANGELOG.md).
