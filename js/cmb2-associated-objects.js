
window.CMB2 = window.CMB2 || {};
window.CMB2.associated = window.CMB2.associated || {};

( function( window, document, $, app, undefined ) {

	var l10n = window.cmb2_l10;

	app.$ = {};

	app.cache = function() {
		var $wrap            = $( '.associated-objects-wrap' );
		app.$.retrievedPosts = $wrap.find( '.retrieved' );
		app.$.attachedPosts  = $wrap.find( '.attached' );
		app.doType           = $wrap.find( '.object-label' ).length;
	};

	app.init = function() {
		app.cache();

		// Allow the user to drag items from the left list
		app.makeDraggable();

		// Allow the right list to be droppable and sortable
		app.makeDroppable();

		$( '.cmb2-wrap > .cmb2-metabox' )
			// Add posts when the plus icon is clicked
			.on( 'click', '.associated-objects-wrap .retrieved .add-remove', app._moveRowToAttached )
			// Remove posts when the minus icon is clicked
			.on( 'click', '.associated-objects-wrap .attached .add-remove', app._removeRowFromAttached )
			// Listen for search events
			.on( 'keyup', '.associated-objects-wrap input.search', app._handleFilter )
			.on( 'click', '.cmb2-associated-objects-search-button', app._openSearch );

		$( document.body ).on( 'click', '.ui-find-overlay', app.closeSearch );
	};

	app.makeDraggable = function() {
		// Allow the user to drag items from the left list
		app.$retrievedPosts().draggable({
			helper: 'clone',
			revert: 'invalid',
			stack: '.retrieved li',
			stop: app.replacePlusIcon,
		});
	};

	app.makeDroppable = function() {
		app.$.attachedPosts.droppable({
			accept: '.retrieved li',
			drop: function(evt, ui) {
				app.buildItems( ui.draggable );
			}
		}).sortable({
			stop: function( evt, ui ) {
				app.resetItems( ui.item );
			}
		}).disableSelection();
	};

	// Clone our dragged item
	app.buildItems = function( item ) {
		var $wrap  = $( item ).parents( '.associated-objects-wrap' );
		// Get the ID of the item being dragged
		var itemID = item[0].attributes[0].value;

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class to our retrieved column when clicked
		$wrap.find( '.retrieved li[data-id="'+ itemID +'"]' ).addClass( 'added' );

		item.clone().appendTo( $wrap.find( '.attached' ) );

		app.resetAttachedListItems( $wrap );
	};

	// Add the items when the plus icon is clicked
	app._moveRowToAttached = function() {
		app.moveRowToAttached( $( this ).parent() );
	};

	// Move Post to Attached column.
	app.moveRowToAttached = function( $li ) {
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.associated-objects-wrap' );

		if ( $li.hasClass( 'added' ) ) {
			return;
		}

		// If our item is in our post ID array, stop
		if ( app.inputHasId( $wrap, itemID ) ) {
			return;
		}

		// Add the 'added' class when clicked
		$li.addClass( 'added' );

		// Add the item to the right list
		$wrap.find( '.attached' ).append( $li.clone() );

		app.resetAttachedListItems( $wrap );
	};

	// Remove items from our attached list when the minus icon is clicked
	app._removeRowFromAttached = function() {
		// Get the clicked item's ID
		app.removeRowFromAttached( $(this).closest( 'li' ) );
	};

	// Remove items from our attached list when the minus icon is clicked
	app.removeRowFromAttached = function( $li ) {
		var itemID = $li.data( 'id' );
		var $wrap  = $li.parents( '.associated-objects-wrap' );

		// Remove the list item
		$li.remove();

		// Remove the 'added' class from the retrieved column
		$wrap.find('.retrieved li[data-id="' + itemID +'"]').removeClass('added');

		app.resetAttachedListItems( $wrap );
	};

	app.inputHasId = function( $wrap, itemID ) {
		var $input  = app.getPostIdsInput( $wrap );
		// Get array
		var postIds = app.getPostIdsVal( $input );
		// If our item is in our post ID array, stop everything
		return $.inArray( itemID, postIds) !== -1;
	};

	app.getPostIdsInput = function( $wrap ) {
		return $wrap.find('.associated-objects-ids');
	};

	app.getPostIdsVal = function( $input ) {
		var val = $input.val();
		return val ? val.split( ',' ) : [];
	};

	app.resetAttachedListItems = function( $wrap ) {
		var $input = app.getPostIdsInput( $wrap );
		var newVal = [];

		$wrap.find( '.attached li' ).each( function( index ) {
			var zebraClass = 0 === index % 2 ? 'odd' : 'even';
			newVal.push( $(this).attr( 'class', zebraClass + ' ui-sortable-handle' ).data( 'id' ) );
		});

		// Replace the plus icon with a minus icon in the attached column
		app.replacePlusIcon();

		$input.val( newVal.join( ',' ) );
	};

	// Re-order items when items are dragged
	app.resetItems = function( item ) {
		var $li = $( item );
		app.resetAttachedListItems( $li.parents( '.associated-objects-wrap' ) );
	};

	// Replace the plus icon in the attached posts column
	app.replacePlusIcon = function() {
		$( '.attached li .dashicons.dashicons-plus' ).removeClass( 'dashicons-plus' ).addClass( 'dashicons-minus' );
	};

	// Handle searching available list
	app._handleFilter = function( evt ) {
		var $this = $( evt.target );
		app.handleFilter( $this.val() || '', $this.closest( '.column-wrap' ) );
	};

	// Handle searching available list
	app.handleFilter = function( term, $column ) {
		term = term ? term.toLowerCase() : '';

		$column.find( 'ul.connected li' ).each( function() {
			var $el = $(this);

			if ( $el.text().toLowerCase().search( term ) > -1 ) {
				$el.show();
			} else {
				$el.hide();
			}
		} );
	};

	app.rowTmpl = function( row ) {
		row.type = app.doType ? ' &mdash; <span class="object-label">'+ row.type +'</span>' : '';
		return '<li data-id="'+ row.id +'" class="'+ row.class +' ui-draggable ui-draggable-handle"><a title="'+ app.editTitle +'" href="'+ row.linkTmpl.replace( 'REPLACEME', row.id ) +'" target="_blank">'+ row.title +'</a>'+ row.type +'<span class="dashicons dashicons-plus add-remove"></span></li>';
	};

	app.$retrievedPosts = function() {
		return app.$.retrievedPosts.find( 'li' );
	};

	app.$lastRow = function() {
		var $lastRow = app.$retrievedPosts().last();

		if ( ! app.editTitle ) {
			app.editTitle = $lastRow.find( 'a' ).attr( 'title' );
		}

		return $lastRow;
	};

	app.SearchView = window.Backbone.View.extend({
		el         : '#find-posts',
		overlaySet : false,
		$overlay   : false,
		$button    : false,

		events : {
			'keypress .find-box-search:input' : 'maybeStartSearch',
			'keyup #find-posts-input'  : 'escClose',
			'click #find-posts-submit' : 'selectPost',
			'click #find-posts-search' : 'send',
			'click #find-posts-close'  : 'close',
		},

		initialize: function() {
			this.$spinner  = this.$el.find( '.find-box-search .spinner' );
			this.$input    = this.$el.find( '#find-posts-input' );
			this.$response = this.$el.find( '#find-posts-response' );
			this.$overlay  = $( '.ui-find-overlay' );

			this.listenTo( this, 'open', this.open );
			this.listenTo( this, 'close', this.close );
		},

		escClose: function( evt ) {
			if ( evt.which && 27 === evt.which ) {
				this.close();
			}
		},

		close: function() {
			this.$overlay.hide();
			this.$el.hide();
		},

		open: function() {
			this.$response.html('');

			// WP, why you so dumb? (why isn't text in its own dom node?)
			this.$el.show().find( '#find-posts-head' ).html( this.findtxt + '<div id="find-posts-close"></div>' );

			this.$input.focus();

			if ( ! this.$overlay.length ) {
				$( 'body' ).append( '<div class="ui-find-overlay"></div>' );
				this.$overlay  = $( '.ui-find-overlay' );
			}

			this.$overlay.show();

			// Pull some results up by default
			this.send();

			return false;
		},

		maybeStartSearch: function( evt ) {
			if ( 13 === evt.which ) {
				this.send();
				return false;
			}
		},

		send: function() {
			this.$spinner.addClass( 'is-active' );

			var retrieved = app.$retrievedPosts().map( function() {
				return $( this ).data( 'id' );
			} ).get();

			var data = {
				ps                   : this.$input.val(),
				action               : 'find_posts',
				query_object_type    : this.queryObjectType,
				search_types         : this.types,
				cmb_id               : this.cmbId,
				group_id             : this.groupId,
				field_id             : this.fieldId,
				exclude              : this.exclude,
				retrieved            : retrieved,
				_ajax_nonce          : $( '#find-posts #_ajax_nonce' ).val(),
				cmb2_attached_search : true,
			};

			$.post( l10n.ajaxurl, data )
				.always( this.hideSpinner.bind( this ) )
				.done( this.ajaxSuccess.bind( this ) )
				.fail( this.ajaxFail.bind( this ) );
		},

		hideSpinner: function() {
			this.$spinner.removeClass( 'is-active' );
		},

		ajaxSuccess: function( response ) {
			if ( ! response.success ) {
				this.$response.text( this.errortxt );
			}

			var data = response.data.replace( /type="radio"/gi, 'type="checkbox"' );

			this.$response.html( data );
		},

		ajaxFail: function() {
			this.$response.text( this.errortxt );
		},

		selectPost: function( evt ) {
			evt.preventDefault();

			var html = '';
			// var posts = [];
			var $checked = this.$response.find( 'input[type="checkbox"]:checked' );

			if ( ! $checked.length ) {
				this.close();
				return;
			}

			var $lastRow  = app.$lastRow();
			var nextClass = $lastRow.hasClass( 'even' ) ? 'odd' : 'even';
			var linkTmpl  = this.linkTmpl;
			var ids       = [];

			$checked.each( function() {
				ids.push( this.value );

				var $row = $( this ).parents( '.found-posts' );
				html += app.rowTmpl( {
					title    : $row.find( 'label' ).html(),
					type     : $row.find( '> td' ).eq( 2 ).text(),
					id       : this.value,
					linkTmpl : linkTmpl,
					class    : nextClass
				} );

				nextClass = 'even' === nextClass ? 'odd' : 'even';
			} );

			if ( html ) {
				$lastRow.after( html );
				app.makeDraggable();

				this.moveInserted( ids );
			}

			this.close();
		},

		moveInserted: function( ids ) {
			// This delay is only for dramatic effect,
			// as otherwise it appears nothing happened.
			window.setTimeout( function() {
				for ( var i = 0; i <= ids.length; i++ ) {
					app.moveRowToAttached( app.$retrievedPosts().filter( '[data-id="'+ ids[i] +'"]' ) );
				}
			}, 500 );
		},


	});

	app.search = new app.SearchView();

	app.closeSearch = function() {
		app.search.trigger( 'close' );
	};

	app._openSearch = function( evt ) {
		app.openSearch( $( evt.currentTarget ) );
	};

	app.openSearch = function( $button ) {
		app.search.$button = $button;

		// Setup our variables from the field data
		$.extend( app.search, app.search.$button.data( 'search' ) );

		app.search.trigger( 'open' );
	};

	$( app.init );

} )( window, document, jQuery, window.CMB2.associated );
