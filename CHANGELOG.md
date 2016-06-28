# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased][unreleased]

## 2.2.2.1 - 2016-06-27

### Bug Fixes

* Fix issue that kept CMB2 stylesheet from being enqueued when using the [options-page snippets](https://github.com/WebDevStudios/CMB2-Snippet-Library/tree/master/options-and-settings-pages).
* Fix issue which caused the CMB2 column display styles to be enqueued in the wrong pages. Now only enqueues on admin pages with columns.

## 2.2.2 - 2016-06-27

### Enhancements

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

### Bug Fixes
* Fix issue with 'default' callback not being applied in all instances. Introduced new `CMB2_Field::get_default()` method, and `'default_cb'` field parameter. Using the `'default'` field parameter with a callback will be deprecated in the next few releases. ([#572](https://github.com/WebDevStudios/CMB2/issues/572)).
* Be sure to call `CMB2_Field::row_classes()` for group field rows. Also, update CSS to use the "cmb-type-group" classname instead of "cmb-repeat-group-wrap".
* Introduce new `'text'` and `'text_cb'` field parameters for overriding CMB2 text strings instead of using the `'options'` array. ([#630](https://github.com/WebDevStudios/CMB2/pull/630))
* Fix bug where the value of '0' could not be saved in group fields.
* Fix bug where a serialized empty array value in the database for a repeatable field would output as "Array".
* Allow for optional/empty money field. Props [@jrfnl](https://github.com/jrfnl) ([#577](https://github.com/WebDevStudios/CMB2/pull/577)).
* The `CMB2::$updated` parameter (which contains field ids for all fields updated during a save) now also correctly adds group field ids to the array.

## 2.2.1 - 2016-02-29

### Bug Fixes

* Fixes back-compatibility issue which could allow multiple CMB2 instances to load (causing fatal errors). ([#520](https://github.com/WebDevStudios/CMB2/pull/520))

## 2.2.0 - 2016-02-27

### Enhancements

* Term Meta! As of WordPress 4.4, [WordPress will have the ability to use term metadata](https://make.wordpress.org/core/2015/10/23/4-4-taxonomy-roundup/). CMB2 will work with term meta out of the box. To do so, see the example cmb registration in the `yourprefix_register_taxonomy_metabox` function in [example-functions.php](https://github.com/WebDevStudios/CMB2/blob/master/example-functions.php).
* New hooks which hook in after save field action: `'cmb2_save_field'` and `"cmb2_save_field_{$field_id}"`. Props [wpsmith](https://github.com/wpsmith) ([#475](https://github.com/WebDevStudios/CMB2/pull/475)).
* The "cmb2_sanitize_{$field_type}" hook now runs for every field type (not just custom types) so you can override the sanitization for all field types via a filter.
* `CMB2::show_form()` is now composed of 3 smaller methods, `CMB2::render_form_open()`, `CMB2::render_field()`, `CMB2::render_form_close()` ([#506](https://github.com/WebDevStudios/CMB2/pull/506)).
* RTL Style generated. Props [@devinsays](https://github.com/devinsays) ([#510](https://github.com/WebDevStudios/CMB2/pull/510)).
* Properly scope date/time-pickers styling by adding a class to only cmb2 picker instances. ([#527](https://github.com/WebDevStudios/CMB2/pull/527))
* Allow per-field overrides for the date/time/color picker options (wiki documentation: [Modify Field Date, Time, or Color Picker options](https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks#modify-field-date-time-or-color-picker-options))
* Fix some inline documentation issues. Props [@jrfnl](https://github.com/jrfnl) ([#579](https://github.com/WebDevStudios/CMB2/pull/579)).
* Include `.gitattributes` file for excluding development resources when using Composer. Props [@benoitchantre](https://github.com/benoitchantre) ([#575](https://github.com/WebDevStudios/CMB2/pull/575), [#53](https://github.com/WebDevStudios/CMB2/pull/53)).

### Bug Fixes

* Fixed issue with `'taxonomy_select'` field type where a term which evaluated falsey would not be displayed properly. Props [adamcapriola](https://github.com/adamcapriola) ([#477](https://github.com/WebDevStudios/CMB2/pull/477)).
* Fix issue with colorpickers not changing when sorting groups.
* `'show_option_none'` field parameter now works on taxonomy fields when explicitly setting to false.
* Fix so the date/time-picker javascript respects the `'date_format'` and `'time_format'` field parameters. Props [@yivi](https://github.com/yivi) ([#39](https://github.com/WebDevStudios/CMB2/pull/39), [#282](https://github.com/WebDevStudios/CMB2/pull/282), [#300](https://github.com/WebDevStudios/CMB2/pull/300), [#318](https://github.com/WebDevStudios/CMB2/pull/318), [#330](https://github.com/WebDevStudios/CMB2/pull/330), [#446](https://github.com/WebDevStudios/CMB2/pull/446), [#498](https://github.com/WebDevStudios/CMB2/pull/498)).
* Fix a sometimes-broken unit test. Props [JPry](https://github.com/JPry) ([#539](https://github.com/WebDevStudios/CMB2/pull/539)).
* Fix issue with oembed fields not working correctly on options pages. ([#542](https://github.com/WebDevStudios/CMB2/pull/542)).
* Fix issue with repeatable field <button> elements stealing focus from "submit" button.

## 2.1.2 - 2015-10-01

### Bug Fixes

* Fixes back-compatibility issue when adding fields array to the metabox registration. ([#472](https://github.com/WebDevStudios/CMB2/pull/472))

## 2.1.1 - 2015-09-30

### Enhancements

* Make all CMB2::save_fields arguments optional to fall-back to `$_POST` data. Props [JPry](https://github.com/JPry).
* New filter, `cmb2_non_repeatable_fields` for adding additional fields to the blacklist of repeatable field-types. Props [JPry](https://github.com/JPry) ([#430](https://github.com/WebDevStudios/CMB2/pull/430)).
* New recommended hook for adding metaboxes, `cmb2_admin_init`. Most metabox registration only needs to happen if in wp-admin, so there is no reason to register them when loading the front-end (and increase the memory usage). `cmb2_init` still exists to register metaboxes that will be used on the front-end or used on both the front and back-end. Instances of `cmb2_init` in example-functions.php have been switched to `cmb2_admin_init`.
* Add `'render_row_cb'` field parameter for overriding the field render method.
* Add `'label_cb'` field parameter for overriding the field label render method.
* Allow `CMB2_Types::checkbox()` method to be more flexible for extending by taking an args array and an `$is_checked` second argument.
* More thorough unit tests. Props [pglewis](https://github.com/pglewis), ([#447](https://github.com/WebDevStudios/CMB2/pull/447),[#448](https://github.com/WebDevStudios/CMB2/pull/448)).
* Update `CMB2_Utils::image_id_from_url` to be more reliable. Props [wpscholar](https://github.com/wpscholar), ([#453](https://github.com/WebDevStudios/CMB2/pull/453)).
* `cmb2_get_option` now takes a default fallback value as a third parameter.

### Bug Fixes

* Address issue where `'file'` and `'file_list'` field results were getting mixed. Props [augustuswm](https://github.com/augustuswm) ([#382](https://github.com/WebDevStudios/CMB2/pull/382), [#250](https://github.com/WebDevStudios/CMB2/pull/250), [#296](https://github.com/WebDevStudios/CMB2/pull/296)).
* Fix long-standing issues with radio and multicheck fields in repeatable groups losing their values when new rows are added. ([#341](https://github.com/WebDevStudios/CMB2/pull/341), [#304](https://github.com/WebDevStudios/CMB2/pull/304), [#263](https://github.com/WebDevStudios/CMB2/pull/263), [#246](https://github.com/WebDevStudios/CMB2/pull/246), [#150](https://github.com/WebDevStudios/CMB2/pull/150))
* Fixes issue where currently logged-in user's profile data would display in the "Add New User" screen fields. ([#427](https://github.com/WebDevStudios/CMB2/pull/427))
* Fixes issue where radio values/selections would not always properly transfer when shifting rows (up/down). Props [jamiechong](https://github.com/jamiechong) ([#429](https://github.com/WebDevStudios/CMB2/pull/429), [#152](https://github.com/WebDevStudios/CMB2/pull/152)).
* Fixes issue where repeatable groups display "Array" as the field values if group is left completely empty. ([#332](https://github.com/WebDevStudios/CMB2/pull/332),[#390](https://github.com/WebDevStudios/CMB2/pull/390)).
* Fixes issue with `'file_list'` fields not saving properly when in repeatable groups display. Props [jamiechong](https://github.com/jamiechong) ([#433](https://github.com/WebDevStudios/CMB2/pull/433),[#187](https://github.com/WebDevStudios/CMB2/pull/187)).
* Update `'taxonomy_radio_inline'` and `'taxonomy_multicheck_inline'` fields sanitization method to use the same method as the non-inline versions. Props [superfreund](https://github.com/superfreund) ([#454](https://github.com/WebDevStudios/CMB2/pull/454)).

## 2.1.0 - 2015-08-05

### Bug Fixes

* Fix user fields not saving. Props [achavez](https://github.com/achavez), ([#417](https://github.com/WebDevStudios/CMB2/pull/417)).

## 2.0.9 - 2015-07-28

### Enhancements

* Updated/Added many translations. Props [fxbenard](https://github.com/fxbenard), ([#203](https://github.com/WebDevStudios/CMB2/pull/344)) and [Mte90](https://github.com/Mte90) for the Italian translation.
* Updated `'file_list'` field type to have a more intuitive selection in the media library, and updated the 'Use file' text in the button. Props [SteveHoneyNZ](https://github.com/SteveHoneyNZ) ([#357](https://github.com/WebDevStudios/CMB2/pull/357), [#358](https://github.com/WebDevStudios/CMB2/pull/358)).
* `'closed'` group field option parameter introduced in order to set the groups as collapsed by default. Requested in [#391](https://github.com/WebDevStudios/CMB2/issues/391).
* Added `"cmb2_{$object_type}_process_fields_{$cmb_id}"` hook for hooking in and modifying the metabox or fields before the fields are processed/sanitized for saving.
* Added Comment Metabox support. Props [GregLancaster71](https://github.com/GregLancaster71) ([#238](https://github.com/WebDevStudios/CMB2/pull/238), [#244](https://github.com/WebDevStudios/CMB2/pull/244)).
* New `"cmb2_{$field_id}_is_valid_img_ext"`` filter for determining if a field value has a valid image file-type extension.

### Bug Fixes

* `'multicheck_inline'`, `'taxonomy_radio_inline'`, and `'taxonomy_multicheck_inline'` field types were not outputting anything since it's value was not being returned. Props [ediamin](https://github.com/ediamin), ([#367](https://github.com/WebDevStudios/CMB2/pull/367), ([#405](https://github.com/WebDevStudios/CMB2/pull/405)).
* `'hidden'` type fields were not honoring the `'show_on_cb'` callback. Props [JPry](https://github.com/JPry), ([commits](https://github.com/WebDevStudios/CMB2/compare/5a4146eec546089fbe1a1c859d680dfda3a86ee2...1ef5ef1e3b2260ab381090c4abe9dc7234cfa0a6)).
* Fixed: There was no minified cmb2-front.min.css file.
* Fallback for fatal error with invalid timezone. Props [ryanduff](https://github.com/ryanduff) ([#385](https://github.com/WebDevStudios/CMB2/pull/385)).
* Fix issues with deleting a row from repeatable group. Props [yuks](https://github.com/yuks) ([#387](https://github.com/WebDevStudios/CMB2/pull/387)).
* Ensure value passed to `strtotime` in `make_valid_time_stamp` is cast to a string. Props [vajrasar](https://github.com/vajrasar) ([#389](https://github.com/WebDevStudios/CMB2/pull/389)).
* Fixed issue with Windows IIS and bundling CMB2 in the theme. Props [DevinWalker](https://github.com/DevinWalker), ([#400](https://github.com/WebDevStudios/CMB2/pull/400), [#401](https://github.com/WebDevStudios/CMB2/pull/401))

## 2.0.8 - 2015-06-01

### Bug Fixes

* Fix color-picker field not enqueueing the colorpicker script. ([#333](https://github.com/WebDevStudios/CMB2/issues/333))

## 2.0.7 - 2015-05-28

### Enhancements

* Ability to use non-repeatable group fields by setting the `'repeatable'` field param to `false` when registering a group field type. Props [marcusbattle](https://github.com/marcusbattle), ([#159](https://github.com/WebDevStudios/CMB2/pull/159)).
* Add and enqeueue a front-end specific CSS file which adds additional styles which are typically covered by wp-admin css. ([#311](https://github.com/WebDevStudios/CMB2/issues/311))
* Better handling of the CMB2 javascript (and CSS) required dependencies array. Dependencies are now only added conditionally based on the field types that are actually visible. ([#136](https://github.com/WebDevStudios/CMB2/issues/136))
* **THIS IS A BREAKING CHANGE:** The `group` field type's `'show_on_cb'` parameter now receives the `CMB2_Field` object instance as an argument instead of the `CMB2` instance. If you're using the `'show_on_cb'` parameter for a `group` field, please adjust accordingly. _note: you can still retrieve the `CMB2` instance via the `cmb2_get_metabox` helper function._
* New dynamic hook, `"cmb2_save_{$object_type}_fields_{$this->cmb_id}"`, to complement the existing `"cmb2_save_{$object_type}_fields"` hook.
* New CMB2 parameter, `enqueue_js`, to disable the enqueueing of the CMB2 Javascript.
* German translation provided by Friedhelm Jost.

### Bug Fixes

* Fix incorrect repeatable group title number. ([#310](https://github.com/WebDevStudios/CMB2/pull/310))
* Fix obscure bug which prevented group field arguments from being passed to the sub-fields (like `show_names` and `context`).
* Fixed occasional issue when adding a group row, the previous row's content would be cloned. ([#257](https://github.com/WebDevStudios/CMB2/pull/257))

## 2.0.6 - 2015-04-30

### Enhancements

* New metabox/form parameter, `show_on_cb`, allows you to conditionally display a cmb metabox/form via a callback. The `$cmb` object gets passed as a parameter to the callback. This complements the `'show_on_cb'` parameter that already exists for individual fields. Using this callback is similar to using the `'cmb2_show_on'` filter, but only applies to that specific metabox and it is recommended to use this callback instead as it minimizes th risk that your filter will affect other metaboxes.
* Taxonomy types no longer save a value. The value getting saved was causing confusion and is not meant to be used. To use the saved taxonomy data, you need to use the WordPress term api, `get_the_terms `, `get_the_term_list`, etc.
* Add `'multiple'` field parameter to store values in individual rows instead of serialized array. Will only work if field is not repeatable or a repeatable group. Props [JohnyGoerend](https://github.com/JohnyGoerend). ([#262](https://github.com/WebDevStudios/CMB2/pull/262), [#206](https://github.com/WebDevStudios/CMB2/issues/206), [#45](https://github.com/WebDevStudios/CMB2/issues/45)).
* Portuguese (Brazil) translation provided by [@lucascdsilva](https://github.com/lucascdsilva) - [#293](https://github.com/WebDevStudios/CMB2/pull/293).
* Spanish (Spain) translation updated by [@yivi](https://github.com/yivi) - [#272](https://github.com/WebDevStudios/CMB2/pull/272).
* Added group field callback parameters, `'before_group'`, `'before_group_row'`, `'after_group_row'`, `'after_group'` to complement the `'before_row'`, `'before'`, `'after'`, `'after_row'` field parameters.
* Better styling for `title` fields and `title` descriptions on options pages.
* Add a `sanitization_cb` field parameter check for the `group` field type.
* Better function/file doc-blocks to provide better documentation for automated documentation tools. See: [cmb2.io/api](http://cmb2.io/api/).
* `cmb2_print_metabox_form`, `cmb2_metabox_form`, and `cmb2_get_metabox_form` helper functions now accept two new parameters:
	* an `'object_type'` parameter to explictly set that in the `$cmb` object.
	* an `'enqueue_js'` parameter to explicitly disable the CMB JS enqueue. This is handy if you're not planning on using any of the fields which require JS (like color/date pickers, wysiwyg, file, etc).

### Bug Fixes

* Fix issue with oembed fields in repeatable groups where changing video changed it for all fields in a group.
* Fix empty arrays (like in the group field) saving as a value.
* Move `'cmb2_override_meta_value'` and `"cmb2_override_{$field_id}_meta_value"` filters to the `CMB2_Field::get_data()` method so that the filters are applied every time the data is requested. **THIS IS A BREAKING CHANGE:** The parameters for those filters have changed a bit. Previously, the filters accepted 5 arguments, `$value`, `$object_id`, `$field_args`, `$object_type`, `$field`. They have changed to accept 4 arguments instead, `$value`, `$object_id`, `$args`, `$field`, where `$args` is an array that contains the following:
	* @type string $type     The current object type
	* @type int    $id       The current object ID
	* @type string $field_id The ID of the field being requested
	* @type bool   $repeat   Whether current field is repeatable
	* @type bool   $single   Whether current field is a single database row


## 2.0.5 - 2015-03-17

### Bug Fixes

* Fix grouped fields display (first field was being repeated), broken in 2.0.3.

## 2.0.4 - 2015-03-16

### Enhancements

* `select`, `radio`, `radio_inline` field types now all accept the `'show_option_none'` field parameter. This parameter allows you to set the text to display for showing a 'no selection' option. Default will be `false`, which means a 'none' option will not be added. Set to `true` to use the default text, 'None', or specify another value, i.e. 'No selection'.

### Bug Fixes

* Fix back-compatibility when adding group field sub-fields via old array method (vs using the `CMB2:add_group_field()` method). Thanks to [norcross](https://github.com/norcross) for reporting.
* Fix occasional jQuery issues with group-field indexes.

## 2.0.3 - 2015-03-15

### Enhancements

* New constant, `CMB2_DIR`, which stores the file-path to the CMB2 directory.
* `text_date`, `text_time`, `text_date_timestamp`, `text_datetime_timestamp`, and ` text_datetime_timestamp_timezone` field types now take an arguments array so they can be extended by custom field types.
* Removed auto-scroll when adding groups. To re-add the feature, use the [snippet/plugin here](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/javascript/cmb2-auto-scroll-to-new-group.php). ([#205](https://github.com/WebDevStudios/CMB2/issues/205))
* Updated Timepicker utilizing the [@trentrichardson](https://github.com/trentrichardson) jQuery Timepicker add-on (https://github.com/trentrichardson/jQuery-Timepicker-Addon), and updated Datepicker styles. Props [JonMasterson](https://github.com/JonMasterson). ([#204](https://github.com/WebDevStudios/CMB2/issues/204), [#206](https://github.com/WebDevStudios/CMB2/issues/206), [#45](https://github.com/WebDevStudios/CMB2/issues/45)).
* Added a callback option for the field default value. The callback gets passed an array of all the field parameters as the first argument, and the field object as the second argument. (which means you can get the post id using `$field->object_id`). ([#233](https://github.com/WebDevStudios/CMB2/issues/233)).
* New `CMB2::get_field()` method and `cmb2_get_field` helper function for retrieving a `CMB2_Field` object from the array of registered fields for a metabox.
* New `CMB2::get_sanitized_values()` method and `cmb2_get_metabox_sanitized_values` helper function for retrieving sanitized values from an array of values (usually `$_POST` data).
* New `'save_fields'` metabox parameter that can be used to disable (by setting `'save_fields' => false`) the automatic saving of the fields when the form is submitted. These can be useful when you want to handle the saving of the fields yourself, or want to use submitted data for other purposes like generating new posts, or sending emails, etc.

### Bug Fixes

* Fix commented out text_datetime_timestamp_timezone field registration example in `example-functions.php`. Props [cliffordp](https://github.com/cliffordp), ([#203](https://github.com/WebDevStudios/CMB2/pull/203)).
* Fix sidebar styling for money fields and fields with textareas. ([#234](https://github.com/WebDevStudios/CMB2/issues/234))
* Fix `CMB2_Sanitize` class to properly use the stripslashed value (which was added in [#162](https://github.com/WebDevStudios/CMB2/pull/162) but never used). Props [dustyf](https://github.com/dustyf), ([#241](https://github.com/WebDevStudios/CMB2/pull/241)).

## 2.0.2 - 2015-02-15

### Enhancements

* Use the more appropriate `add_meta_boxes` hook for hooking in metaboxes to post-edit screen. Thanks [@inspiraaz](https://github.com/inspiraaz) for reporting. ([#161](https://github.com/WebDevStudios/CMB2/issues/161))
* Add a `row_classes` field param which allows you to add additional classes to the cmb-row wrap. This parameter can take a string, or array, or can take a callback that returns a string or array. The callback will receive `$field_args` as the first argument, and the CMB2_Field `$field` object as the second argument. Reported/requested in [#68](https://github.com/WebDevStudios/CMB2/issues/68).
* New constant, `CMB2_LOADED`, which you can use to check if CMB2 is loaded for your plugins/themes with CMB2 dependency.
* New hooks, [`cmb2_init_before_hookup` and `cmb2_after_init`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions).
* New API for adding metaboxes and fields, demonstrated in [`example-functions.php`](https://github.com/WebDevStudios/CMB2/blob/master/example-functions.php). In keeping with backwards-compatibility, the `cmb2_meta_boxes` filter method will still work, but is not recommended. New API includes `new_cmb2_box` helper function to generate a new metabox, and returns a `$cmb` object to add new fields (via the `CMB2::add_field()` and `CMB2::add_group_field()` methods).
* New CMB2 method, [`CMB2::remove_field()`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions/cmb2_init_%24cmb_id-remove-field.php).
* New CMB2_Boxes method, [`CMB2_Boxes::remove()`](https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/filters-and-actions/cmb2_init_before_hookup-remove-cmb2-metabox.php).
* When clicking on a file/image in the `file`, or `file_list` type, the media modal will open with that image selected. Props [johnsonpaul1014](https://github.com/johnsonpaul1014), ([#120](https://github.com/WebDevStudios/CMB2/pull/120)).


## 2.0.1 - 2015-02-02

2.0.1 is the official version after beta, and includes all the changes from 2.0.0 (beta).

## 2.0.0(beta) - 2014-08-16

2.0.0 is the official version number for the transition to CMB2, and 2.0.1 is the official version after beta. It is a complete rewrite. Improvements and fixes are listed below. __Note: This release requires WordPress 3.8+__
 
### Enhancements

* Converted `<table>` markup to more generic `<div>` markup to be more extensible and allow easier styling.
* Much better handling and display of repeatable groups.
* Entirely translation-ready [with full translations](http://wp-translations.org/project/cmb2/) in Spanish, French (Props [@fredserva](https://github.com/fredserva) - [#127](https://github.com/WebDevStudios/CMB2/pull/127)), Finnish (Props [@onnimonni](https://github.com/onnimonni) - [#108](https://github.com/WebDevStudios/CMB2/pull/108)), Swedish (Props [@EyesX](https://github.com/EyesX) - [#141](https://github.com/WebDevStudios/CMB2/pull/141)), and English.
* Add cmb fields to new user page. Props [GioSensation](https://github.com/GioSensation), ([#645](https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/pull/645)).
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
