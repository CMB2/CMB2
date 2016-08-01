/**
 * Used for WYSIWYG logic
 */
window.CMB2 = window.CMB2 || {};
window.CMB2.wysiwyg = window.CMB2.wysiwyg || {};

( function(window, document, $, wysiwyg, undefined ) {
	'use strict';

	// Private variables
	var toBeDestroyed = [];
	var toBeInitialized = [];
	var all = wysiwyg.all = {};

	// Private functions

	/**
	 * Initializes any editors that weren't initialized because they didn't exist yet.
	 *
	 * @since  2.2.3
	 *
	 * @return {void}
	 */
	function delayedInit() {

		// Don't initialize until they've all been destroyed.
		if ( 0 === toBeDestroyed.length ) {
			toBeInitialized.forEach( function ( toInit ) {
				toBeInitialized.splice( toBeInitialized.indexOf( toInit ), 1 );
				wysiwyg.init.apply( wysiwyg, toInit );
			} );
		} else {
			window.setTimeout( delayedInit, 100 );
		}
	}

	/**
	 * Destroys any editors that weren't destroyed because they didn't exist yet.
	 *
	 * @since  2.2.3
	 *
	 * @return {void}
	 */
	function delayedDestroy() {
		toBeDestroyed.forEach( function( id ) {
			toBeDestroyed.splice( toBeDestroyed.indexOf( id ), 1 );
			wysiwyg.destroy( id );
		} );
	}

	/**
	 * Gets the option data for a group (and initializes that data if it doesn't exist).
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} data The group/field data.
	 *
	 * @return {object}      Options data object for a group.
	 */
	function getGroupData( data ) {
		var groupid = data.groupid;
		var fieldid = data.fieldid;

		if ( ! all[ groupid ] || ! all[ groupid ][ fieldid ] ) {
			all[ groupid ] = all[ groupid ] || {};
			all[ groupid ][ fieldid ] = {
				template : wp.template( 'cmb2-wysiwyg-' + groupid + '-' + fieldid ),
				defaults : {

					// Get the data from the template-wysiwyg initiation.
					mce : $.extend( {}, tinyMCEPreInit.mceInit[ 'cmb2_i_' + groupid + fieldid ] ),
					qt  : $.extend( {}, tinyMCEPreInit.qtInit[ 'cmb2_i_' + groupid + fieldid ] )
				}
			};
			// This is the template-wysiwyg data, and we do not want that to be initiated.
			delete tinyMCEPreInit.mceInit[ 'cmb2_i_' + groupid + fieldid ];
			delete tinyMCEPreInit.qtInit[ 'cmb2_i_' + groupid + fieldid ];
		}

		return all[ groupid ][ fieldid ];
	}

	/**
	 * Initiates the tinyMCEPreInit options for a wysiwyg editor instance.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} options Options data object for the wysiwyg editor instance.
	 *
	 * @return {void}
	 */
	function initOptions( options ) {
		var nameRegex = new RegExp( 'cmb2_n_' + options.groupid + options.fieldid, 'g' );
		var idRegex   = new RegExp( 'cmb2_i_' + options.groupid + options.fieldid, 'g' );
		var prop, newSettings, newQTS;

		// If no settings for this field. Clone from placeholder.
		if ( 'undefined' === typeof( tinyMCEPreInit.mceInit[ options.id ] ) ) {
			newSettings = $.extend( {}, options.defaults.mce );
			for ( prop in newSettings ) {
				if ( 'string' === typeof( newSettings[ prop ] ) ) {
					newSettings[ prop ] = newSettings[ prop ]
						.replace( idRegex, options.id )
						.replace( nameRegex, options.name );
				}
			}
			tinyMCEPreInit.mceInit[ options.id ] = newSettings;
		}

		// If no Quicktag settings for this field. Clone from placeholder.
		if ( 'undefined' === typeof( tinyMCEPreInit.qtInit[ options.id ] ) ) {
			newQTS = $.extend( {}, options.defaults.qt );
			for ( prop in newQTS ) {
				if ( 'string' === typeof( newQTS[ prop ] ) ) {
					newQTS[ prop ] = newQTS[ prop ]
						.replace( idRegex, options.id )
						.replace( nameRegex, options.name );
				}
			}
			tinyMCEPreInit.qtInit[ options.id ] = newQTS;
		}
	}

	/**
	 * Initializes all group wysiwyg editors. Hooked to cmb_init.
	 *
	 * @since  2.2.3
	 *
	 * @return {void}
	 */
	wysiwyg.initAll = function() {
		var $this,data,initiated;

		$( '.cmb2-wysiwyg-placeholder' ).each( function() {
			$this = $( this );
			data  = $this.data();

			if ( data.groupid ) {

				data.id    = $this.attr( 'id' );
				data.name  = $this.attr( 'name' );
				data.value = $this.val();

				wysiwyg.init( $this, data, false );
				initiated = true;
			}
		} );

		if ( true === initiated ) {
			window.QTags._buttonsInit();
		}
	};

	/**
	 * Initiates wysiwyg editors in a new group row. Hooked to cmb2_add_row.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} evt A jQuery-normalized event object.
	 * @param  {object} $row A jQuery dom element object for the group row.
	 *
	 * @return {void}
	 */
	wysiwyg.addRow = function( evt, $row ) {
		wysiwyg.initRow( $row );
	};

	/**
	 * Destroys wysiwyg editors in a group row when that row is removed. Hooked to cmb2_remove_group_row_start.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} evt A jQuery-normalized event object.
	 * @param  {object} $btn A jQuery dom element object for the remove-row button.
	 *
	 * @return {void}
	 */
	wysiwyg.destroyRowEditors = function( evt, $btn ) {
		wysiwyg.destroy( $btn.parents( '.cmb-repeatable-grouping' ).find( '.wp-editor-area' ).attr( 'id' ) );
	};

	/**
	 * When a row-shift starts, we need to destroy the wysiwyg editors for the group-rows being shuffled.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} evt   A jQuery-normalized event object.
	 * @param  {object} $btn  A jQuery dom element object for the remove-row button.
	 * @param  {object} $from A jQuery dom element object for the row being shifted from.
	 * @param  {object} $to   A jQuery dom element object for the row being shifted to.
	 *
	 * @return {void}
	 */
	wysiwyg.shiftStart = function( evt, $btn, $from, $to ) {
		$from.add( $to ).find( '.wp-editor-wrap textarea' ).each( function() {
			wysiwyg.destroy( $( this ).attr( 'id' ) );
		} );
	};

	/**
	 * When a row-shift completes, we need to re-init the wysiwyg editors for the group-rows being shuffled.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} evt   A jQuery-normalized event object.
	 * @param  {object} $btn  A jQuery dom element object for the remove-row button.
	 * @param  {object} $from A jQuery dom element object for the row being shifted from.
	 * @param  {object} $to   A jQuery dom element object for the row being shifted to.
	 *
	 * @return {void}
	 */
	wysiwyg.shiftComplete = function( evt, $btn, $from, $to ) {
		$from.add( $to ).each( function() {
			wysiwyg.initRow( $( this ) );
		} );
	};

	/**
	 * Initializes editors for a new CMB row.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} $row A jQuery dom element object for the group row.
	 *
	 * @return {void}
	 */
	wysiwyg.initRow = function( $row ) {
		var $toReplace, data;

		$row.find( '.cmb2-wysiwyg-inner-wrap' ).each( function() {
			$toReplace    = $( this );
			data          = $toReplace.data();

			data.iterator = $row.data( 'iterator' );
			data.fieldid  = data.id;
			data.id       = data.groupid + '_' + data.iterator + '_' + data.fieldid;
			data.name     = data.groupid + '[' + data.iterator + '][' + data.fieldid + ']';
			data.value    = $toReplace.find( '.wp-editor-area' ).length ? $toReplace.find( '.wp-editor-area' ).val() : '';

			// The destroys might not have happened yet.  Don't init until they have.
			if ( 0 === toBeDestroyed.length ) {

				wysiwyg.init( $toReplace, data );

			} else {
				toBeInitialized.push( [$toReplace, data] );
				window.setTimeout( delayedInit, 100 );
			}
		} );

	};

	/**
	 * Initiates a wysiwyg editor instance and replaces the passed dom element w/ the editor html.
	 *
	 * @since  2.2.3
	 *
	 * @param  {object} $toReplace A jQuery dom element which will be replaced with the wysiwyg editor.
	 * @param  {object} data        Data used to initate the editor.
	 * @param  {bool}   buttonsInit Whether to run QTags._buttonsInit()
	 *
	 * @return {void}
	 */
	wysiwyg.init = function( $toReplace, data, buttonsInit ) {
		if ( ! data.groupid ) {
			return false;
		}

		$.extend( data, getGroupData( data ) );

		initOptions( data );

		$toReplace.replaceWith( data.template( data ) );

		window.tinyMCE.init( tinyMCEPreInit.mceInit[ data.id ] );
		window.quicktags( tinyMCEPreInit.qtInit[ data.id ] );

		$( document.getElementById( data.id ) ).parents( '.wp-editor-wrap' ).removeClass( 'html-active' ).addClass( 'tmce-active' );

		if ( false !== buttonsInit ) {
			window.QTags._buttonsInit();
		}

	};

	/**
	 * Destroys a wysiwyg editor instance.
	 *
	 * @since  2.2.3
	 *
	 * @param  {string} id Editor id.
	 *
	 * @return {void}
	 */
	wysiwyg.destroy = function( id ) {

		// The editor might not be initialized yet.  But we need to destroy it once it is.
		var editor = tinyMCE.get( id );

		if ( editor !== null && typeof( editor ) !== 'undefined' ) {
			editor.destroy();

			if ( 'undefined' === typeof( tinyMCEPreInit.mceInit[ id ] ) ) {
				delete tinyMCEPreInit.mceInit[ id ];
			}

			if ( 'undefined' === typeof( tinyMCEPreInit.qtInit[ id ] ) ) {
				delete tinyMCEPreInit.qtInit[ id ];
			}

		} else if ( -1 === toBeDestroyed.indexOf( id ) ) {
			toBeDestroyed.push( id );
			window.setTimeout( delayedDestroy, 100 );
		}
	};

	// Hook in our event callbacks.
	$( document )
		.on( 'cmb_init', wysiwyg.initAll )
		.on( 'cmb2_add_row', wysiwyg.addRow )
		.on( 'cmb2_remove_group_row_start', wysiwyg.destroyRowEditors )
		.on( 'cmb2_shift_rows_start', wysiwyg.shiftStart )
		.on( 'cmb2_shift_rows_complete', wysiwyg.shiftComplete );

} )( window, document, jQuery, window.CMB2.wysiwyg );
