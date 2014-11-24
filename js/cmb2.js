/**
 * Controls the behaviours of custom metabox fields.
 *
 * @author Andrew Norcross
 * @author Jared Atchison
 * @author Bill Erickson
 * @author Justin Sternberg
 * @see    https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * Custom jQuery for Custom Metaboxes and Fields
 */
window.CMB2 = (function(window, document, $, undefined){
	'use strict';

	// localization strings
	var l10n = window.cmb2_l10;
	var setTimeout = window.setTimeout;

	// CMB functionality object
	var cmb = {
		formfield          : '',
		idNumber           : false,
		file_frames        : {},
		repeatEls          : 'input:not([type="button"]),select,textarea,.cmb2-media-status',
		defaults : {
			time_picker  : l10n.defaults.time_picker,
			date_picker  : l10n.defaults.date_picker,
			color_picker : l10n.defaults.color_picker || {},
		},
		styleBreakPoint : 450,
	};

	cmb.metabox = function() {
		if ( cmb.$metabox ) {
			return cmb.$metabox;
		}
		cmb.$metabox = $('.cmb2-wrap > .cmb2-metabox');
		return cmb.$metabox;
	};

	cmb.init = function() {

		cmb.log( 'CMB2 localized data', l10n );
		var $metabox = cmb.metabox();
		var $repeatGroup = $metabox.find('.cmb-repeatable-group');

		/**
		 * Initialize time/date/color pickers
		 */
		cmb.initPickers( $metabox.find('input[type="text"].cmb2-timepicker'), $metabox.find('input[type="text"].cmb2-datepicker'), $metabox.find('input[type="text"].cmb2-colorpicker') );

		// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
		$("#ui-datepicker-div").wrap('<div class="cmb2-element" />');

		// Insert toggle button into DOM wherever there is multicheck. credit: Genesis Framework
		$( '<p><span class="button cmb-multicheck-toggle">' + l10n.strings.check_toggle + '</span></p>' ).insertBefore( '.cmb2-checkbox-list:not(.no-select-all)' );

		// Make File List drag/drop sortable:
		cmb.makeListSortable();

		$metabox
			.on( 'change', '.cmb2_upload_file', function() {
				cmb.formfield = $(this).attr('id');
				$('#' + cmb.formfield + '_id').val('');
			})
			// Media/file management
			.on( 'click', '.cmb-multicheck-toggle', cmb.toggleCheckBoxes )
			.on( 'click', '.cmb2-upload-button', cmb.handleMedia )
			.on( 'click', '.cmb2-remove-file-button', cmb.handleRemoveMedia )
			// Repeatable content
			.on( 'click', '.cmb-add-group-row', cmb.addGroupRow )
			.on( 'click', '.cmb-add-row-button', cmb.addAjaxRow )
			.on( 'click', '.cmb-remove-group-row', cmb.removeGroupRow )
			.on( 'click', '.cmb-remove-row-button', cmb.removeAjaxRow )
			// Ajax oEmbed display
			.on( 'keyup paste focusout', '.cmb2-oembed', cmb.maybeOembed )
			// Reset titles when removing a row
			.on( 'cmb2_remove_row', '.cmb-repeatable-group', cmb.resetTitlesAndIterator );

		if ( $repeatGroup.length ) {
			$repeatGroup
				.filter('.sortable').each( function() {
					// Add sorting arrows
					$(this).find( '.cmb-remove-group-row' ).before( '<a class="button cmb-shift-rows move-up alignleft" href="#"><span class="'+ l10n.up_arrow_class +'"></span></a> <a class="button cmb-shift-rows move-down alignleft" href="#"><span class="'+ l10n.down_arrow_class +'"></span></a>' );
				})
				.on( 'click', '.cmb-shift-rows', cmb.shiftRows )
				.on( 'cmb2_add_row', cmb.emptyValue );
		}

		// on pageload
		setTimeout( cmb.resizeoEmbeds, 500);
		// and on window resize
		$(window).on( 'resize', cmb.resizeoEmbeds );

	};

	cmb.resetTitlesAndIterator = function() {
		// Loop repeatable group tables
		$( '.cmb-repeatable-group' ).each( function() {
			var $table = $(this);
			// Loop repeatable group table rows
			$table.find( '.cmb-repeatable-grouping' ).each( function( rowindex ) {
				var $row = $(this);
				// Reset rows iterator
				$row.data( 'iterator', rowindex );
				// Reset rows title
				$row.find( '.cmb-group-title h4' ).text( $table.find( '.cmb-add-group-row' ).data( 'grouptitle' ).replace( '{#}', ( rowindex + 1 ) ) );
			});
		});
	};

	cmb.toggleCheckBoxes = function( event ) {
		event.preventDefault();
		var $self = $(this);
		var $multicheck = $self.closest( '.cmb-td' ).find( 'input[type=checkbox]' );

		// If the button has already been clicked once...
		if ( $self.data( 'checked' ) ) {
			// clear the checkboxes and remove the flag
			$multicheck.prop( 'checked', false );
			$self.data( 'checked', false );
		}
		// Otherwise mark the checkboxes and add a flag
		else {
			$multicheck.prop( 'checked', true );
			$self.data( 'checked', true );
		}
	};

	cmb.handleMedia = function(event) {

		if ( ! wp ) {
			return;
		}

		event.preventDefault();

		var $metabox     = cmb.metabox();
		var $self        = $(this);
		cmb.formfield    = $self.prev('input').attr('id');
		var $formfield   = $('#'+cmb.formfield);
		var previewSize  = $formfield.data( 'previewsize' );
		var formName     = $formfield.attr('name');
		var uploadStatus = true;
		var attachment   = true;
		var isList       = $self.hasClass( 'cmb2-upload-list' );

		// If this field's media frame already exists, reopen it.
		if ( cmb.formfield in cmb.file_frames ) {
			cmb.file_frames[cmb.formfield].open();
			return;
		}

		// Create the media frame.
		cmb.file_frames[cmb.formfield] = wp.media.frames.file_frame = wp.media({
			title: $metabox.find('label[for=' + cmb.formfield + ']').text(),
			button: {
				text: l10n.strings.upload_file
			},
			multiple: isList ? true : false
		});

		var handlers = {
			list : function( selection ) {
				// Get all of our selected files
				attachment = selection.toJSON();

				$formfield.val(attachment.url);
				$('#'+ cmb.formfield +'_id').val(attachment.id);

				// Setup our fileGroup array
				var fileGroup = [];

				// Loop through each attachment
				$( attachment ).each( function() {
					if ( this.type && this.type === 'image' ) {
						var width = previewSize[0] ? previewSize[0] : 50;
						var height = previewSize[1] ? previewSize[1] : 50;

						// image preview
						uploadStatus = '<li class="img-status">'+
							'<img width="'+ width +'" height="'+ height +'" src="' + this.url + '" class="attachment-'+ width +'px'+ height +'px" alt="'+ this.filename +'">'+
							'<p><a href="#" class="cmb2-remove-file-button" rel="'+ cmb.formfield +'['+ this.id +']">'+ l10n.strings.remove_image +'</a></p>'+
							'<input type="hidden" id="filelist-'+ this.id +'" name="'+ formName +'['+ this.id +']" value="' + this.url + '">'+
						'</li>';

					} else {
						// Standard generic output if it's not an image.
						uploadStatus = '<li>'+ l10n.strings.file +' <strong>'+ this.filename +'</strong>&nbsp;&nbsp;&nbsp; (<a href="' + this.url + '" target="_blank" rel="external">'+ l10n.strings.download +'</a> / <a href="#" class="cmb2-remove-file-button" rel="'+ cmb.formfield +'['+ this.id +']">'+ l10n.strings.remove_file +'</a>)'+
							'<input type="hidden" id="filelist-'+ this.id +'" name="'+ formName +'['+ this.id +']" value="' + this.url + '">'+
						'</li>';

					}

					// Add our file to our fileGroup array
					fileGroup.push( uploadStatus );
				});

				// Append each item from our fileGroup array to .cmb2-media-status
				$( fileGroup ).each( function() {
					$formfield.siblings('.cmb2-media-status').slideDown().append(this);
				});
			},
			single : function( selection ) {
				// Only get one file from the uploader
				attachment = selection.first().toJSON();

				$formfield.val(attachment.url);
				$('#'+ cmb.formfield +'_id').val(attachment.id);

				if ( attachment.type && attachment.type === 'image' ) {
					// image preview
					var width = previewSize[0] ? previewSize[0] : 350;
					uploadStatus = '<div class="img-status"><img width="'+ width +'px" style="max-width: '+ width +'px; width: 100%; height: auto;" src="' + attachment.url + '" alt="'+ attachment.filename +'" title="'+ attachment.filename +'" /><p><a href="#" class="cmb2-remove-file-button" rel="' + cmb.formfield + '">'+ l10n.strings.remove_image +'</a></p></div>';
				} else {
					// Standard generic output if it's not an image.
					uploadStatus = l10n.strings.file +' <strong>'+ attachment.filename +'</strong>&nbsp;&nbsp;&nbsp; (<a href="'+ attachment.url +'" target="_blank" rel="external">'+ l10n.strings.download +'</a> / <a href="#" class="cmb2-remove-file-button" rel="'+ cmb.formfield +'">'+ l10n.strings.remove_file +'</a>)';
				}

				// add/display our output
				$formfield.siblings('.cmb2-media-status').slideDown().html(uploadStatus);
			}
		};

		// When an file is selected, run a callback.
		cmb.file_frames[cmb.formfield].on( 'select', function() {
			var selection = cmb.file_frames[cmb.formfield].state().get('selection');
			var type = isList ? 'list' : 'single';
			handlers[type]( selection );
		});

		// Finally, open the modal
		cmb.file_frames[cmb.formfield].open();
	};

	cmb.handleRemoveMedia = function( event ) {
		event.preventDefault();
		var $self = $(this);
		if ( $self.is( '.cmb-attach-list .cmb2-remove-file-button' ) ){
			$self.parents('li').remove();
			return false;
		}
		cmb.formfield    = $self.attr('rel');
		var $container   = $self.parents('.img-status');

		cmb.metabox().find('input#' + cmb.formfield).val('');
		cmb.metabox().find('input#' + cmb.formfield + '_id').val('');
		if ( ! $container.length ) {
			$self.parents('.cmb2-media-status').html('');
		} else {
			$container.html('');
		}
		return false;
	};

	// src: http://www.benalman.com/projects/jquery-replacetext-plugin/
	$.fn.replaceText = function(b, a, c) {
		return this.each(function() {
			var f = this.firstChild, g, e, d = [];
			if (f) {
				do {
					if (f.nodeType === 3) {
						g = f.nodeValue;
						e = g.replace(b, a);
						if (e !== g) {
							if (!c && /</.test(e)) {
								$(f).before(e);
								d.push(f);
							} else {
								f.nodeValue = e;
							}
						}
					}
				} while (f = f.nextSibling);
			}
			if ( d.length ) { $(d).remove(); }
		});
	};

	$.fn.cleanRow = function( prevNum, group ) {
		var $self = $(this);
		var $inputs = $self.find('input:not([type="button"]), select, textarea, label');
		if ( group ) {
			// Remove extra ajaxed rows
			$self.find('.cmb-repeat-table .cmb-repeat-row:not(:first-child)').remove();
		}
		cmb.neweditor_id = [];

		$inputs.filter(':checked').removeAttr( 'checked' );
		$inputs.filter(':selected').removeAttr( 'selected' );

		if ( $self.find('.cmb-group-title').length ) {
			$self.find( '.cmb-group-title h4' ).text( $self.data( 'title' ).replace( '{#}', ( cmb.idNumber + 1 ) ) );
		}

		$inputs.each( function(){
			var $newInput = $(this);
			var isEditor  = $newInput.hasClass( 'wp-editor-area' );
			var oldFor    = $newInput.attr( 'for' );
			// var $next     = $newInput.next();
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

			$newInput
				.removeClass( 'hasDatepicker' )
				.attr( attrs ).val('');

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

		});

		return this;
	};

	$.fn.newRowHousekeeping = function() {
		var $row         = $(this);
		var $colorPicker = $row.find( '.wp-picker-container' );
		var $list        = $row.find( '.cmb2-media-status' );

		if ( $colorPicker.length ) {
			// Need to clean-up colorpicker before appending
			$colorPicker.each( function() {
				var $td = $(this).parent();
				$td.html( $td.find( 'input[type="text"].cmb2-colorpicker' ).attr('style', '') );
			});
		}

		// Need to clean-up colorpicker before appending
		if ( $list.length ) {
			$list.empty();
		}

		return this;
	};

	cmb.afterRowInsert = function( $row, group ) {
		var $focus = $row.find('input:not([type="button"]), textarea, select').first();
		if ( $focus.length ) {
			if ( group ) {
				$('html, body').animate({
					scrollTop: Math.round( $focus.offset().top - 150 )
				}, 1000);
			}
			$focus.focus();
		}

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

		var $this = $(this);
		var name  = $this.attr( 'name' ); // get current name

		// No name? bail
		if ( typeof name === 'undefined' ) {
			return false;
		}

		var prevNum = parseInt( $this.parents( '.cmb-repeatable-grouping' ).data( 'iterator' ) );
		var newNum  = prevNum - 1; // Subtract 1 to get new iterator number

		// Update field name attributes so data is not orphaned when a row is removed and post is saved
		var $newName = name.replace( '[' + prevNum + ']', '[' + newNum + ']' );

		// New name with replaced iterator
		$this.attr( 'name', $newName );

	};

	cmb.emptyValue = function( event, row ) {
		$('input:not([type="button"]), textarea', row).val('');
	};

	cmb.addGroupRow = function( event ) {

		event.preventDefault();

		var $self    = $(this);
		var $table   = $('#'+ $self.data('selector'));
		var $oldRow  = $table.find('.cmb-repeatable-grouping').last();
		var prevNum  = parseInt( $oldRow.data('iterator') );
		cmb.idNumber = prevNum + 1;
		var $row     = $oldRow.clone();

		$row.data( 'title', $self.data( 'grouptitle' ) ).newRowHousekeeping().cleanRow( prevNum, true );

		var $newRow = $( '<div class="cmb-row cmb-repeatable-grouping" data-iterator="'+ cmb.idNumber +'">'+ $row.html() +'</div>' );
		$oldRow.after( $newRow );

		cmb.afterRowInsert( $newRow, true );

		if ( $table.find('.cmb-repeatable-grouping').length <= 1  ) {
			$table.find('.cmb-remove-group-row').attr( 'disabled', 'disabled' );
		} else {
			$table.find('.cmb-remove-group-row').removeAttr( 'disabled' );
		}

		$table.trigger( 'cmb2_add_row', $newRow );
	};

	cmb.addAjaxRow = function( event ) {

		event.preventDefault();

		var $self         = $(this);
		var tableselector = '#'+ $self.data('selector');
		var $table        = $(tableselector);
		var $emptyrow     = $table.find('.empty-row');
		var prevNum       = parseInt( $emptyrow.find('[data-iterator]').data('iterator') );
		cmb.idNumber      = prevNum + 1;
		var $row          = $emptyrow.clone();

		$row.newRowHousekeeping().cleanRow( prevNum );

		$emptyrow.removeClass('empty-row hidden').addClass('cmb-repeat-row');
		$emptyrow.after( $row );

		cmb.afterRowInsert( $row );
		$table.trigger( 'cmb2_add_row', $row );

		$table.find( '.cmb-remove-row-button' ).removeAttr( 'disabled' );

	};

	cmb.removeGroupRow = function( event ) {
		event.preventDefault();
		var $self   = $(this);
		var $table  = $('#'+ $self.data('selector'));
		var $parent = $self.parents('.cmb-repeatable-grouping');
		var number  = $table.find('.cmb-repeatable-grouping').length;

		if ( number > 1 ) {
			// when a group is removed loop through all next groups and update fields names
			$parent.nextAll( '.cmb-repeatable-grouping' ).find( cmb.repeatEls ).each( cmb.updateNameAttr );

			$parent.remove();
			if ( number <= 2 ) {
				$table.find('.cmb-remove-group-row').attr( 'disabled', 'disabled' );
			} else {
				$table.find('.cmb-remove-group-row').removeAttr( 'disabled' );
			}
			$table.trigger( 'cmb2_remove_row' );
		}

	};

	cmb.removeAjaxRow = function( event ) {
		event.preventDefault();
		var $self   = $(this);
		var $parent = $self.parents('.cmb-row');
		var $table  = $self.parents('.cmb-repeat-table');
		var number  = $table.find('.cmb-row').length;

		if ( number > 2 ) {
			if ( $parent.hasClass('empty-row') ) {
				$parent.prev().addClass( 'empty-row' ).removeClass('cmb-repeat-row');
			}
			$self.parents('.cmb-repeat-table .cmb-row').remove();
			if ( number === 3 ) {
				$table.find( '.cmb-remove-row-button' ).attr( 'disabled', 'disabled' );
			}
			$table.trigger( 'cmb2_remove_row' );
		} else {
			$self.attr( 'disabled', 'disabled' );
		}
	};

	cmb.shiftRows = function( event ) {

		event.preventDefault();

		var $self     = $(this);
		var $parent   = $self.parents( '.cmb-repeatable-grouping' );
		var $goto     = $self.hasClass( 'move-up' ) ? $parent.prev( '.cmb-repeatable-grouping' ) : $parent.next( '.cmb-repeatable-grouping' );

		if ( ! $goto.length ) {
			return;
		}

		var inputVals = [];
		// Loop this items fields
		$parent.find( cmb.repeatEls ).each( function() {
			var $element = $(this);
			var val;
			if ( $element.hasClass('cmb2-media-status') ) {
				// special case for image previews
				val = $element.html();
			} else if ( 'checkbox' === $element.attr('type') ) {
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
			var $element = $(this);
			var val;

			if ( $element.hasClass('cmb2-media-status') ) {
				// special case for image previews
				val = $element.html();
				$element.html( inputVals[ index ]['val'] );
				inputVals[ index ]['$'].html( val );

			}
			// handle checkbox swapping
			else if ( 'checkbox' === $element.attr('type') ) {
				inputVals[ index ]['$'].prop( 'checked', $element.is(':checked') );
				$element.prop( 'checked', inputVals[ index ]['val'] );
			}
			// handle select swapping
			else if ( 'select' === $element.prop('tagName') ) {
				inputVals[ index ]['$'].prop( 'selected', $element.is(':selected') );
				$element.prop( 'selected', inputVals[ index ]['val'] );
			}
			// handle normal input swapping
			else {
				inputVals[ index ]['$'].val( $element.val() );
				$element.val( inputVals[ index ]['val'] );
			}
		});
	};

	cmb.initPickers = function( $timePickers, $datePickers, $colorPickers ) {
		// Initialize timepicker
		cmb.initTimePickers( $timePickers );

		// Initialize jQuery UI datepicker
		cmb.initDatePickers( $datePickers );

		// Initialize color picker
		cmb.initColorPickers( $colorPickers );
	};

	cmb.initTimePickers = function( $selector ) {
		if ( ! $selector.length ) {
			return;
		}

		$selector.timePicker( cmb.defaults.time_picker );
	};

	cmb.initDatePickers = function( $selector ) {
		if ( ! $selector.length ) {
			return;
		}

		$selector.datepicker( "destroy" );
		$selector.datepicker( cmb.defaults.date_picker );
	};

	cmb.initColorPickers = function( $selector ) {
		if ( ! $selector.length ) {
			return;
		}
		if (typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function') {

			$selector.wpColorPicker( cmb.defaults.color_picker );

		} else {
			$selector.each( function(i) {
				$(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
				$('#picker-' + i).hide().farbtastic($(this));
			})
			.focus( function() {
				$(this).next().show();
			})
			.blur( function() {
				$(this).next().hide();
			});
		}
	};

	cmb.makeListSortable = function() {
		var $filelist = cmb.metabox().find( '.cmb2-media-status.cmb-attach-list' );
		if ( $filelist.length ) {
			$filelist.sortable({ cursor: "move" }).disableSelection();
		}
	};

	cmb.maybeOembed = function( evt ) {
		var $self = $(this);
		var type = evt.type;

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
					cmb.doAjax( $self, evt);
				}
			},
			paste : function() {
				// paste event is fired before the value is filled, so wait a bit
				setTimeout( function() { cmb.doAjax( $self ); }, 100);
			}
		};
		m[type]();

	};

	/**
	 * Resize oEmbed videos to fit in their respective metaboxes
	 */
	cmb.resizeoEmbeds = function() {
		cmb.metabox().each( function() {
			var $self      = $(this);
			var $tableWrap = $self.parents('.inside');
			var isSide     = $self.parents('.inner-sidebar').length || $self.parents( '#side-sortables' ).length;
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

			var $embeds   = $self.find('.cmb-type-oembed .embed-status');
			var $children = $embeds.children().not('.cmb2-remove-wrapper');
			if ( ! $children.length ) {
				return true; // continue
			}

			$children.each( function() {
				var $self     = $(this);
				var iwidth    = $self.width();
				var iheight   = $self.height();
				var _newWidth = newWidth;
				if ( $self.parents( '.cmb-repeat-row' ).length && ! isSmall ) {
					// Make room for our repeatable "remove" button column
					_newWidth = newWidth - 91;
					_newWidth = 785 > tableW ? _newWidth - 15 : _newWidth;
				}
				// Calc new height
				var newHeight = Math.round((_newWidth * iheight)/iwidth);
				$self.width(_newWidth).height(newHeight);
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
	cmb.doAjax = function($obj) {
		// get typed value
		var oembed_url = $obj.val();
		// only proceed if the field contains more than 6 characters
		if ( oembed_url.length < 6 ) {
			return;
		}

		// only proceed if the user has pasted, pressed a number, letter, or whitelisted characters

			// get field id
			var field_id = $obj.attr('id');
			// get our inputs $context for pinpointing
			var $context = $obj.parents('.cmb-repeat-table  .cmb-row .cmb-td');
			$context = $context.length ? $context : $obj.parents('.cmb2-metabox .cmb-row .cmb-td');

			var embed_container = $('.embed-status', $context);
			var oembed_width = $obj.width();
			var child_el = $(':first-child', embed_container);

			// http://www.youtube.com/watch?v=dGG7aru2S6U
			cmb.log( 'oembed_url', oembed_url, field_id );
			oembed_width = ( embed_container.length && child_el.length ) ? child_el.width() : $obj.width();

			// show our spinner
			cmb.spinner( $context );
			// clear out previous results
			$('.embed_wrap', $context).html('');
			// and run our ajax function
			setTimeout( function() {
				// if they haven't typed in 500 ms
				if ( $('.cmb2-oembed:focus').val() !== oembed_url ) {
					return;
				}
				$.ajax({
					type : 'post',
					dataType : 'json',
					url : l10n.ajaxurl,
					data : {
						'action': 'cmb2_oembed_handler',
						'oembed_url': oembed_url,
						'oembed_width': oembed_width > 300 ? oembed_width : 300,
						'field_id': field_id,
						'object_id': $obj.data('objectid'),
						'object_type': $obj.data('objecttype'),
						'cmb2_ajax_nonce': l10n.ajax_nonce
					},
					success: function(response) {
						cmb.log( response );
						// hide our spinner
						cmb.spinner( $context, true );
						// and populate our results from ajax response
						$('.embed_wrap', $context).html(response.data);
					}
				});

			}, 500);
	};

	$(document).ready(cmb.init);

	return cmb;

})(window, document, jQuery);
