# CMB2

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/CMB2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Travis](https://img.shields.io/travis/CMB2/CMB2.svg)](https://travis-ci.org/CMB2/CMB2/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/CMB2/CMB2.svg)](https://scrutinizer-ci.com/g/CMB2/CMB2/?branch=develop)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/CMB2/CMB2.svg)](https://scrutinizer-ci.com/g/CMB2/CMB2/?branch=develop)
[![Project Stats](https://www.openhub.net/p/CMB2/widgets/project_thin_badge.gif)](https://www.openhub.net/p/CMB2)

![CMB2](https://plugins.trac.wordpress.org/export/HEAD/cmb2/assets/banner-1544x500.png)

**Contributors:**      [jtsternberg](https://github.com/jtsternberg), [webdevstudios](https://github.com/webdevstudios), [zao](https://github.com/zao-web), [humanmade](https://github.com/humanmade)  
**Homepage:**          [https://cmb2.io](https://cmb2.io)  
**Tags:**              metaboxes, forms, fields, options, settings  
**Requires at least:** 3.8.0  
**Tested up to:**      5.3.2  
**Stable tag:**        2.7.0  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Wordpress plugin](https://img.shields.io/wordpress/plugin/v/cmb2.svg)](https://wordpress.org/plugins/cmb2/)
[![Wordpress](https://img.shields.io/wordpress/plugin/dt/cmb2.svg)](https://wordpress.org/plugins/cmb2/)
[![Wordpress rating](https://img.shields.io/wordpress/plugin/r/cmb2.svg)](https://wordpress.org/plugins/cmb2/)

Complete contributors list found here: [github.com/CMB2/CMB2/graphs/contributors](https://github.com/CMB2/CMB2/graphs/contributors)

## Description

CMB2 is a developer's toolkit for building metaboxes, custom fields, and forms for WordPress that will blow your mind. Easily manage meta for posts, terms, users, comments, or create custom option pages.

**[Download plugin on wordpress.org](https://wordpress.org/plugins/cmb2/)**

CMB2 is a complete rewrite of [Custom Metaboxes and Fields for WordPress](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress). To get started, please follow the examples in the included `example-functions.php` file and have a look at the [basic usage instructions](https://github.com/CMB2/CMB2/wiki/Basic-Usage).

You can see a list of available field types [here](https://github.com/CMB2/CMB2/wiki/Field-Types#types).

If you are coming from the original "Custom Metaboxes and Fields for WordPress" plugin, [please read this post](https://webdevstudios.com/2015/02/02/cmb2-wordpress-plugin/) for the CMB2 background story.

### Contribution
Development occurs on Github, and all contributions welcome. Please read the [CONTRIBUTING](https://github.com/CMB2/CMB2/blob/master/CONTRIBUTING.md) doc for more details.

A complete list of all our awesome contributors found here: [github.com/CMB2/CMB2/graphs/contributors](https://github.com/CMB2/CMB2/graphs/contributors)

## Features:

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

## Translation
If you are looking to provide language translation files, Please do so via [WordPress Plugin Translations](https://translate.wordpress.org/projects/wp-plugins/cmb2).

## 3rd Party Resources

#### Custom Field Types
* [CMB2 Field Type: CMB Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) from [coreymcollins](https://github.com/coreymcollins): `custom_attached_posts`, for attaching posts to a page.
* [CMB2 Field Type: Post Search Ajax](https://github.com/alexis-magina/cmb2-field-post-search-ajax) by [alexis-magina](https://github.com/alexis-magina): `post_search_ajax` Attach posts to each other. Same approach as [CMB2 Attached Posts Field](https://github.com/coreymcollins/cmb-attached-posts) but with Ajax request, multiple/single option, and different UI.
* [CMB2 Field Type: CMB2 Post Search field](https://github.com/CMB2/CMB2-Post-Search-field): `post_search_text` adds a post-search dialog for searching/attaching other post IDs.
* [CMB2 Field Type: CMB2 User Search field](https://github.com/Mte90/CMB2-User-Search-field) from [Mte90](https://github.com/Mte90): `user_search_text` adds a user-search dialog for searching/attaching other User IDs.
* [CMB2 Field Type: Google Maps](https://github.com/mustardBees/cmb_field_map) from [mustardBees](https://github.com/mustardBees): Custom field type for Google Maps.
	> The `pw_map` field stores the latitude/longitude values which you can then use to display a map in your theme.
	
* [CMB2 Field Type: Leaflet Maps](https://github.com/villeristi/CMB2-field-Leaflet-Geocoder) from [villeristi](https://github.com/villeristi): Custom field type for [Leaflet](http://leafletjs.com/) Maps.
* [CMB2 Field Type: Select2](https://github.com/mustardBees/cmb-field-select2) from [mustardBees](https://github.com/mustardBees): Custom field types which use the [Select2](http://ivaynberg.github.io/select2/) script:

	> 1. The `pw_select field` acts much like the default select field. However, it adds typeahead-style search allowing you to quickly make a selection from a large list
	> 2. The `pw_multiselect` field allows you to select multiple values with typeahead-style search. The values can be dragged and dropped to reorder

* [CMB Field Type: Slider](https://github.com/qmatt/cmb2-field-slider) from [mattkrupnik](https://github.com/mattkrupnik/): Adds a jQuery UI Slider field.
* [WDS CMB2 Date Range Field](https://github.com/CMB2/CMB2-Date-Range-Field) from [dustyf](https://github.com/dustyf) of [WebDevStudios](https://github.com/WebDevStudios): Adds a date range field.
* [CMB2 Remote Image Select](https://github.com/CMB2/CMB2-Remote-Image-Select-Field) from [JayWood](https://github.com/JayWood) of [WebDevStudios](https://github.com/WebDevStudios): Allows users to enter a URL in a text field and select a single image for use in post meta. Similar to Facebook's featured image selector.
* [CMB Field Type: Sorter](https://wordpress.org/plugins/cmb-field-type-sorter/): This plugin gives you two CMB field types based on the Sorter script.
* [CMB Field Type: Tags](https://github.com/florianbeck/cmb2-field-type-tags): WordPress-Tags-like field type for CMB2. _note: this does not set the post tags, but simply provides a unique text input_
* [CMB Field Type: Link Picker](https://wordpress.org/plugins/link-picker-for-cmb2/): Using the Link Picker for CMB2 control, you can choose a link from your WordPress site, or manually enter a link. You can also identify if the link should open in a new window, or not.
* [CMB Field Type: MultidatesPicker](https://github.com/origgami/cmb2-multidates-picker): Creates a CMB2 field type that enables a multiple date calendar. It uses a plugin called [MultiDatesPicker v1.6.3 for jQuery UI](http://multidatespickr.sourceforge.net/).
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

#### Other Helpful Resources
* [CMB2 Admin Extension](https://github.com/twoelevenjay/CMB2-Admin-Extension): Adds a UI to create CMB2 meta boxes from the WordPress admin. Also on [wordpress.org](https://wordpress.org/plugins/cmb2-admin-extension/).
* [WordPress Shortcode Button](https://github.com/jtsternberg/Shortcode_Button): Uses CMB2 fields to generate fields for shortcode input modals.
* [WDS-Simple-Page-Builder](https://github.com/WebDevStudios/WDS-Simple-Page-Builder): Uses existing template parts in the currently-active theme to build a customized page with rearrangeable elements. Built with CMB2.
* [CMB2 Example Theme](https://github.com/CMB2/CMB2-Example-Theme): Demonstrate how to include CMB2 in your theme, as well as some cool tips and tricks.
* [facetwp-cmb2](https://github.com//WebDevStudios/facetwp-cmb2): FacetWP integration with CMB2.
* [CMB2-grid](https://github.com/origgami/CMB2-grid) from [origgami](https://github.com/origgami/): A grid system for WordPress CMB2 library that allows the creation of columns for a better layout in the admin.
* [CMB2 Metatabs Options](https://github.com/rogerlos/cmb2-metatabs-options) from [rogerlos](https://github.com/rogerlos/): CMO makes it easy to create options pages with multiple metaboxes--and optional WordPress admin tabs.
* [CMB2 Conditionals](https://github.com/jcchavezs/cmb2-conditionals) from [jcchavezs](https://github.com/jcchavezs/): Allows developers to relate fields so the display of one is conditional on the value of another.
* [CMB2 Metabox Code Generator](http://willthemoor.github.io/cmb2-metabox-generator/) from [willthemoor](https://github.com/willthemoor/): Skip the boring bits. Use this generator to create fully functional CMB2 metaboxes easily. Now with bulk entry!
* [Caldera Metaplate](https://wordpress.org/plugins/caldera-metaplate/) by [CalderaWP](https://calderawp.com/): Not specific to CMB2, but allows creating templates for outputting your custom fields.
* [Yoast CMB2 Field Analysis WP Plugin](https://github.com/alexis-magina/yoast-cmb2-field-analysis) by [alexis-magina](https://github.com/alexis-magina): This plugin adds in a js based method of recalculating Yoast SEO's content scores when updating page content, specifically custom meta fields added via the CMB2 library.
* [Skeleton](https://github.com/awethemes/skeleton) by [awethemes](https://github.com/awethemes): A complete framework for WordPress, uses CMB2 engine.
* [WP Simple Iconfonts](https://wordpress.org/plugins/wp-simple-iconfonts/) by [awethemes](https://github.com/awethemes): An icon fonts manager and provides a font icon picker for CMB2.
* [CMB2 Nav Menus](https://github.com/nsrosenqvist/cmb2-nav-menus) by [nsrosenqvist](https://github.com/nsrosenqvist): Lets you use CMB2 in nav menu entries..

## Links
* [Project Homepage](https://cmb2.io)
* [Github project page](https://github.com/CMB2/CMB2)
* [Documentation (GitHub wiki)](https://github.com/CMB2/CMB2/wiki)
* [Snippet Library](https://github.com/CMB2/CMB2-Snippet-Library/)

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

**[View CHANGELOG](https://github.com/CMB2/CMB2/blob/master/CHANGELOG.md)**

## Known Issues

* Not all fields work well in a repeatable group.

