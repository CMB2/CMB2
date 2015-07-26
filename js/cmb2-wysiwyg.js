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

	var $groups = $( '.cmb-repeatable-group' );
	var wysiwygObjects = cmb.wysiwygObjects = {};
	var wysiwyg = cmb.wysiwyg = {

		nameRegex : new RegExp( '\\[placeholder_key\\]', 'g' ),

		replace : function( str ) {
			return str.replace( this.idRegex, this.idAttr ).replace( this.nameRegex, '[' + cmb.idNumber + ']' );
		},

		reInitEditor : function( globalObject ) {
			if ( 'undefined' !== typeof( globalObject[ this.idAttr ] ) ) {
				return;
			}

			// If no settings for this field. Clone from placeholder.
			var newSettings = jQuery.extend( {}, globalObject[ this.templateId ] );
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
			this.$field.find( '.cmb-td' ).html( this.replace( this.$template.html() ) );
		},

	};

	cmb.wysiwygObject = function( $field ) {

		wysiwyg.$field  = $field;
		wysiwyg.$editor = $field.find( '.wp-editor-area' );
		wysiwyg.idAttr  = wysiwyg.$editor.attr( 'id' );

		if ( wysiwygObjects[ wysiwyg.idAttr ] ) {
			return wysiwygObjects[ wysiwyg.idAttr ];
		}

		wysiwyg.nameAttr   = wysiwyg.$editor.attr( 'name' );
		wysiwyg.template   = wysiwyg.idAttr.replace( '_'+ cmb.idNumber, '' );
		wysiwyg.templateId = wysiwyg.template + '_placeholder';
		wysiwyg.$template  = cmb.$id( wysiwyg.template + '_template' );
		wysiwyg.idRegex    = new RegExp( wysiwyg.templateId, 'g' );

		wysiwygObjects[ wysiwyg.idAttr ] = wysiwyg;

		return wysiwyg;
	};

	cmb.handleGroupAddWysiwyg = function( evt, newRow ) {
		var $wysiwygs = $( newRow ).find( '.cmb-type-wysiwyg' );

		if ( ! $wysiwygs.length ) {
			return;
		}

		// wysiwyg fields
		$wysiwygs.each( function(){
			wysiwyg = cmb.wysiwygObject( $( this ) );

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

		});

	};

	cmb.moveWysiwygTemplates = function() {
		$( '.template-wysiwyg-placeholder' ).each( function() {
			var $this = $( this );
			$this.parents( '.cmb-repeatable-group' ).append( $this );
		});
	};

	cmb.wysiwygInit = function() {

		cmb.moveWysiwygTemplates();
		$groups.on( 'cmb2_add_row', cmb.handleGroupAddWysiwyg );

	};

	$(document).ready( cmb.wysiwygInit );

})(window, document, jQuery, window.CMB2 );
