/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author WebDevStudios
 * @see    https://github.com/WebDevStudios/CMB2
 */

 // TODO: fix this.
 // JQMIGRATE: jQuery.fn.attr('value') no longer gets properties

/**
 * Custom jQuery for Custom Metaboxes and Fields
 */
window.CMB2 = window.CMB2 || {};
(function(window, document, $, cmb, undefined){
	'use strict';

	// localization strings
	var l10n = window.cmb2_l10;
	var setTimeout = window.setTimeout;
	var $document;
	var $id = function( selector ) {
		return $( document.getElementById( selector ) );
	};
	var defaults = {
		idNumber        : false,
		repeatEls       : 'input:not([type="button"],[id^=filelist]),select,textarea,.cmb2-media-status',
		noEmpty         : 'input:not([type="button"]):not([type="radio"]):not([type="checkbox"]),textarea',
		repeatUpdate    : 'input:not([type="button"]),select,textarea,label',
		styleBreakPoint : 450,
		mediaHandlers   : {},
		neweditor_id    : [],
		defaults : {
			time_picker  : l10n.defaults.time_picker,
			date_picker  : l10n.defaults.date_picker,
			color_picker : l10n.defaults.color_picker || {},
		},
		media : {
			frames : {},
		},
	};

	cmb.metabox = function() {
		if ( cmb.$metabox ) {
			return cmb.$metabox;
		}
		cmb.$metabox = $('.cmb2-wrap > .cmb2-metabox');
		return cmb.$metabox;
	};

	cmb.init = function() {
		$document = $( document );

		// Setup the CMB2 object defaults.
		$.extend( cmb, defaults );

		cmb.trigger( 'cmb_pre_init' );

		cmb.log( 'CMB2 localized data', l10n );

		var $metabox     = cmb.metabox();
		var $repeatGroup = $metabox.find('.cmb-repeatable-group');

		/**
		 * Initialize time/date/color pickers
		 */
		cmb.initPickers( $metabox.find('input[type="text"].cmb2-timepicker'), $metabox.find('input[type="text"].cmb2-datepicker'), $metabox.find('input[type="text"].cmb2-colorpicker') );

		// Insert toggle button into DOM wherever there is multicheck. credit: Genesis Framework
		$( '<p><span class="button cmb-multicheck-toggle">' + l10n.strings.check_toggle + '</span></p>' ).insertBefore( '.cmb2-checkbox-list:not(.no-select-all)' );

		// Make File List drag/drop sortable:
		cmb.makeListSortable();

		$metabox
			.on( 'change', '.cmb2_upload_file', function() {
				cmb.media.field = $( this ).attr( 'id' );
				$id( cmb.media.field + '_id' ).val('');
			})
			// Media/file management
			.on( 'click', '.cmb-multicheck-toggle', cmb.toggleCheckBoxes )
			.on( 'click', '.cmb2-upload-button', cmb.handleMedia )
			.on( 'click', '.cmb-attach-list li, .cmb2-media-status .img-status img, .cmb2-media-status .file-status > span', cmb.handleFileClick )
			.on( 'click', '.cmb2-remove-file-button', cmb.handleRemoveMedia )
			// Repeatable content
			.on( 'click', '.cmb-add-group-row', cmb.addGroupRow )
			.on( 'click', '.cmb-add-row-button', cmb.addAjaxRow )
			.on( 'click', '.cmb-remove-group-row', cmb.removeGroupRow )
			.on( 'click', '.cmb-remove-row-button', cmb.removeAjaxRow )
			// Ajax oEmbed display
			.on( 'keyup paste focusout', '.cmb2-oembed', cmb.maybeOembed )
			// Reset titles when removing a row
			.on( 'cmb2_remove_row', '.cmb-repeatable-group', cmb.resetTitlesAndIterator )
			.on( 'click', '.cmbhandle, .cmbhandle + .cmbhandle-title', cmb.toggleHandle );

		if ( $repeatGroup.length ) {
			$repeatGroup
				.filter('.sortable').each( function() {
					// Add sorting arrows
					$( this ).find( '.button.cmb-remove-group-row' ).before( '<a class="button cmb-shift-rows move-up alignleft" href="#"><span class="'+ l10n.up_arrow_class +'"></span></a> <a class="button cmb-shift-rows move-down alignleft" href="#"><span class="'+ l10n.down_arrow_class +'"></span></a>' );
				})
				.on( 'click', '.cmb-shift-rows', cmb.shiftRows )
				.on( 'cmb2_add_row', cmb.emptyValue );
		}

		// on pageload
		setTimeout( cmb.resizeoEmbeds, 500);
		// and on window resize
		$( window ).on( 'resize', cmb.resizeoEmbeds );

		cmb.trigger( 'cmb_init' );
	};

	cmb.resetTitlesAndIterator = function() {
		// Loop repeatable group tables
		$( '.cmb-repeatable-group' ).each( function() {
			var $table = $( this );
			// Loop repeatable group table rows
			$table.find( '.cmb-repeatable-grouping' ).each( function( rowindex ) {
				var $row = $( this );
				// Reset rows iterator
				$row.data( 'iterator', rowindex );
				// Reset rows title
				$row.find( '.cmb-group-title h4' ).text( $table.find( '.cmb-add-group-row' ).data( 'grouptitle' ).replace( '{#}', ( rowindex + 1 ) ) );
			});
		});
	};

	cmb.toggleHandle = function( evt ) {
		evt.preventDefault();
		cmb.trigger( 'postbox-toggled', $( this ).parent('.postbox').toggleClass('closed') );
	};

	cmb.toggleCheckBoxes = function( evt ) {
		evt.preventDefault();
		var $this = $( this );
		var $multicheck = $this.closest( '.cmb-td' ).find( 'input[type=checkbox]:not([disabled])' );

		// If the button has already been clicked once...
		if ( $this.data( 'checked' ) ) {
			// clear the checkboxes and remove the flag
			$multicheck.prop( 'checked', false );
			$this.data( 'checked', false );
		}
		// Otherwise mark the checkboxes and add a flag
		else {
			$multicheck.prop( 'checked', true );
			$this.data( 'checked', true );
		}
	};

	cmb.handleMedia = function( evt ) {
		evt.preventDefault();

		var $el = $( this );
		cmb.attach_id = ! $el.hasClass( 'cmb2-upload-list' ) ? $el.closest( '.cmb-td' ).find( '.cmb2-upload-file-id' ).val() : false;
		// Clean up default 0 value
		cmb.attach_id = '0' !== cmb.attach_id ? cmb.attach_id : false;

		cmb._handleMedia( $el.prev('input.cmb2-upload-file').attr('id'), $el.hasClass( 'cmb2-upload-list' ) );
	};

	cmb.handleFileClick = function( evt ) {
		evt.preventDefault();

		var $el    = $( this );
		var $td    = $el.closest( '.cmb-td' );
		var isList = $td.find( '.cmb2-upload-button' ).hasClass( 'cmb2-upload-list' );
		cmb.attach_id = isList ? $el.find( 'input[type="hidden"]' ).data( 'id' ) : $td.find( '.cmb2-upload-file-id' ).val();

		if ( cmb.attach_id ) {
			cmb._handleMedia( $td.find( 'input.cmb2-upload-file' ).attr('id'), isList, cmb.attach_id );
		}
	};

	cmb._handleMedia = function( formfield, isList ) {
		if ( ! wp ) {
			return;
		}

		var media         = cmb.media;
		media.field       = formfield;
		media.$field      = $id( media.field );
		media.fieldData   = media.$field.data();
		media.previewSize = media.fieldData.previewsize;
		media.fieldName   = media.$field.attr('name');
		media.isList      = isList;

		var uploadStatus, attachment;

		// If this field's media frame already exists, reopen it.
		if ( media.field in media.frames ) {
			media.frames[ media.field ].open();
			return;
		}

		// Create the media frame.
		media.frames[ media.field ] = wp.media( {
			title: cmb.metabox().find('label[for=' + media.field + ']').text(),
			library : media.fieldData.queryargs || {},
			button: {
				text: l10n.strings[ isList ? 'upload_files' : 'upload_file' ]
			},
			multiple: isList ? 'add' : false
		} );

		cmb.trigger( 'cmb_media_modal_init', media );

		cmb.mediaHandlers.list = function( selection, returnIt ) {
			// Get all of our selected files
			attachment = selection.toJSON();

			media.$field.val(attachment.url);
			$id( media.field +'_id' ).val(attachment.id);

			// Setup our fileGroup array
			var fileGroup = [];

			// Loop through each attachment
			$( attachment ).each( function() {
				if ( this.type && this.type === 'image' ) {
					var width = media.previewSize[0] ? media.previewSize[0] : 50;
					var height = media.previewSize[1] ? media.previewSize[1] : 50;

					// image preview
					uploadStatus = '<li class="img-status">'+
						'<img width="'+ width +'" height="'+ height +'" src="' + this.url + '" class="attachment-'+ width +'px'+ height +'px" alt="'+ this.filename +'">'+
						'<p><a href="#" class="cmb2-remove-file-button" rel="'+ media.field +'['+ this.id +']">'+ l10n.strings.remove_image +'</a></p>'+
						'<input type="hidden" id="filelist-'+ this.id +'" data-id="'+ this.id +'" name="'+ media.fieldName +'['+ this.id +']" value="' + this.url + '">'+
					'</li>';

				} else {
					// Standard generic output if it's not an image.
					uploadStatus = '<li class="file-status"><span>'+ l10n.strings.file +' <strong>'+ this.filename +'</strong></span>&nbsp;&nbsp; (<a href="' + this.url + '" target="_blank" rel="external">'+ l10n.strings.download +'</a> / <a href="#" class="cmb2-remove-file-button" rel="'+ media.field +'['+ this.id +']">'+ l10n.strings.remove_file +'</a>)'+
						'<input type="hidden" id="filelist-'+ this.id +'" data-id="'+ this.id +'" name="'+ media.fieldName +'['+ this.id +']" value="' + this.url + '">'+
					'</li>';

				}

				// Add our file to our fileGroup array
				fileGroup.push( uploadStatus );
			});

			if ( ! returnIt ) {
				// Append each item from our fileGroup array to .cmb2-media-status
				$( fileGroup ).each( function() {
					media.$field.siblings('.cmb2-media-status').slideDown().append(this);
				});
			} else {
				return fileGroup;
			}

		};

		cmb.mediaHandlers.single = function( selection ) {
			// Only get one file from the uploader
			attachment = selection.first().toJSON();

			media.$field.val(attachment.url);
			$id( media.field +'_id' ).val(attachment.id);

			if ( attachment.type && attachment.type === 'image' ) {
				// image preview
				var width = media.previewSize[0] ? media.previewSize[0] : 350;
				uploadStatus = '<div class="img-status"><img width="'+ width +'px" style="max-width: '+ width +'px; width: 100%; height: auto;" src="' + attachment.url + '" alt="'+ attachment.filename +'" title="'+ attachment.filename +'" /><p><a href="#" class="cmb2-remove-file-button" rel="' + media.field + '">'+ l10n.strings.remove_image +'</a></p></div>';
			} else {
				// Standard generic output if it's not an image.
				uploadStatus = '<div class="file-status"><span>'+ l10n.strings.file +' <strong>'+ attachment.filename +'</strong></span>&nbsp;&nbsp; (<a href="'+ attachment.url +'" target="_blank" rel="external">'+ l10n.strings.download +'</a> / <a href="#" class="cmb2-remove-file-button" rel="'+ media.field +'">'+ l10n.strings.remove_file +'</a>)</div>';
			}

			// add/display our output
			media.$field.siblings('.cmb2-media-status').slideDown().html(uploadStatus);
		};

		cmb.mediaHandlers.selectFile = function() {
			var selection = media.frames[ media.field ].state().get('selection');
			var type = isList ? 'list' : 'single';

			if ( cmb.attach_id && isList ) {
				$( '[data-id="'+ cmb.attach_id +'"]' ).parents( 'li' ).replaceWith( cmb.mediaHandlers.list( selection, true ) );
			} else {
				cmb.mediaHandlers[type]( selection );
			}

			cmb.trigger( 'cmb_media_modal_select', selection, media );
		};

		cmb.mediaHandlers.openModal = function() {
			var selection = media.frames[ media.field ].state().get('selection');
			var attach;

			if ( ! cmb.attach_id ) {
				selection.reset();
			} else {
				attach = wp.media.attachment( cmb.attach_id );
				attach.fetch();
				selection.set( attach ? [ attach ] : [] );
			}

			cmb.trigger( 'cmb_media_modal_open', selection, media );
		};

		// When a file is selected, run a callback.
		media.frames[ media.field ]
			.on( 'select', cmb.mediaHandlers.selectFile )
			.on( 'open', cmb.mediaHandlers.openModal );

		// Finally, open the modal
		media.frames[ media.field ].open();
	};

	cmb.handleRemoveMedia = function( evt ) {
		evt.preventDefault();
		var $this = $( this );
		if ( $this.is( '.cmb-attach-list .cmb2-remove-file-button' ) ){
			$this.parents('li').remove();
			return false;
		}

		cmb.media.field = $this.attr('rel');

		cmb.metabox().find( 'input#' + cmb.media.field ).val('');
		cmb.metabox().find( 'input#' + cmb.media.field + '_id' ).val('');
		$this.parents('.cmb2-media-status').html('');

		return false;
	};

	cmb.cleanRow = function( $row, prevNum, group ) {
		var $elements = $row.find( cmb.repeatUpdate );
		if ( group ) {

			var $other  = $row.find( '[id]' ).not( cmb.repeatUpdate );

			// Remove extra ajaxed rows
			$row.find('.cmb-repeat-table .cmb-repeat-row:not(:first-child)').remove();

			// Update all elements w/ an ID
			if ( $other.length ) {
				$other.each( function() {
					var $_this = $( this );
					var oldID = $_this.attr( 'id' );
					var newID = oldID.replace( '_'+ prevNum, '_'+ cmb.idNumber );
					var $buttons = $row.find('[data-selector="'+ oldID +'"]');
					$_this.attr( 'id', newID );

					// Replace data-selector vars
					if ( $buttons.length ) {
						$buttons.attr( 'data-selector', newID ).data( 'selector', newID );
					}
				});
			}
		}
		cmb.neweditor_id = [];

		$elements.filter(':checked').prop( 'checked', false );
		$elements.filter(':selected').prop( 'selected', false );

		if ( $row.find('h3.cmb-group-title').length ) {
			$row.find( 'h3.cmb-group-title' ).text( $row.data( 'title' ).replace( '{#}', ( cmb.idNumber + 1 ) ) );
		}

		$elements.each( function() {
			cmb.elReplacements( $( this ), prevNum );
		} );

		return cmb;
	};

	cmb.elReplacements = function( $newInput, prevNum ) {
		var isEditor  = $newInput.hasClass( 'wp-editor-area' );
		var oldFor    = $newInput.attr( 'for' );
		var oldVal    = $newInput.val();
		var type      = $newInput.prop( 'type' );
		var checkable = 'radio' === type || 'checkbox' === type ? oldVal : false;
		// var $next  = $newInput.next();
		var attrs     = {};
		var newID, oldID;
		if ( oldFor ) {
			attrs = { 'for' : oldFor.replace( '_'+ prevNum, '_'+ cmb.idNumber ) };
		} else {
			var oldName = $newInput.attr( 'name' );
			// Replace 'name' attribute key
			var newName = oldName ? oldName.replace( '['+ prevNum +']', '['+ cmb.idNumber +']' ) : '';
			oldID       = $newInput.attr( 'id' );
			newID       = oldID ? oldID.replace( '_'+ prevNum, '_'+ cmb.idNumber ) : '';
			attrs       = {
				id: newID,
				name: newName,
				// value: '',
				'data-iterator': cmb.idNumber,
			};

		}

		// Clear out old values
		if ( undefined !== typeof( oldVal ) && oldVal || checkable ) {
			attrs.value = checkable ? checkable : '';
		}

		// Clear out textarea values
		if ( 'TEXTAREA' === $newInput.prop('tagName') ) {
			$newInput.html( '' );
		}

		if ( checkable ) {
			$newInput.removeAttr( 'checked' );
		}

		$newInput
			.removeClass( 'hasDatepicker' )
			.attr( attrs ).val( checkable ? checkable : '' );

		// wysiwyg field
		if ( isEditor ) {
			// Get new wysiwyg ID
			newID = newID ? oldID.replace( 'zx'+ prevNum, 'zx'+ cmb.idNumber ) : '';
			// Empty the contents
			$newInput.html('');
			// Get wysiwyg field
			var $wysiwyg = $newInput.parents( '.cmb-type-wysiwyg' );
			// Remove extra mce divs
			$wysiwyg.find('.mce-tinymce:not(:first-child)').remove();
			// Replace id instances
			var html = $wysiwyg.html().replace( new RegExp( oldID, 'g' ), newID );
			// Update field html
			$wysiwyg.html( html );
			// Save ids for later to re-init tinymce
			cmb.neweditor_id.push( { 'id': newID, 'old': oldID } );
		}

		return $newInput;
	};

	cmb.newRowHousekeeping = function( $row ) {

		var $colorPicker = $row.find( '.wp-picker-container' );
		var $list        = $row.find( '.cmb2-media-status' );

		if ( $colorPicker.length ) {
			// Need to clean-up colorpicker before appending
			$colorPicker.each( function() {
				var $td = $( this ).parent();
				$td.html( $td.find( 'input[type="text"].cmb2-colorpicker' ).attr('style', '') );
			});
		}

		// Need to clean-up colorpicker before appending
		if ( $list.length ) {
			$list.empty();
		}

		return cmb;
	};

	cmb.afterRowInsert = function( $row ) {
		var _prop;

		// Need to re-init wp_editor instances
		if ( cmb.neweditor_id.length ) {
			var i;
			for ( i = cmb.neweditor_id.length - 1; i >= 0; i-- ) {
				var id = cmb.neweditor_id[i].id;
				var old = cmb.neweditor_id[i].old;

				if ( typeof( tinyMCEPreInit.mceInit[ id ] ) === 'undefined' ) {
					var newSettings = jQuery.extend( {}, tinyMCEPreInit.mceInit[ old ] );

					for ( _prop in newSettings ) {
						if ( 'string' === typeof( newSettings[_prop] ) ) {
							newSettings[_prop] = newSettings[_prop].replace( new RegExp( old, 'g' ), id );
						}
					}
					tinyMCEPreInit.mceInit[ id ] = newSettings;
				}
				if ( typeof( tinyMCEPreInit.qtInit[ id ] ) === 'undefined' ) {
					var newQTS = jQuery.extend( {}, tinyMCEPreInit.qtInit[ old ] );
					for ( _prop in newQTS ) {
						if ( 'string' === typeof( newQTS[_prop] ) ) {
							newQTS[_prop] = newQTS[_prop].replace( new RegExp( old, 'g' ), id );
						}
					}
					tinyMCEPreInit.qtInit[ id ] = newQTS;
				}
				tinyMCE.init({
					id : tinyMCEPreInit.mceInit[ id ],
				});

			}
		}

		// Init pickers from new row
		cmb.initPickers( $row.find('input[type="text"].cmb2-timepicker'), $row.find('input[type="text"].cmb2-datepicker'), $row.find('input[type="text"].cmb2-colorpicker') );
	};

	cmb.updateNameAttr = function () {

		var $this = $( this );
		var name  = $this.attr( 'name' ); // get current name

		// If name is defined
		if ( typeof name !== 'undefined' ) {
			var prevNum = parseInt( $this.parents( '.cmb-repeatable-grouping' ).data( 'iterator' ), 10 );
			var newNum  = prevNum - 1; // Subtract 1 to get new iterator number

			// Update field name attributes so data is not orphaned when a row is removed and post is saved
			var $newName = name.replace( '[' + prevNum + ']', '[' + newNum + ']' );

			// New name with replaced iterator
			$this.attr( 'name', $newName );
		}

	};

	cmb.emptyValue = function( evt, row ) {
		$( cmb.noEmpty, row ).val( '' );
	};

	cmb.addGroupRow = function( evt ) {
		evt.preventDefault();

		var $this = $( this );

		// before anything significant happens
		cmb.triggerElement( $this, 'cmb2_add_group_row_start', $this );

		var $table   = $id( $this.data('selector') );
		var $oldRow  = $table.find('.cmb-repeatable-grouping').last();
		var prevNum  = parseInt( $oldRow.data('iterator'), 10 );
		cmb.idNumber = prevNum + 1;
		var $row     = $oldRow.clone();

		cmb.newRowHousekeeping( $row.data( 'title', $this.data( 'grouptitle' ) ) ).cleanRow( $row, prevNum, true );
		$row.find( '.cmb-add-row-button' ).prop( 'disabled', false );

		var $newRow = $( '<div class="postbox cmb-row cmb-repeatable-grouping" data-iterator="'+ cmb.idNumber +'">'+ $row.html() +'</div>' );
		$oldRow.after( $newRow );

		cmb.afterRowInsert( $newRow );

		if ( $table.find('.cmb-repeatable-grouping').length <= 1 ) {
			$table.find('.cmb-remove-group-row').prop( 'disabled', true );
		} else {
			$table.find('.cmb-remove-group-row').prop( 'disabled', false );
		}

		cmb.triggerElement( $table, 'cmb2_add_row', $newRow );
	};

	cmb.addAjaxRow = function( evt ) {
		evt.preventDefault();

		var $this         = $( this );
		var $table        = $id( $this.data('selector') );
		var $emptyrow     = $table.find('.empty-row');
		var prevNum       = parseInt( $emptyrow.find('[data-iterator]').data('iterator'), 10 );
		cmb.idNumber      = prevNum + 1;
		var $row          = $emptyrow.clone();

		cmb.newRowHousekeeping( $row ).cleanRow( $row, prevNum );

		$emptyrow.removeClass('empty-row hidden').addClass('cmb-repeat-row');
		$emptyrow.after( $row );

		cmb.afterRowInsert( $row );

		cmb.triggerElement( $table, 'cmb2_add_row', $row );

		$table.find( '.cmb-remove-row-button' ).removeClass( 'button-disabled' );

	};

	cmb.removeGroupRow = function( evt ) {
		evt.preventDefault();

		var $this   = $( this );
		var $table  = $id( $this.data('selector') );
		var $parent = $this.parents('.cmb-repeatable-grouping');
		var number  = $table.find('.cmb-repeatable-grouping').length;

		// Needs to always be at least one group.
		if ( number < 2 ) {
			return;
		}

		cmb.triggerElement( $table, 'cmb2_remove_group_row_start', $this );

		// when a group is removed loop through all next groups and update fields names
		$parent.nextAll( '.cmb-repeatable-grouping' ).find( cmb.repeatEls ).each( cmb.updateNameAttr );

		$parent.remove();

		if ( number <= 2 ) {
			$table.find('.cmb-remove-group-row').prop( 'disabled', true );
		} else {
			$table.find('.cmb-remove-group-row').prop( 'disabled', false );
		}

		cmb.triggerElement( $table, 'cmb2_remove_row' );

	};

	cmb.removeAjaxRow = function( evt ) {
		evt.preventDefault();

		var $this = $( this );

		// Check if disabled
		if ( $this.hasClass( 'button-disabled' ) ) {
			return;
		}

		var $parent = $this.parents('.cmb-row');
		var $table  = $this.parents('.cmb-repeat-table');
		var number  = $table.find('.cmb-row').length;

		if ( number > 2 ) {
			if ( $parent.hasClass('empty-row') ) {
				$parent.prev().addClass( 'empty-row' ).removeClass('cmb-repeat-row');
			}
			$this.parents('.cmb-repeat-table .cmb-row').remove();
			if ( number === 3 ) {
				$table.find( '.cmb-remove-row-button' ).addClass( 'button-disabled' );
			}

			cmb.triggerElement( $table, 'cmb2_remove_row' );
		} else {
			$this.addClass( 'button-disabled' );
		}
	};

	cmb.shiftRows = function( evt ) {

		evt.preventDefault();

		var $this = $( this );
		// Before shift occurs.
		cmb.triggerElement( $this, 'cmb2_shift_rows_enter', $this );

		var $parent   = $this.parents( '.cmb-repeatable-grouping' );
		var $goto     = $this.hasClass( 'move-up' ) ? $parent.prev( '.cmb-repeatable-grouping' ) : $parent.next( '.cmb-repeatable-grouping' );

		if ( ! $goto.length ) {
			return;
		}

		// About to shift
		cmb.triggerElement( $this, 'cmb2_shift_rows_start', $this );

		var inputVals = [];
		// Loop this item's fields
		$parent.find( cmb.repeatEls ).each( function() {
			var $element = $( this );
			var elType = $element.attr( 'type' );
			var val;

			if ( $element.hasClass('cmb2-media-status') ) {
				// special case for image previews
				val = $element.html();
			} else if ( 'checkbox' === elType || 'radio' === elType ) {
				val = $element.is(':checked');
			} else if ( 'select' === $element.prop('tagName') ) {
				val = $element.is(':selected');
			} else {
				val = $element.val();
			}

			// Get all the current values per element
			inputVals.push( { val: val, $: $element } );
		});
		// And swap them all
		$goto.find( cmb.repeatEls ).each( function( index ) {
			var $element = $( this );
			var elType = $element.attr( 'type' );
			var val;

			if ( $element.hasClass('cmb2-media-status') ) {
				var toRowId = $element.closest('.cmb-repeatable-grouping').attr('data-iterator');
				var fromRowId = inputVals[ index ].$.closest('.cmb-repeatable-grouping').attr('data-iterator');

				// special case for image previews
				val = $element.html();
				$element.html( inputVals[ index ].val );
				inputVals[ index ].$.html( val );

				inputVals[ index ].$.find( 'input' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( '['+toRowId+']', '['+fromRowId+']' );
					$( this ).attr( 'name', name );
				});
				$element.find('input').each(function() {
					var name = $( this ).attr('name');
					name = name.replace('['+fromRowId+']', '['+toRowId+']');
					$( this ).attr('name', name);
				});

			}
			// handle checkbox swapping
			else if ( 'checkbox' === elType  ) {
				inputVals[ index ].$.prop( 'checked', $element.is(':checked') );
				$element.prop( 'checked', inputVals[ index ].val );
			}
			// handle radio swapping
			else if ( 'radio' === elType  ) {
				if ( $element.is( ':checked' ) ) {
					inputVals[ index ].$.attr( 'data-checked', 'true' );
				}
				if ( inputVals[ index ].$.is( ':checked' ) ) {
					$element.attr( 'data-checked', 'true' );
				}
			}
			// handle select swapping
			else if ( 'select' === $element.prop('tagName') ) {
				inputVals[ index ].$.prop( 'selected', $element.is(':selected') );
				$element.prop( 'selected', inputVals[ index ].val );
			}
			// handle normal input swapping
			else {
				inputVals[ index ].$.val( $element.val() );
				$element.val( inputVals[ index ].val );
			}
		});

		$parent.find( 'input[data-checked=true]' ).prop( 'checked', true ).removeAttr( 'data-checked' );
		$goto.find( 'input[data-checked=true]' ).prop( 'checked', true ).removeAttr( 'data-checked' );

		// trigger color picker change event
		$parent.find( 'input[type="text"].cmb2-colorpicker' ).trigger( 'change' );
		$goto.find( 'input[type="text"].cmb2-colorpicker' ).trigger( 'change' );

		// shift done
		cmb.triggerElement( $this, 'cmb2_shift_rows_complete', $this );
	};

	cmb.initPickers = function( $timePickers, $datePickers, $colorPickers ) {
		// Initialize jQuery UI timepickers
		cmb.initDateTimePickers( $timePickers, 'timepicker', 'time_picker' );
		// Initialize jQuery UI datepickers
		cmb.initDateTimePickers( $datePickers, 'datepicker', 'date_picker' );
		// Initialize color picker
		cmb.initColorPickers( $colorPickers );
	};

	cmb.initDateTimePickers = function( $selector, method, defaultKey ) {
		if ( $selector.length ) {
			$selector[ method ]( 'destroy' ).each( function() {
				var $this     = $( this );
				var fieldOpts = $this.data( method ) || {};
				var options   = $.extend( {}, cmb.defaults[ defaultKey ], fieldOpts );
				$this[ method ]( cmb.datePickerSetupOpts( fieldOpts, options, method ) );
			} );
		}
	};

	cmb.datePickerSetupOpts = function( fieldOpts, options, method ) {
		var existing = $.extend( {}, options );

		options.beforeShow = function( input, inst ) {
			if ( 'timepicker' === method ) {
				cmb.addTimePickerClasses( inst.dpDiv );
			}

			// Wrap datepicker w/ class to narrow the scope of jQuery UI CSS and prevent conflicts
			$id( 'ui-datepicker-div' ).addClass( 'cmb2-element' );

			// Let's be sure to call beforeShow if it was added
			if ( 'function' === typeof existing.beforeShow ) {
				existing.beforeShow( input, inst );
			}
		};

		if ( 'timepicker' === method ) {
			options.onChangeMonthYear = function( year, month, inst, picker ) {
				cmb.addTimePickerClasses( inst.dpDiv );

				// Let's be sure to call onChangeMonthYear if it was added
				if ( 'function' === typeof existing.onChangeMonthYear ) {
					existing.onChangeMonthYear( year, month, inst, picker );
				}
			};
		}

		options.onClose = function( dateText, inst ) {
			// Remove the class when we're done with it (and hide to remove FOUC).
			$id( 'ui-datepicker-div' ).removeClass( 'cmb2-element' ).hide();

			// Let's be sure to call onClose if it was added
			if ( 'function' === typeof existing.onClose ) {
				existing.onClose( dateText, inst );
			}
		};

		return options;
	};

	// Adds classes to timepicker buttons.
	cmb.addTimePickerClasses = function( $picker ) {
		var func = cmb.addTimePickerClasses;
		func.count = func.count || 0;

		// Wait a bit to let the timepicker render, since these are pre-render events.
		setTimeout( function() {
			if ( $picker.find( '.ui-priority-secondary' ).length ) {
				$picker.find( '.ui-priority-secondary' ).addClass( 'button-secondary' );
				$picker.find( '.ui-priority-primary' ).addClass( 'button-primary' );
				func.count = 0;
			} else if ( func.count < 5 ) {
				func.count++;
				func( $picker );
			}
		}, 10 );
	};

	cmb.initColorPickers = function( $selector ) {
		if ( ! $selector.length ) {
			return;
		}
		if ( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ) {

			$selector.each( function() {
				var $this = $( this );
				var fieldOpts = $this.data( 'colorpicker' ) || {};
				$this.wpColorPicker( $.extend( {}, cmb.defaults.color_picker, fieldOpts ) );
			} );

		} else {
			$selector.each( function( i ) {
				$( this ).after( '<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>' );
				$id( 'picker-' + i ).hide().farbtastic( $( this ) );
			} )
			.focus( function() {
				$( this ).next().show();
			} )
			.blur( function() {
				$( this ).next().hide();
			} );
		}
	};

	cmb.makeListSortable = function() {
		var $filelist = cmb.metabox().find( '.cmb2-media-status.cmb-attach-list' );
		if ( $filelist.length ) {
			$filelist.sortable({ cursor: 'move' }).disableSelection();
		}
	};

	cmb.maybeOembed = function( evt ) {
		var $this = $( this );

		var m = {
			focusout : function() {
				setTimeout( function() {
					// if it's been 2 seconds, hide our spinner
					cmb.spinner( '.postbox .cmb2-metabox', true );
				}, 2000);
			},
			keyup : function() {
				var betw = function( min, max ) {
					return ( evt.which <= max && evt.which >= min );
				};
				// Only Ajax on normal keystrokes
				if ( betw( 48, 90 ) || betw( 96, 111 ) || betw( 8, 9 ) || evt.which === 187 || evt.which === 190 ) {
					// fire our ajax function
					cmb.doAjax( $this, evt );
				}
			},
			paste : function() {
				// paste event is fired before the value is filled, so wait a bit
				setTimeout( function() { cmb.doAjax( $this ); }, 100);
			}
		};

		m[ evt.type ]();
	};

	/**
	 * Resize oEmbed videos to fit in their respective metaboxes
	 */
	cmb.resizeoEmbeds = function() {
		cmb.metabox().each( function() {
			var $this      = $( this );
			var $tableWrap = $this.parents('.inside');
			var isSide     = $this.parents('.inner-sidebar').length || $this.parents( '#side-sortables' ).length;
			var isSmall    = isSide;
			var isSmallest = false;
			if ( ! $tableWrap.length )  {
				return true; // continue
			}

			// Calculate new width
			var tableW = $tableWrap.width();

			if ( cmb.styleBreakPoint > tableW ) {
				isSmall    = true;
				isSmallest = ( cmb.styleBreakPoint - 62 ) > tableW;
			}

			tableW = isSmall ? tableW : Math.round(($tableWrap.width() * 0.82)*0.97);
			var newWidth = tableW - 30;
			if ( isSmall && ! isSide && ! isSmallest ) {
				newWidth = newWidth - 75;
			}
			if ( newWidth > 639 ) {
				return true; // continue
			}

			var $embeds   = $this.find('.cmb-type-oembed .embed-status');
			var $children = $embeds.children().not('.cmb2-remove-wrapper');
			if ( ! $children.length ) {
				return true; // continue
			}

			$children.each( function() {
				var $this     = $( this );
				var iwidth    = $this.width();
				var iheight   = $this.height();
				var _newWidth = newWidth;
				if ( $this.parents( '.cmb-repeat-row' ).length && ! isSmall ) {
					// Make room for our repeatable "remove" button column
					_newWidth = newWidth - 91;
					_newWidth = 785 > tableW ? _newWidth - 15 : _newWidth;
				}
				// Calc new height
				var newHeight = Math.round((_newWidth * iheight)/iwidth);
				$this.width(_newWidth).height(newHeight);
			});

		});
	};

	/**
	 * Safely log things if query var is set
	 * @since  1.0.0
	 */
	cmb.log = function() {
		if ( l10n.script_debug && console && typeof console.log === 'function' ) {
			console.log.apply(console, arguments);
		}
	};

	cmb.spinner = function( $context, hide ) {
		if ( hide ) {
			$('.cmb-spinner', $context ).hide();
		}
		else {
			$('.cmb-spinner', $context ).show();
		}
	};

	// function for running our ajax
	cmb.doAjax = function( $obj ) {
		// get typed value
		var oembed_url = $obj.val();
		// only proceed if the field contains more than 6 characters
		if ( oembed_url.length < 6 ) {
			return;
		}

		// get field id
		var field_id         = $obj.attr('id');
		var $context         = $obj.closest( '.cmb-td' );
		var $embed_container = $context.find( '.embed-status' );
		var $embed_wrap      = $context.find( '.embed_wrap' );
		var $child_el        = $embed_container.find( ':first-child' );
		var oembed_width     = $embed_container.length && $child_el.length ? $child_el.width() : $obj.width();

		cmb.log( 'oembed_url', oembed_url, field_id );

		// show our spinner
		cmb.spinner( $context );
		// clear out previous results
		$embed_wrap.html('');
		// and run our ajax function
		setTimeout( function() {
			// if they haven't typed in 500 ms
			if ( $( '.cmb2-oembed:focus' ).val() !== oembed_url ) {
				return;
			}
			$.ajax({
				type : 'post',
				dataType : 'json',
				url : l10n.ajaxurl,
				data : {
					'action'          : 'cmb2_oembed_handler',
					'oembed_url'      : oembed_url,
					'oembed_width'    : oembed_width > 300 ? oembed_width : 300,
					'field_id'        : field_id,
					'object_id'       : $obj.data( 'objectid' ),
					'object_type'     : $obj.data( 'objecttype' ),
					'cmb2_ajax_nonce' : l10n.ajax_nonce
				},
				success: function(response) {
					cmb.log( response );
					// hide our spinner
					cmb.spinner( $context, true );
					// and populate our results from ajax response
					$embed_wrap.html( response.data );
				}
			});

		}, 500);

	};

	cmb.trigger = function( evtName ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		args.push( cmb );
		$document.trigger( evtName, args );
	};

	cmb.triggerElement = function( $el, evtName ) {
		var args = Array.prototype.slice.call( arguments, 2 );
		args.push( cmb );
		$el.trigger( evtName, args );
	};

	$( cmb.init );

})(window, document, jQuery, window.CMB2);
