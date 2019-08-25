/**
 * Used for character counters
 */
window.CMB2 = window.CMB2 || {};
window.CMB2.charcounter = window.CMB2.charcounter || {};

( function(window, document, $, cmb, counter ) {
	'use strict';

	if ( ! wp.utils || ! wp.utils.WordCounter ) {
		return cmb.log( 'Cannot find wp.utils!' );
	}

	// Private variables
	counter.counters = {};
	var counters     = counter.counters;
	var wpCounter    = new wp.utils.WordCounter();

	/**
	 * Update a field's character counter
	 *
	 * @since  2.7.0
	 *
	 * @param {string} field_id
	 *
	 * @return {int}
	 */
	counter.updateCounter = function( field_id ) {
		// No counter?
		if ( ! counters.hasOwnProperty( field_id ) ) {
			return null;
		}

		var instance = counters[ field_id ];
		var wysiwyg  = instance.editor && ! instance.editor.isHidden();

		// Are we dealing with WYSIWYG visual editor, or textarea / WYSIWYG textarea?
		var text     = wysiwyg ? instance.editor.getContent( { format: 'raw' } ) : cmb.$id( field_id ).val().trim();
		var count    = wpCounter.count( text, instance.type );
		var exceeded = instance.max && count > instance.max;

		// Number remaining when max is defined
		var val      = instance.max ? instance.max - count : count;

		// Over maximum?
		instance.$el.parents( '.cmb2-char-counter-wrap' )[ exceeded ? 'addClass' : 'removeClass' ]( 'cmb2-max-exceeded' );

		// Update counter, and update counter input width.
		instance.$el.val( val ).outerWidth( ( ( 8 * String( val ).length ) + 15 ) + 'px' );

		return count;
	};

	counter.instantiate = function( $el ) {
		var data = $el.data();

		// Add counter details if not already done
		if ( ! ( data.fieldId in counters ) ) {

			var instance = {
				$el    : $el,
				max    : data.max,
				type   : 'words' === data.counterType ? 'words' : 'characters_including_spaces',
				editor : false,
			};

			counters[ data.fieldId ] = instance;

			// Initialise counter
			counter.updateCounter( data.fieldId );
		}
	};

	/**
	 * Initializes all character counters. Hooked to cmb_init.
	 *
	 * @since  2.7.0
	 *
	 * @param {bool} init First init?
	 *
	 * @return {void}
	 */
	counter.initAll = function() {

		// Gather counters and initialise
		$( '.cmb2-char-counter' ).each( function() {
			counter.instantiate( $( this ) );
		});
	};

	/**
	 * Initializes WYSIWYG editors. Hooked to tinymce-editor-init
	 *
	 * @since  2.7.0
	 *
	 * @param {object} evt
	 * @param {object} editor
	 *
	 * @return {void}
	 */
	counter.initWysiwyg = function( evt, editor ) {

		// Check if it's one of our WYSIWYGs
		// Should have already been registered in counters via hidden textarea
		if ( editor.id in counters ) {

			// Add editor to counter
			counters[ editor.id ].editor = editor;

			// Add nodechange event
			editor.on( 'nodechange keyup', counter.countWysiwyg );
		}
	};

	/**
	 * Initializes after a new repeatable row has been added. Hooked to cmb2_add_row
	 *
	 * @since  2.7.0
	 *
	 * @param  {object} evt A jQuery-normalized event object.
	 * @param  {object} $row A jQuery dom element object for the group row.
	 *
	 * @return {void}
	 */
	counter.addRow = function( evt, $row ) {

		// Character counters in row?
		$row.find( '.cmb2-char-counter' ).each( function() {

			// Update attributes
			var $this    = $( this );
			var id       = $this.attr( 'id' );
			var field_id = id.replace( /^char-counter-/, '' );
			$this.attr( 'data-field-id', field_id ).data( 'field-id', field_id );

			counter.instantiate( $this );
		});
	};

	/**
	 * Clean the counters array.
	 * Removes counters after a repeatable row has been removed. Hooked to cmb2_remove_row.
	 *
	 * @since  2.7.0
	 *
	 * @return {void}
	 */
	counter.cleanCounters = function() {
		var field_id, remove = [];

		// Got through counters
		for ( field_id in counters ) {
			// Check for element, gather for removal
			if ( ! document.getElementById( field_id ) ) {
				remove.push( field_id );
			}
		}

		// Anything to remove?
		if ( remove.length ) {
			_.each( remove, function( field_id ) {
				delete counters[ field_id ];
			});
		}
	};

	/**
	 * Counts the value of wysiwyg on the keyup event.
	 *
	 * @since  2.7.0
	 *
	 * @param  {object} evt
	 *
	 * @return {void}
	 */
	counter.countWysiwyg = _.throttle( function( evt ) {

		// Init event
		if ( evt.hasOwnProperty( 'element' ) ) {
			return counter.updateCounter( $( evt.element ).data( 'id' ) );
		}

		// Nodechange event
		if ( evt.hasOwnProperty( 'currentTarget' ) ) {
			return counter.updateCounter( $( evt.currentTarget ).data( 'id' ) );
		}

	} );

	/**
	 * Counts the value of textarea on the keyup event.
	 *
	 * @since  2.7.0
	 *
	 * @param  {object} evt
	 *
	 * @return {void}
	 */
	counter.countTextarea = _.throttle( function(evt) {
		counter.updateCounter( evt.currentTarget.id );
	}, 400 );

	// Hook in our event callbacks.
	$( document )
		.on( 'cmb_init', counter.initAll )
		.on( 'tinymce-editor-init', counter.initWysiwyg )
		.on( 'cmb2_add_row', counter.addRow )
		.on( 'cmb2_remove_row', counter.cleanCounters )
		.on( 'input keyup', '.cmb2-count-chars', counter.countTextarea );

} )( window, document, jQuery, window.CMB2, window.CMB2.charcounter );
