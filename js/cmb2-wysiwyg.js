/**
 * Controls the behaviours of repeatable wysiwyg fields.
 *
 * @author WebDevStudios
 * @see    https://github.com/WebDevStudios/CMB2
 */

/**
 * Custom jQuery for WYSIWYG Fields
 */
(function(window, document, $, cmb, undefined){
	'use strict';

	var wysiwygObjects = cmb.wysiwygObjects = {};
	var wysiwyg = cmb.wysiwyg = {

		nameRegex : new RegExp( '\\[placeholder_key\\]', 'g' ),

		replace : function( str ) {
			return str.replace( this.idRegex, this.idAttr ).replace( this.nameRegex, '[' + this.iterator + ']' );
		},

		reInitEditor : function( globalObject ) {
			/*if ( 'undefined' !== typeof( globalObject[ this.idAttr ] ) ) {
				return;
			}*/

			// If no settings for this field. Clone from placeholder.
			var newSettings = jQuery.extend( {}, globalObject[ this.templateId ] );
				console.log('newSettings', newSettings);
			var prop;

			for ( prop in newSettings ) {
				if ( 'string' === typeof( newSettings[ prop ] ) ) {
					newSettings[ prop ] = this.replace( newSettings[ prop ] );
				}
			}

			globalObject[ this.idAttr ] = newSettings;

			return globalObject[ this.idAttr ];
		},

		getMode: function() {
			return this.$field.find('.wp-editor-wrap').hasClass( 'tmce-active' ) ? 'tmce' : 'html';
		},

		replaceHtml: function() {
			// Replace id instances and update field html
			this.$field.find( '.cmb-td' ).first().html( this.replace( this.$template.html() ) );
		},

	};

	cmb.wysiwygObject = function( $field, iterator ) {

		wysiwyg.$field  = $field;
		wysiwyg.$editor = $field.find( '.wp-editor-area' );
		wysiwyg.idAttr  = wysiwyg.$editor.attr( 'id' );

		if ( wysiwygObjects[ wysiwyg.idAttr ] ) {
			return wysiwygObjects[ wysiwyg.idAttr ];
		}

		wysiwyg.nameAttr   = wysiwyg.$editor.attr( 'name' );
		// console.log( 'wysiwyg.idAttr', wysiwyg.idAttr );
		// console.log( 'iterator', iterator );
		wysiwyg.iterator   = iterator;
		wysiwyg.template   = wysiwyg.template ? wysiwyg.template : wysiwyg.idAttr.replace( '_'+ iterator, '' );
		// console.log( 'wysiwyg.template', wysiwyg.template );
		wysiwyg.templateId = wysiwyg.templateId ? wysiwyg.templateId : wysiwyg.template + '_placeholder';
		// console.log( 'wysiwyg.templateId', wysiwyg.templateId );
		wysiwyg.$template  = wysiwyg.$template ? wysiwyg.$template : cmb.$id( wysiwyg.template + '_template' );
		// console.log( 'wysiwyg.$template', wysiwyg.$template.length );
		wysiwyg.idRegex    = wysiwyg.idRegex ? wysiwyg.idRegex : new RegExp( wysiwyg.templateId, 'g' );

		wysiwygObjects[ wysiwyg.idAttr ] = wysiwyg;

		console.log( 'wysiwyg', wysiwyg );
		return wysiwyg;
	};

	cmb.initWysiwyg = function( field ) {
		/**
		 * @todo when adding a group then removing, then adding, the wysiwyg editor is not working
		 */
		wysiwyg = cmb.wysiwygObject( $( field ), cmb.idNumber );

		if ( window.tinyMCE.get( wysiwyg.idAttr ) ) {
			return;
		}

		// Update field html
		wysiwyg.replaceHtml();

		// Re-initiate the editor instances for this
		var mceEditor = wysiwyg.reInitEditor( tinyMCEPreInit.mceInit );
		var qtEditor  = wysiwyg.reInitEditor( tinyMCEPreInit.qtInit );

		// If current mode is visual, create the tinyMCE.
		if ( 'tmce' === wysiwyg.getMode() ) {
			var editor;

			if ( '4' === window.tinyMCE.majorVersion ) {
				editor = window.tinyMCE.init( mceEditor );
			} else if ( '3' === window.tinyMCE.majorVersion ) {
				editor = new window.tinyMCE.Editor( wysiwyg.idAttr, mceEditor );
				editor.render();
			}
		}

		// Init Quicktags.
		window.QTags.instances[0] = undefined;

		try {
			window.quicktags( qtEditor );
		} catch( err ){
			console.log( 'QT-err', err );
		}

	};

	cmb.handleRowAddWysiwyg = function( ) {
		cmb.addDataAttribute();

		console.log( 'handleRowAddWysiwyg' );
		var $wysiwyg = $( this ).find( '.cmb-repeat-row' ).last();
		// console.log( '$wysiwyg', $wysiwyg.find( '.wp-editor-area' ).attr( 'name' ) );

		var name = $wysiwyg.find( '.wp-editor-area' ).attr( 'name' );
		var iterator = parseInt( name.substr( name.indexOf( '[' ) + 1 ).replace( ']', '' ) );

		wysiwyg = cmb.wysiwygObject( $wysiwyg, iterator );

		if ( window.tinyMCE.get( wysiwyg.idAttr ) ) {
			console.warn('tinyMCE.get( wysiwyg.idAttr exists');
			return;
		}

		// Update field html
		wysiwyg.replaceHtml();

		/*if ( 1===1 ) {
			return;
		}*/

		console.log('tinyMCEPreInit.mceInit', tinyMCEPreInit.mceInit);
		console.log('tinyMCEPreInit.qtInit', tinyMCEPreInit.qtInit);

		// Re-initiate the editor instances for this
		var mceEditor = wysiwyg.reInitEditor( tinyMCEPreInit.mceInit );
		var qtEditor  = wysiwyg.reInitEditor( tinyMCEPreInit.qtInit );

		// If current mode is visual, create the tinyMCE.
		if ( 'tmce' === wysiwyg.getMode() ) {
			var editor;

			if ( '4' === window.tinyMCE.majorVersion ) {
				editor = window.tinyMCE.init( mceEditor );
			} else if ( '3' === window.tinyMCE.majorVersion ) {
				editor = new window.tinyMCE.Editor( wysiwyg.idAttr, mceEditor );
				editor.render();
			}
		}


		// Init Quicktags.
		window.QTags.instances[0] = undefined;

		try {
			console.log('qtEditor', qtEditor);
			window.quicktags( qtEditor );
		} catch( err ){
			console.log( 'QT-err', err );
		}

	};

	cmb.handleGroupAddWysiwyg = function( evt, newRow ) {
		var $wysiwygs = $( newRow ).find( '.cmb-type-wysiwyg' );

		if ( $wysiwygs.length ) {
			$wysiwygs.each( function() { cmb.initWysiwyg( this ); });
		}

	};

	cmb.moveWysiwygTemplates = function() {
		$( '.template-wysiwyg-placeholder' ).each( function() {
			var $this = $( this );
			var $parents = $this.parents( '.cmb-repeatable-group' );
			$parents = $parents.length ? $parents : $this.parents( '.cmb-type-wysiwyg' );

			$parents.append( $this );
		});
	};

	cmb.addDataAttribute = function() {
		// Set data-iterator attributes
		return $( '.cmb-repeat-table' ).each( function() {
			$( this ).find( '.wp-editor-area' ).each( function( index ) {
				$( this ).attr( 'data-iterator', index );
			});
		});
	};

	cmb.wysiwygInit = function() {

		cmb.moveWysiwygTemplates();

		$( '.cmb-repeatable-group' ).on( 'cmb2_add_row', cmb.handleGroupAddWysiwyg );
		cmb.addDataAttribute().on( 'cmb2_add_row', cmb.handleRowAddWysiwyg );

		// @TODO handle sortable

	};

	$(document).ready( cmb.wysiwygInit );

})(window, document, jQuery, window.CMB2 );
