/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author CMB2 team
 * @see    https://github.com/CMB2/CMB2
 */

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
		defaults : {
			time_picker  : l10n.defaults.time_picker,
			date_picker  : l10n.defaults.date_picker,
			color_picker : l10n.defaults.color_picker || {},
			code_editor  : l10n.defaults.code_editor,
		},
		media : {
			frames : {},
		},
	};

	cmb.init = function() {
		$document = $( document );

		// Setup the CMB2 object defaults.
		$.extend( cmb, defaults );

		cmb.trigger( 'cmb_pre_init' );

		var $metabox     = cmb.metabox();
		var $repeatGroup = $metabox.find('.cmb-repeatable-group');

		 // Init time/date/color pickers
		cmb.initPickers( $metabox.find('input[type="text"].cmb2-timepicker'), $metabox.find('input[type="text"].cmb2-datepicker'), $metabox.find('input[type="text"].cmb2-colorpicker') );

		// Init code editors.
		cmb.initCodeEditors( $metabox.find( '.cmb2-textarea-code:not(.disable-codemirror)' ) );

		// Insert toggle button into DOM wherever there is multicheck. credit: Genesis Framework
		$( '<p><span class="button-secondary cmb-multicheck-toggle">' + l10n.strings.check_toggle + '</span></p>' ).insertBefore( '.cmb2-checkbox-list:not(.no-select-all)' );

		// Make File List drag/drop sortable:
		cmb.makeListSortable();
		// Make Repeatable fields drag/drop sortable:
		cmb.makeRepeatableSortable();

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
				.on( 'cmb2_add_row', cmb.emptyValue )
				.on( 'cmb2_add_row', cmb.setDefaults )
				.filter('.sortable').each( function() {
					// Add sorting arrows
					$( this ).find( '.cmb-remove-group-row-button' ).before( '<a class="button-secondary cmb-shift-rows move-up alignleft" href="#"><span class="'+ l10n.up_arrow_class +'"></span></a> <a class="button-secondary cmb-shift-rows move-down alignleft" href="#"><span class="'+ l10n.down_arrow_class +'"></span></a>' );
				})
				.on( 'click', '.cmb-shift-rows', cmb.shiftRows );
		}

		// on pageload
		setTimeout( cmb.resizeoEmbeds, 500);
		// and on window resize
		$( window ).on( 'resize', cmb.resizeoEmbeds );

		if ( $id( 'addtag' ).length ) {
			cmb.listenTagAdd();
		}

		$( document ).on( 'cmb_init', cmb.mceEnsureSave );

		cmb.trigger( 'cmb_init' );
	};

	// Handles updating tiny mce instances when saving a gutenberg post.
	// https://github.com/CMB2/CMB2/issues/1156
	cmb.mceEnsureSave = function() {
		// If no wp.data, do not proceed (no gutenberg)
		if ( ! wp.data || ! wp.data.hasOwnProperty('subscribe') ) {
			return;
		}

		// If the current user cannot richedit, or MCE is not available, bail.
		if ( ! cmb.canTinyMCE() ) {
			return;
		}

		wp.data.subscribe( function() {
			var editor = wp.data.hasOwnProperty('select') ? wp.data.select( 'core/editor' ) : null;

			// the post is currently being saved && we have tinymce editors
			if ( editor && editor.isSavingPost && editor.isSavingPost() && window.tinyMCE.editors.length ) {
				for ( var i = 0; i < window.tinyMCE.editors.length; i++ ) {
					if ( window.tinyMCE.activeEditor !== window.tinyMCE.editors[i] ) {
						window.tinyMCE.editors[i].save();
					}
				}
			}
		});
	};

	cmb.canTinyMCE = function() {
		return l10n.user_can_richedit && window.tinyMCE;
	};

	cmb.listenTagAdd = function() {
		$document.ajaxSuccess( function( evt, xhr, settings ) {
			if ( settings.data && settings.data.length && -1 !== settings.data.indexOf( 'action=add-tag' ) ) {
				cmb.resetBoxes( $id( 'addtag' ).find( '.cmb2-wrap > .cmb2-metabox' ) );
			}
		});
	};

	cmb.resetBoxes = function( $boxes ) {
		$.each( $boxes, function() {
			cmb.resetBox( $( this ) );
		});
	};

	cmb.resetBox = function( $box ) {
		$box.find( '.wp-picker-clear' ).trigger( 'click' );
		$box.find( '.cmb2-remove-file-button' ).trigger( 'click' );
		$box.find( '.cmb-row.cmb-repeatable-grouping:not(:first-of-type) .cmb-remove-group-row' ).click();
		$box.find( '.cmb-repeat-row:not(:first-child)' ).remove();

		$box.find( 'input:not([type="button"]),select,textarea' ).each( function() {
			var $element = $( this );
			var tagName = $element.prop('tagName');

			if ( 'INPUT' === tagName ) {
				var elType = $element.attr( 'type' );
				if ( 'checkbox' === elType || 'radio' === elType ) {
					$element.prop( 'checked', false );
				} else {
					$element.val( '' );
				}
			}
			if ( 'SELECT' === tagName ) {
				$( 'option:selected', this ).prop( 'selected', false );
			}
			if ( 'TEXTAREA' === tagName ) {
				$element.html( '' );
			}
		});
	};

	cmb.resetTitlesAndIterator = function( evt ) {
		if ( ! evt.group ) {
			return;
		}

		// Loop repeatable group tables
		$( '.cmb-repeatable-group.repeatable' ).each( function() {
			var $table = $( this );
			var groupTitle = $table.find( '.cmb-add-group-row' ).data( 'grouptitle' );

			// Loop repeatable group table rows
			$table.find( '.cmb-repeatable-grouping' ).each( function( rowindex ) {
				var $row = $( this );
				var $rowTitle = $row.find( 'h3.cmb-group-title' );
				// Reset rows iterator
				$row.data( 'iterator', rowindex );
				// Reset rows title
				if ( $rowTitle.length ) {
					$rowTitle.text( groupTitle.replace( '{#}', ( rowindex + 1 ) ) );
				}
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
		if ( $( evt.target ).is( 'a' ) ) {
			return;
		}

		evt.preventDefault();

		var $el    = $( this );
		var $td    = $el.closest( '.cmb-td' );
		var isList = $td.find( '.cmb2-upload-button' ).hasClass( 'cmb2-upload-list' );
		cmb.attach_id = isList ? $el.find( 'input[type="hidden"]' ).data( 'id' ) : $td.find( '.cmb2-upload-file-id' ).val();

		if ( cmb.attach_id ) {
			cmb._handleMedia( $td.find( 'input.cmb2-upload-file' ).attr( 'id' ), isList, cmb.attach_id );
		}
	};

	cmb._handleMedia = function( id, isList ) {
		if ( ! wp ) {
			return;
		}

		var media, handlers;

		handlers          = cmb.mediaHandlers;
		media             = cmb.media;
		media.field       = id;
		media.$field      = $id( media.field );
		media.fieldData   = media.$field.data();
		media.previewSize = media.fieldData.previewsize;
		media.sizeName    = media.fieldData.sizename;
		media.fieldName   = media.$field.attr('name');
		media.isList      = isList;

		// If this field's media frame already exists, reopen it.
		if ( id in media.frames ) {
			return media.frames[ id ].open();
		}

		// Create the media frame.
		media.frames[ id ] = wp.media( {
			title: cmb.metabox().find('label[for="' + id + '"]').text(),
			library : media.fieldData.queryargs || {},
			button: {
				text: l10n.strings[ isList ? 'upload_files' : 'upload_file' ]
			},
			multiple: isList ? 'add' : false
		} );

		// Enable the additional media filters: https://github.com/CMB2/CMB2/issues/873
		media.frames[ id ].states.first().set( 'filterable', 'all' );

		cmb.trigger( 'cmb_media_modal_init', media );

		handlers.list = function( selection, returnIt ) {

			// Setup our fileGroup array
			var fileGroup = [];
			var attachmentHtml;

			if ( ! handlers.list.templates ) {
				handlers.list.templates = {
					image : wp.template( 'cmb2-list-image' ),
					file  : wp.template( 'cmb2-list-file' ),
				};
			}

			// Loop through each attachment
			selection.each( function( attachment ) {

				// Image preview or standard generic output if it's not an image.
				attachmentHtml = handlers.getAttachmentHtml( attachment, 'list' );

				// Add our file to our fileGroup array
				fileGroup.push( attachmentHtml );
			});

			if ( ! returnIt ) {
				// Append each item from our fileGroup array to .cmb2-media-status
				media.$field.siblings( '.cmb2-media-status' ).append( fileGroup );
			} else {
				return fileGroup;
			}

		};

		handlers.single = function( selection ) {
			if ( ! handlers.single.templates ) {
				handlers.single.templates = {
					image : wp.template( 'cmb2-single-image' ),
					file  : wp.template( 'cmb2-single-file' ),
				};
			}

			// Only get one file from the uploader
			var attachment = selection.first();

			media.$field.val( attachment.get( 'url' ) );
			$id( media.field +'_id' ).val( attachment.get( 'id' ) );

			// Image preview or standard generic output if it's not an image.
			var attachmentHtml = handlers.getAttachmentHtml( attachment, 'single' );

			// add/display our output
			media.$field.siblings( '.cmb2-media-status' ).slideDown().html( attachmentHtml );
		};

		handlers.getAttachmentHtml = function( attachment, templatesId ) {
			var isImage = 'image' === attachment.get( 'type' );
			var data    = handlers.prepareData( attachment, isImage );

			// Image preview or standard generic output if it's not an image.
			return handlers[ templatesId ].templates[ isImage ? 'image' : 'file' ]( data );
		};

		handlers.prepareData = function( data, image ) {
			if ( image ) {
				// Set the correct image size data
				handlers.getImageData.call( data, 50 );
			}

			data                   = data.toJSON();
			data.mediaField        = media.field;
			data.mediaFieldName    = media.fieldName;
			data.stringRemoveImage = l10n.strings.remove_image;
			data.stringFile        = l10n.strings.file;
			data.stringDownload    = l10n.strings.download;
			data.stringRemoveFile  = l10n.strings.remove_file;

			return data;
		};

		handlers.getImageData = function( fallbackSize ) {

			// Preview size dimensions
			var previewW = media.previewSize[0] || fallbackSize;
			var previewH = media.previewSize[1] || fallbackSize;

			// Image dimensions and url
			var url    = this.get( 'url' );
			var width  = this.get( 'width' );
			var height = this.get( 'height' );
			var sizes  = this.get( 'sizes' );

			// Get the correct dimensions and url if a named size is set and exists
			// fallback to the 'large' size
			if ( sizes ) {
				if ( sizes[ media.sizeName ] ) {
					url    = sizes[ media.sizeName ].url;
					width  = sizes[ media.sizeName ].width;
					height = sizes[ media.sizeName ].height;
				} else if ( sizes.large ) {
					url    = sizes.large.url;
					width  = sizes.large.width;
					height = sizes.large.height;
				}
			}

			// Fit the image in to the preview size, keeping the correct aspect ratio
			if ( width > previewW ) {
				height = Math.floor( previewW * height / width );
				width = previewW;
			}

			if ( height > previewH ) {
				width = Math.floor( previewH * width / height );
				height = previewH;
			}

			if ( ! width ) {
				width = previewW;
			}

			if ( ! height ) {
				height = 'svg' === this.get( 'filename' ).split( '.' ).pop() ? '100%' : previewH;
			}

			this.set( 'sizeUrl', url );
			this.set( 'sizeWidth', width );
			this.set( 'sizeHeight', height );

			return this;
		};

		handlers.selectFile = function() {
			var selection = media.frames[ id ].state().get( 'selection' );
			var type = isList ? 'list' : 'single';

			if ( cmb.attach_id && isList ) {
				$( '[data-id="'+ cmb.attach_id +'"]' ).parents( 'li' ).replaceWith( handlers.list( selection, true ) );
			} else {
				handlers[type]( selection );
			}

			cmb.trigger( 'cmb_media_modal_select', selection, media );
		};

		handlers.openModal = function() {
			var selection = media.frames[ id ].state().get( 'selection' );
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
		media.frames[ id ]
			.on( 'select', handlers.selectFile )
			.on( 'open', handlers.openModal );

		// Finally, open the modal
		media.frames[ id ].open();
	};

	cmb.handleRemoveMedia = function( evt ) {
		evt.preventDefault();
		var $this = $( this );
		if ( $this.is( '.cmb-attach-list .cmb2-remove-file-button' ) ) {
			$this.parents( '.cmb2-media-item' ).remove();
			return false;
		}

		cmb.media.field = $this.attr('rel');

		cmb.metabox().find( document.getElementById( cmb.media.field ) ).val('');
		cmb.metabox().find( document.getElementById( cmb.media.field + '_id' ) ).val('');
		$this.parents('.cmb2-media-status').html('');

		return false;
	};

	cmb.cleanRow = function( $row, prevNum, group ) {
		var $elements = $row.find( cmb.repeatUpdate );
		if ( group ) {

			var $other = $row.find( '[id]' ).not( cmb.repeatUpdate );

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

		$elements.filter( ':checked' ).removeAttr( 'checked' );
		$elements.find( ':checked' ).removeAttr( 'checked' );
		$elements.filter( ':selected' ).removeAttr( 'selected' );
		$elements.find( ':selected' ).removeAttr( 'selected', false );

		if ( $row.find('h3.cmb-group-title').length ) {
			$row.find( 'h3.cmb-group-title' ).text( $row.data( 'title' ).replace( '{#}', ( cmb.idNumber + 1 ) ) );
		}

		$elements.each( function() {
			cmb.elReplacements( $( this ), prevNum, group );
		} );

		return cmb;
	};

	cmb.elReplacements = function( $newInput, prevNum, group ) {
		var oldFor    = $newInput.attr( 'for' );
		var oldVal    = $newInput.val();
		var type      = $newInput.prop( 'type' );
		var defVal    = cmb.getFieldArg( $newInput, 'default' );
		var newVal    = 'undefined' !== typeof defVal && false !== defVal ? defVal : '';
		var tagName   = $newInput.prop('tagName');
		var checkable = 'radio' === type || 'checkbox' === type ? oldVal : false;
		var attrs     = {};
		var newID, oldID;
		if ( oldFor ) {
			attrs = { 'for' : oldFor.replace( '_'+ prevNum, '_'+ cmb.idNumber ) };
		} else {
			var oldName = $newInput.attr( 'name' );
			var newName;
			oldID = $newInput.attr( 'id' );

			// Handle adding groups vs rows.
			if ( group ) {
				// Expect another bracket after group's index closing bracket.
				newName = oldName ? oldName.replace( '['+ prevNum +'][', '['+ cmb.idNumber +'][' ) : '';
				// Expect another underscore after group's index trailing underscore.
				newID   = oldID ? oldID.replace( '_' + prevNum + '_', '_' + cmb.idNumber + '_' ) : '';
			}
			else {
				// Row indexes are at the very end of the string.
				newName = oldName ? cmb.replaceLast( oldName, '[' + prevNum + ']', '[' + cmb.idNumber + ']' ) : '';
				newID   = oldID ? cmb.replaceLast( oldID, '_' + prevNum, '_' + cmb.idNumber ) : '';
			}

			attrs = {
				id: newID,
				name: newName
			};

		}

		// Clear out textarea values
		if ( 'TEXTAREA' === tagName ) {
			$newInput.html( newVal );
		}

		if ( 'SELECT' === tagName && 'undefined' !== typeof defVal ) {
			var $toSelect = $newInput.find( '[value="'+ defVal + '"]' );
			if ( $toSelect.length ) {
				$toSelect.attr( 'selected', 'selected' ).prop( 'selected', 'selected' );
			}
		}

		if ( checkable ) {
			$newInput.removeAttr( 'checked' );
			if ( 'undefined' !== typeof defVal && oldVal === defVal ) {
				$newInput.attr( 'checked', 'checked' ).prop( 'checked', 'checked' );
			}
		}

		if ( ! group && $newInput[0].hasAttribute( 'data-iterator' ) ) {
			attrs['data-iterator'] = cmb.idNumber;
		}

		$newInput
			.removeClass( 'hasDatepicker' )
			.val( checkable ? checkable : newVal ).attr( attrs );

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
		// Init pickers from new row
		cmb.initPickers( $row.find('input[type="text"].cmb2-timepicker'), $row.find('input[type="text"].cmb2-datepicker'), $row.find('input[type="text"].cmb2-colorpicker') );
	};

	cmb.updateNameAttr = function () {
		var $this = $( this );
		var name  = $this.attr( 'name' ); // get current name

		// If name is defined
		if ( 'undefined' !== typeof name ) {
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

	cmb.setDefaults = function( evt, row ) {
		$( cmb.noEmpty, row ).each( function() {
			var $el = $(this);
			var defVal = cmb.getFieldArg( $el, 'default' );
			if ( 'undefined' !== typeof defVal && false !== defVal ) {
				$el.val( defVal );
			}
		});
	};

	cmb.addGroupRow = function( evt ) {
		evt.preventDefault();

		var $this = $( this );

		// before anything significant happens
		cmb.triggerElement( $this, 'cmb2_add_group_row_start', $this );

		var $table   = $id( $this.data('selector') );
		var $oldRow  = $table.find('.cmb-repeatable-grouping').last();
		var prevNum  = parseInt( $oldRow.data('iterator'), 10 );
		cmb.idNumber = parseInt( prevNum, 10 ) + 1;
		var $row     = $oldRow.clone();
		var nodeName = $row.prop('nodeName') || 'div';
		var getRowId = function( id ) {
			id = id.split('-');
			id.splice(id.length - 1, 1);
			id.push( cmb.idNumber );
			return id.join('-');
		};

		// Make sure the next number doesn't exist.
		while ( $table.find( '.cmb-repeatable-grouping[data-iterator="'+ cmb.idNumber +'"]' ).length > 0 ) {
			cmb.idNumber++;
		}

		cmb.newRowHousekeeping( $row.data( 'title', $this.data( 'grouptitle' ) ) ).cleanRow( $row, prevNum, true );
		$row.find( '.cmb-add-row-button' ).prop( 'disabled', false );

		var $newRow = $( '<' + nodeName + ' id="'+ getRowId( $oldRow.attr('id') ) +'" class="postbox cmb-row cmb-repeatable-grouping" data-iterator="'+ cmb.idNumber +'">'+ $row.html() +'</' + nodeName + '>' );
		$oldRow.after( $newRow );

		cmb.afterRowInsert( $newRow );

		cmb.triggerElement( $table, { type: 'cmb2_add_row', group: true }, $newRow );

	};

	cmb.addAjaxRow = function( evt ) {
		evt.preventDefault();

		var $this     = $( this );
		var $table    = $id( $this.data('selector') );
		var $row      = $table.find('.empty-row');
		var prevNum   = parseInt( $row.find('[data-iterator]').data('iterator'), 10 );
		cmb.idNumber  = parseInt( prevNum, 10 ) + 1;
		var $emptyrow = $row.clone();

		cmb.newRowHousekeeping( $emptyrow ).cleanRow( $emptyrow, prevNum );

		$row.removeClass('empty-row hidden').addClass('cmb-repeat-row');
		$row.after( $emptyrow );

		cmb.afterRowInsert( $emptyrow );

		cmb.triggerElement( $table, { type: 'cmb2_add_row', group: false }, $emptyrow, $row );
	};

	cmb.removeGroupRow = function( evt ) {
		evt.preventDefault();

		var $this        = $( this );
		var confirmation = $this.data('confirm');

		// Process further only if deletion confirmation enabled and user agreed.
		if ( confirmation && ! window.confirm( confirmation ) ) {
			return;
		}

		var $table  = $id( $this.data('selector') );
		var $parent = $this.parents('.cmb-repeatable-grouping');
		var number  = $table.find('.cmb-repeatable-grouping').length;

		if ( number < 2 ) {
			return cmb.resetRow( $parent.parents('.cmb-repeatable-group').find( '.cmb-add-group-row' ), $this );
		}

		cmb.triggerElement( $table, 'cmb2_remove_group_row_start', $this );

		// When a group is removed, loop through all next groups and update fields names.
		$parent.nextAll( '.cmb-repeatable-grouping' ).find( cmb.repeatEls ).each( cmb.updateNameAttr );

		$parent.remove();

		cmb.triggerElement( $table, { type: 'cmb2_remove_row', group: true } );
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

		if ( number <= 2 ) {
			return cmb.resetRow( $parent.find( '.cmb-add-row-button' ), $this );
		}

		if ( $parent.hasClass('empty-row') ) {
			$parent.prev().addClass( 'empty-row' ).removeClass('cmb-repeat-row');
		}

		$this.parents('.cmb-repeat-table .cmb-row').remove();


		cmb.triggerElement( $table, { type: 'cmb2_remove_row', group: false } );
	};

	cmb.resetRow = function( $addNewBtn, $removeBtn ) {
		// Click the "add new" button followed by the "remove this" button
		// in order to reset the repeat row to empty values.
		$addNewBtn.trigger( 'click' );
		$removeBtn.trigger( 'click' );
	};

	cmb.shiftRows = function( evt ) {

		evt.preventDefault();

		var $this = $( this );
		var $from = $this.parents( '.cmb-repeatable-grouping' );
		var $goto = $this.hasClass( 'move-up' ) ? $from.prev( '.cmb-repeatable-grouping' ) : $from.next( '.cmb-repeatable-grouping' );

		// Before shift occurs.
		cmb.triggerElement( $this, 'cmb2_shift_rows_enter', $this, $from, $goto );

		if ( ! $goto.length ) {
			return;
		}

		// About to shift
		cmb.triggerElement( $this, 'cmb2_shift_rows_start', $this, $from, $goto );

		var inputVals = [];
		// Loop this item's fields
		$from.find( cmb.repeatEls ).each( function() {
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

		$from.find( 'input[data-checked=true]' ).prop( 'checked', true ).removeAttr( 'data-checked' );
		$goto.find( 'input[data-checked=true]' ).prop( 'checked', true ).removeAttr( 'data-checked' );

		// trigger color picker change event
		$from.find( 'input[type="text"].cmb2-colorpicker' ).trigger( 'change' );
		$goto.find( 'input[type="text"].cmb2-colorpicker' ).trigger( 'change' );

		// shift done
		cmb.triggerElement( $this, 'cmb2_shift_rows_complete', $this, $from, $goto );
	};

	cmb.initPickers = function( $timePickers, $datePickers, $colorPickers ) {
		cmb.trigger( 'cmb_init_pickers', {
			time: $timePickers,
			date: $datePickers,
			color: $colorPickers
		} );

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
			var $picker = $id( 'ui-datepicker-div' ).removeClass( 'cmb2-element' ).hide();
			if ( 'timepicker' === method && ! $( inst.input ).val() ) {
				// Set the timepicker field value if it's empty.
				inst.input.val( $picker.find( '.ui_tpicker_time' ).text() );
			}

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
		if ( 'object' === typeof jQuery.wp && 'function' === typeof jQuery.wp.wpColorPicker ) {

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

	cmb.initCodeEditors = function( $selector ) {
		cmb.trigger( 'cmb_init_code_editors', $selector );

		if ( ! cmb.defaults.code_editor || ! wp || ! wp.codeEditor || ! $selector.length ) {
			return;
		}

		$selector.each( function() {
			wp.codeEditor.initialize(
				this.id,
				cmb.codeEditorArgs( $( this ).data( 'codeeditor' ) )
			);
		} );
	};

	cmb.codeEditorArgs = function( overrides ) {
		var props = [ 'codemirror', 'csslint', 'jshint', 'htmlhint' ];
		var args = $.extend( {}, cmb.defaults.code_editor );
		overrides = overrides || {};

		for ( var i = props.length - 1; i >= 0; i-- ) {
			if ( overrides.hasOwnProperty( props[i] ) ) {
				args[ props[i] ] = $.extend( {}, args[ props[i] ] || {}, overrides[ props[i] ] );
			}
		}

		return args;
	};

	cmb.makeListSortable = function() {
		var $filelist = cmb.metabox().find( '.cmb2-media-status.cmb-attach-list' );
		if ( $filelist.length ) {
			$filelist.sortable({ cursor: 'move' }).disableSelection();
		}
	};

	cmb.makeRepeatableSortable = function() {
		var $repeatables = cmb.metabox().find( '.cmb-repeat-table .cmb-field-list' );

		if ( $repeatables.length ) {
			$repeatables.sortable({
				items : '.cmb-repeat-row',
				cursor: 'move'
			});
		}
	};

	cmb.maybeOembed = function( evt ) {
		var $this = $( this );

		var m = {
			focusout : function() {
				setTimeout( function() {
					// if it's been 2 seconds, hide our spinner
					cmb.spinner( '.cmb2-metabox', true );
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
	 *
	 * @since  0.9.4
	 *
	 * @return {return}
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

	/**
	 * Gets jQuery object containing all CMB metaboxes. Caches the result.
	 *
	 * @since  1.0.2
	 *
	 * @return {Object} jQuery object containing all CMB metaboxes.
	 */
	cmb.metabox = function() {
		if ( cmb.$metabox ) {
			return cmb.$metabox;
		}
		cmb.$metabox = $('.cmb2-wrap > .cmb2-metabox');
		return cmb.$metabox;
	};

	/**
	 * Starts/stops contextual spinner.
	 *
	 * @since  1.0.1
	 *
	 * @param  {object} $context The jQuery parent/context object.
	 * @param  {bool} hide       Whether to hide the spinner (will show by default).
	 *
	 * @return {void}
	 */
	cmb.spinner = function( $context, hide ) {
		var m = hide ? 'removeClass' : 'addClass';
		$('.cmb-spinner', $context )[ m ]( 'is-active' );
	};

	/**
	 * Triggers a jQuery event on the document object.
	 *
	 * @since  2.2.3
	 *
	 * @param  {string} evtName The name of the event to trigger.
	 *
	 * @return {void}
	 */
	cmb.trigger = function( evtName ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		args.push( cmb );
		$document.trigger( evtName, args );
	};

	/**
	 * Triggers a jQuery event on the given jQuery object.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} $el     The jQuery element object.
	 * @param  {string} evtName The name of the event to trigger.
	 *
	 * @return {void}
	 */
	cmb.triggerElement = function( $el, evtName ) {
		var args = Array.prototype.slice.call( arguments, 2 );
		args.push( cmb );
		$el.trigger( evtName, args );
	};

	/**
	 * Get an argument for a given field.
	 *
	 * @since  2.5.0
	 *
	 * @param  {string|object} hash The field hash, id, or a jQuery object for a field.
	 * @param  {string}        arg  The argument to get on the field.
	 *
	 * @return {mixed}              The argument value.
	 */
	cmb.getFieldArg = function( hash, arg ) {
		return cmb.getField( hash )[ arg ];
	};

	/**
	 * Get a field object instances. Can be filtered by passing in a filter callback function.
	 * e.g. `const fileFields = CMB2.getFields(f => 'file' === f.type);`
	 *
	 * @since  2.5.0
	 *
	 * @param  {mixed} filterCb An optional filter callback function.
	 *
	 * @return array            An array of field object instances.
	 */
	cmb.getFields = function( filterCb ) {
		if ( 'function' === typeof filterCb ) {
			var fields = [];
			$.each( l10n.fields, function( hash, field ) {
				if ( filterCb( field, hash ) ) {
					fields.push( field );
				}
			});
			return fields;
		}

		return l10n.fields;
	};

	/**
	 * Get a field object instance by hash or id.
	 *
	 * @since  2.5.0
	 *
	 * @param  {string|object} hash The field hash, id, or a jQuery object for a field.
	 *
	 * @return {object}        The field object or an empty object.
	 */
	cmb.getField = function( hash ) {
		var field = {};
		hash = hash instanceof jQuery ? hash.data( 'hash' ) : hash;
		if ( hash ) {
			try {
				if ( l10n.fields[ hash ] ) {
					throw new Error( hash );
				}

				cmb.getFields( function( field ) {
					if ( 'function' === typeof hash ) {
						if ( hash( field ) ) {
							throw new Error( field.hash );
						}
					} else  if ( field.id && field.id === hash ) {
						throw new Error( field.hash );
					}
				});
			} catch( e ) {
				field = l10n.fields[ e.message ];
			}
		}

		return field;
	};

	/**
	 * Safely log things if query var is set. Accepts same parameters as console.log.
	 *
	 * @since  1.0.0
	 *
	 * @return {void}
	 */
	cmb.log = function() {
		if ( l10n.script_debug && console && 'function' === typeof console.log ) {
			console.log.apply(console, arguments);
		}
	};

	/**
	 * Replace the last occurrence of a string.
	 *
	 * @since  2.2.6
	 *
	 * @param  {string} string  String to search/replace.
	 * @param  {string} search  String to search.
	 * @param  {string} replace String to replace search with.
	 *
	 * @return {string}         Possibly modified string.
	 */
	cmb.replaceLast = function( string, search, replace ) {
		// find the index of last time word was used
		var n = string.lastIndexOf( search );

		// slice the string in 2, one from the start to the lastIndexOf
		// and then replace the word in the rest
		return string.slice( 0, n ) + string.slice( n ).replace( search, replace );
	};

	// Kick it off!
	$( cmb.init );

})(window, document, jQuery, window.CMB2);
