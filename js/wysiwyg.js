/**
 * Used for WYSIWYG logic
 */
window.wysiwyg = (function($) {
	'use strict';

	// Private variables
	var toBeDestroyed = [],
		toBeInitialized = [];

	// Private functions

	/**
	 * Destroys any editors that weren't destroyed because they didn't exist yet.
	 */
	function delayedDestroy() {
		toBeDestroyed.forEach(function(id) {
			toBeDestroyed.splice(toBeDestroyed.indexOf(id), 1);
			my.destroy(id);
		});
	}

	/**
	 * Initializes any editors that weren't initialized because they didn't exist yet.
	 */
	function delayedInit() {

		// Don't initialize until they've all been destroyed.
		if (toBeDestroyed.length === 0) {
			toBeInitialized.forEach(function (id) {
				toBeInitialized.splice(toBeInitialized.indexOf(id), 1);
				init(id);
			});
		} else {
			window.setTimeout(delayedInit, 100);
		}
	}

	function init(id) {

		// The destroys might not have happened yet.  Don't init until they have.
		if (toBeDestroyed.length === 0) {
			tinyMCE.init(tinyMCEPreInit.mceInit[id]);

			// Also, switch back to visual to start if on text, because making the editor makes it go to visual mode.
			$('#' + id).parents('.wp-editor-wrap').removeClass('html-active').addClass('tmce-active');
		} else {
			toBeInitialized.push(id);
			window.setTimeout(delayedInit, 100);
		}
	}

	// Public variables
	var my = {
	};

	/**
	 * Initializes editors for a new CMB row
	 *
	 * @param jQuery $groupRow
	 */
	my.init = function($groupRow) {
		$groupRow.find('.wp-editor-wrap').each(function(i, el) {
			var $el = $(el),
				$textarea = $el.find('textarea'),
				id = $textarea.attr('id');

			// Replace the old editor with a new one and build it.
			$el.html('' +
				'<div class="wp-editor-tools hide-if-no-js">' +
				'	 <div class="wp-media-buttons">' +
				'			<a href="#" class="button insert-media add_media" data-editor="' + id + '" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>' +
				'  </div>' +
				'   <div class="wp-editor-tabs">' +
				'     <button id="' + id + '-tmce" type="button" class="wp-switch-editor switch-tmce" data-wp-editor-id="' + id + '">Visual</button>' +
				'     <button id="' + id + '-html" type="button" class="wp-switch-editor switch-html" data-wp-editor-id="' + id + '">Text</button>' +
				'   </div>' +
				' </div>' +
				' <div class="wp-editor-container">' +
				'    <textarea rows="' + $textarea.attr('rows') + '" class="block-content-editor" id="' + id + '" name="' + $textarea.attr('name') + '"></textarea>' +
				' </div>');

			// Use the settings from the first editor in the group.
			var firstRowId = $groupRow.parents('.cmb-repeatable-group').find('.wp-editor-wrap textarea').eq(0).attr('id');

			var settings = $.extend({}, tinyMCEPreInit.mceInit[firstRowId]);
			settings.selector = '#' + id;
			tinyMCEPreInit.mceInit[id] = settings;
			var qtSettings = $.extend({}, tinyMCEPreInit.qtInit[firstRowId]);
			qtSettings.id = id;
			tinyMCEPreInit.qtInit[id] = qtSettings;

			tinyMCE.init(tinyMCEPreInit.mceInit[id]);
			window.quicktags(qtSettings);

			// This needs to be called here due to the late initialization.  If it isn't, the buttons won't appear.
			window.QTags._buttonsInit();

			// Also, switch back to visual to start if on text, because making the editor makes it go to visual mode.  If the first editor
			// started on text, this will be needed.
			$('#' + id).parents('.wp-editor-wrap').removeClass('html-active').addClass('tmce-active');
		});
	};

	/**
	 * Destroys one editor
	 *
	 * @param string id
	 */
	my.destroy = function(id) {

		// The editor might not be initialized yet.  But we need to destroy it once it is.
		var editor = tinyMCE.get(id);

		if (editor) {
			editor.destroy();
		} else if (toBeDestroyed.indexOf(id) === -1) {
			toBeDestroyed.push(id);
			window.setTimeout(delayedDestroy, 100);
		}
	};

	/**
	 * Destroys all editors in a group
	 *
	 * @param jQuery $group
	 */
	my.destroyAll = function($group) {
		$group.find('.wp-editor-wrap textarea').each(function(i, el) {
			my.destroy($(el).attr('id'));
		});
	};

	/**
	 * Re-initializes existing editors in this group
	 *
	 * @param jQuery $group
	 */
	my.reinitAll = function($group) {
		$group.find('.wp-editor-wrap textarea').each(function(i, el) {
			var innerId = $(el).attr('id');
			init(innerId);
		});
	};

	return my;
})(jQuery);