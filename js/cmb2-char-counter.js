/**
 * Used for character counters
 */
window.CMB2 = window.CMB2 || {};
window.CMB2.charcounter = window.CMB2.charcounter || {};

( function(window, document, $, cmb, charcounter, undefined ) {
	'use strict';


	// Private variables

	var counters = [];


	// Private functions

	/**
	 * Update a field's character counter
	 *
	 * @since  ????
	 *
	 * @param {object} field_id
	 * @return {int}
	 */
	function updateCounter( field_id ) {

		var field   = $( '#' + field_id );
		var val     = field.val().trim();
		var counter = counters[ field_id ];
		var count   = 0;
		var update  = true;

		// Do the count
		switch ( counter.type ) {

			case 'words': {
				count = val ? val.split( /\W+/ ).length : 0;
				break;
			}

			default: {
				count = val.length;
				break;
			}

		}

		// Over maximum?
		if ( typeof counter.max !== 'undefined' && count > counter.max ) {

			// Add max exceeded class to wrap
			counter.el.parents( '.cmb2-char-counter-wrap' ).addClass( 'cmb2-max-exceeded' );

		} else {

			// Remove max exceeded class
			counter.el.parents( '.cmb2-char-counter-wrap' ).removeClass( 'cmb2-max-exceeded' );

		}

		// Update counter
		if ( update ) {

			// Number remaining when max is defined
			if ( typeof counter.max !== 'undefined' ) {
				counter.el.val( ( counter.max - count ) );
			} else {
				counter.el.val( count );
			}

		}

		return count;

	}

	/**
	 * Clean the counters array
	 *
	 * @since  ????
	 *
	 * @return {void}
	 */
	function cleanCounters() {

		// Init
		var key, el, remove = [];

		// Got through counters
		for ( key in counters ) {

			if ( counters.hasOwnProperty( key ) ) {

				// Check for element, gather for removal
				el = $( '#' + key );
				if ( el.length === 0 ) {
					remove.push( key );
				}

			}

		}

		// Anything to remove?
		if ( remove.length ) {
			$.each( remove, function( i, v ) {
				delete counters[ v ];
			});
		}

	}


	/**
	 * Initializes all character counters. Hooked to cmb_init.
	 *
	 * @since  ????
	 *
	 * @param {bool} init First init?
	 *
	 * @return {void}
	 */
	charcounter.initAll = function( init ) {

		// First init?
		init = init || true;

		// Gather counters and initialise
		$( '.cmb2-char-counter' ).each( function() {

			var $this = $( this );

			// Add counter details if not already done
			if ( ! ( $this.data( 'field-id' ) in counters ) ) {

				counters[ $this.data( 'field-id' ) ] = {
					el:   $this,
					type: $this.data( 'counter-type' ),
					max:  $this.data( 'max' )
				};

				// Initialise counter
				updateCounter( $this.data( 'field-id' ), true );

			}

		});

		// Event to update counter on first init
		if ( init ) {

			$( 'body' ).on( 'keyup', '.cmb2-count-chars', function() {

				var $this = $( this );
				updateCounter( $this.attr( 'id' ) );

			});

		}

	};


	/**
	 * Initializes after a new repeatable row has been added. Hooked to cmb2_add_row
	 *
	 * @since  ????
	 *
	 * @param  {object} evt A jQuery-normalized event object.
	 * @param  {object} $row A jQuery dom element object for the group row.
	 *
	 * @return {void}
	 */
	charcounter.addRow = function( evt, $row ) {

		// Character counters in row?
		$row.find( '.cmb2-char-counter' ).each( function() {

			// Update attributes
			var $this    = $( this );
			var id       = $this.attr( 'id' );
			var field_id = id.replace( /^char-counter-/, '' );
			$this.attr( 'name', id ).attr( 'data-field-id', field_id ).data( 'field-id', field_id );

		});

		// Now initialise
		charcounter.initAll( false );

	};


	/**
	 * Removes counters after a repeatable row has been removed. Hooked to cmb2_remove_row.
	 *
	 * @since  ????
	 *
	 * @return {void}
	 */
	charcounter.removeRow = function() {

		cleanCounters();

	};


	// Hook in our event callbacks.
	$( document )
		.on( 'cmb_init', charcounter.initAll )
		.on( 'cmb2_add_row', charcounter.addRow )
		.on( 'cmb2_remove_row', charcounter.removeRow );


} )( window, document, jQuery, window.CMB2, window.CMB2.charcounter );
