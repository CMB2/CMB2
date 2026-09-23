/**!
 * wp-color-picker-alpha
 *
 * Overwrite Automattic Iris for enabled Alpha Channel in wpColorPicker
 * Only run in input and is defined data alpha in true
 *
 * Version: 3.0.4
 * https://github.com/kallookoo/wp-color-picker-alpha
 * Licensed under the GPLv2 license or later.
 */

( function ( $, undef ) {

	var wpColorPickerAlpha = {
		'version': 304
	};

	// Always try to use the last version of this script.
	if ( 'wpColorPickerAlpha' in window && 'version' in window.wpColorPickerAlpha ) {
		var version = parseInt( window.wpColorPickerAlpha.version, 10 );
		if ( !isNaN( version ) && version >= wpColorPickerAlpha.version ) {
			return;
		}
	}

	// Prevent multiple initiations
	if ( Color.fn.hasOwnProperty( 'to_s' ) ) {
		return;
	}

	// Create new method to replace the `Color.toString()` inside the scripts.
	Color.fn.to_s = function ( type ) {
		if ( this.error ) {
			return '';
		}
		type = ( type || 'hex' );
		// Change hex to rgba to return the correct color.
		if ( 'hex' === type && this._alpha < 1 ) {
			type = 'rgba';
		}

		var color = '';
		if ( 'hex' === type ) {
			color = this.toString();
		} else if ( 'octohex' === type ) {
			color = this.toString();
			var alpha = parseInt( 255 * this._alpha, 10 ).toString( 16 );
			if ( alpha.length === 1 ) {
				alpha = `0${alpha}`;
			}
			color += alpha;
		} else {
			color = this.toCSS( type ).replace( /\(\s+/, '(' ).replace( /\s+\)/, ')' );
		}

		return color;
	}

	Color.fn.fromHex = function ( color ) {
		color = color.replace( /^#/, '' ).replace( /^0x/, '' );
		if ( 3 === color.length || 4 === color.length ) {
			var extendedColor = '';
			for ( var index = 0; index < color.length; index++ ) {
				extendedColor += '' + color[ index ];
				extendedColor += '' + color[ index ];
			}
			color = extendedColor;
		}

		if ( color.length === 8 ) {
			if ( /^[0-9A-F]{8}$/i.test( color ) ) {
				var alpha = parseInt( color.substring( 6 ), 16 );
				if ( !isNaN( alpha ) ) {
					this.a( alpha / 255 );
				} else {
					this._error();
				}
			} else {
				this._error();
			}
			color = color.substring( 0, 6 );
		}

		if ( !this.error ) {
			this.error = ! /^[0-9A-F]{6}$/i.test( color );
		}

		// console.log(color + ': ' + this.a())
		return this.fromInt( parseInt( color, 16 ) );
	}

	// Register the global variable.
	window.wpColorPickerAlpha = wpColorPickerAlpha;

	// Background image encoded
	var backgroundImage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAAHnlligAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHJJREFUeNpi+P///4EDBxiAGMgCCCAGFB5AADGCRBgYDh48CCRZIJS9vT2QBAggFBkmBiSAogxFBiCAoHogAKIKAlBUYTELAiAmEtABEECk20G6BOmuIl0CIMBQ/IEMkO0myiSSraaaBhZcbkUOs0HuBwDplz5uFJ3Z4gAAAABJRU5ErkJggg==';

	/**
	 * Iris
	 */
	$.widget( 'a8c.iris', $.a8c.iris, {
		/**
		 * Alpha options
		 *
		 * @since 3.0.0
		 *
		 * @type {Object}
		 */
		alphaOptions: {
			alphaEnabled: false,
		},
		/**
		 * Get the current color or the new color.
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @param {Object|*} The color instance if not defined return the current color.
		 *
		 * @return {string} The element's color.
		 */
		_getColor: function ( color ) {
			if ( color === undef ) {
				color = this._color;
			}

			if ( this.alphaOptions.alphaEnabled ) {
				color = color.to_s( this.alphaOptions.alphaColorType );
				if ( !this.alphaOptions.alphaColorWithSpace ) {
					color = color.replace( /\s+/g, '' );
				}
				return color;
			}
			return color.toString();
		},
		/**
		 * Create widget
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_create: function () {
			try {
				// Try to get the wpColorPicker alpha options.
				this.alphaOptions = this.element.wpColorPicker( 'instance' ).alphaOptions;
			} catch ( e ) { }

			// We make sure there are all options
			$.extend( {}, this.alphaOptions, {
				alphaEnabled: false,
				alphaCustomWidth: 130,
				alphaReset: false,
				alphaColorType: 'hex',
				alphaColorWithSpace: false,
				alphaSkipDebounce: false,
				alphaDebounceTimeout: 100,
			} );

			this._super();
		},
		/**
		 * Binds event listeners to the Iris.
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_addInputListeners: function ( input ) {
			var self = this,
				callback = function ( event ) {
					var val = input.val(),
						color = new Color( val ),
						val = val.replace( /^(#|(rgb|hsl)a?)/, '' ),
						type = self.alphaOptions.alphaColorType;

					input.removeClass( 'iris-error' );

					if ( !color.error ) {
						// let's not do this on keyup for hex shortcodes
						if ( 'hex' !== type || !( event.type === 'keyup' && val.match( /^[0-9a-fA-F]{3}$/ ) ) ) {
							// Compare color ( #AARRGGBB )
							if ( color.toIEOctoHex() !== self._color.toIEOctoHex() ) {
								self._setOption( 'color', self._getColor( color ) );
							}
						}
					} else if ( val !== '' ) {
						input.addClass( 'iris-error' );
					}
				};

			input.on( 'change', callback );

			if ( !self.alphaOptions.alphaSkipDebounce ) {
				input.on( 'keyup', self._debounce( callback, self.alphaOptions.alphaDebounceTimeout ) );
			}

			// If we initialized hidden, show on first focus. The rest is up to you.
			if ( self.options.hide ) {
				input.one( 'focus', function () {
					self.show();
				} );
			}
		},
		/**
		 * Init Controls
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_initControls: function () {
			this._super();

			if ( this.alphaOptions.alphaEnabled ) {
				// Create Alpha controls
				var self = this,
					stripAlpha = self.controls.strip.clone( false, false ),
					stripAlphaSlider = stripAlpha.find( '.iris-slider-offset' ),
					controls = {
						stripAlpha: stripAlpha,
						stripAlphaSlider: stripAlphaSlider
					};

				stripAlpha.addClass( 'iris-strip-alpha' );
				stripAlphaSlider.addClass( 'iris-slider-offset-alpha' );
				stripAlpha.appendTo( self.picker.find( '.iris-picker-inner' ) );

				// Push new controls
				$.each( controls, function ( k, v ) {
					self.controls[ k ] = v;
				} );

				// Create slider
				self.controls.stripAlphaSlider.slider( {
					orientation: 'vertical',
					min: 0,
					max: 100,
					step: 1,
					value: parseInt( self._color._alpha * 100 ),
					slide: function ( event, ui ) {
						self.active = 'strip';
						// Update alpha value
						self._color._alpha = parseFloat( ui.value / 100 );
						self._change.apply( self, arguments );
					}
				} );
			}
		},
		/**
		 * Create the controls sizes
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @param {bool} reset Set to True for recreate the controls sizes.
		 *
		 * @return {void}
		 */
		_dimensions: function ( reset ) {
			this._super( reset );

			if ( this.alphaOptions.alphaEnabled ) {
				var self = this,
					opts = self.options,
					controls = self.controls,
					square = controls.square,
					strip = self.picker.find( '.iris-strip' ),
					innerWidth, squareWidth, stripWidth, stripMargin, totalWidth;

				/**
				 * I use Math.round() to avoid possible size errors,
				 * this function returns the value of a number rounded
				 * to the nearest integer.
				 *
				 * The width to append all widgets,
				 * if border is enabled, 22 is subtracted.
				 * 20 for css left and right property
				 * 2 for css border
				 */
				innerWidth = Math.round( self.picker.outerWidth( true ) - ( opts.border ? 22 : 0 ) );
				// The width of the draggable, aka square.
				squareWidth = Math.round( square.outerWidth() );
				// The width for the sliders
				stripWidth = Math.round( ( innerWidth - squareWidth ) / 2 );
				// The margin for the sliders
				stripMargin = Math.round( stripWidth / 2 );
				// The total width of the elements.
				totalWidth = Math.round( squareWidth + ( stripWidth * 2 ) + ( stripMargin * 2 ) );

				// Check and change if necessary.
				while ( totalWidth > innerWidth ) {
					stripWidth = Math.round( stripWidth - 2 );
					stripMargin = Math.round( stripMargin - 1 );
					totalWidth = Math.round( squareWidth + ( stripWidth * 2 ) + ( stripMargin * 2 ) );
				}

				square.css( 'margin', '0' );
				strip.width( stripWidth ).css( 'margin-left', stripMargin + 'px' );
			}
		},
		/**
		 * Callback to update the controls and the current color.
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_change: function () {
			var self = this,
				active = self.active;

			self._super();

			if ( self.alphaOptions.alphaEnabled ) {
				var controls = self.controls,
					alpha = parseInt( self._color._alpha * 100 ),
					color = self._color.toRgb(),
					gradient = [
						'rgb(' + color.r + ',' + color.g + ',' + color.b + ') 0%',
						'rgba(' + color.r + ',' + color.g + ',' + color.b + ', 0) 100%'
					],
					target = self.picker.closest( '.wp-picker-container' ).find( '.wp-color-result' );

				self.options.color = self._getColor();
				// Generate background slider alpha, only for CSS3.
				controls.stripAlpha.css( { 'background': 'linear-gradient(to bottom, ' + gradient.join( ', ' ) + '), url(' + backgroundImage + ')' } );
				// Update alpha value
				if ( active ) {
					controls.stripAlphaSlider.slider( 'value', alpha );
				}

				if ( !self._color.error ) {
					self.element.removeClass( 'iris-error' ).val( self.options.color );
				}

				self.picker.find( '.iris-palette-container' ).on( 'click.palette', '.iris-palette', function () {
					var color = $( this ).data( 'color' );
					if ( self.alphaOptions.alphaReset ) {
						self._color._alpha = 1;
						color = self._getColor();
					}
					self._setOption( 'color', color );
				} );
			}
		},
		/**
		 * Paint dimensions.
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @param {string} origin  Origin (position).
		 * @param {string} control Type of the control,
		 *
		 * @return {void}
		 */
		_paintDimension: function ( origin, control ) {
			var self = this,
				color = false;

			// Fix for slider hue opacity.
			if ( self.alphaOptions.alphaEnabled && 'strip' === control ) {
				color = self._color;
				self._color = new Color( color.toString() );
				self.hue = self._color.h();
			}

			self._super( origin, control );

			// Restore the color after paint.
			if ( color ) {
				self._color = color;
			}
		},
		/**
		 * To update the options, see original source to view the available options.
		 *
		 * @since 3.0.0
		 *
		 * @param {string} key   The Option name.
		 * @param {mixed} value  The Option value to update.
		 *
		 * @return {void}
		 */
		_setOption: function ( key, value ) {
			var self = this;
			if ( 'color' === key && self.alphaOptions.alphaEnabled ) {
				// cast to string in case we have a number
				value = '' + value;
				newColor = new Color( value ).setHSpace( self.options.mode );
				// Check if error && Check the color to prevent callbacks with the same color.
				if ( !newColor.error && self._getColor( newColor ) !== self._getColor() ) {
					self._color = newColor;
					self.options.color = self._getColor();
					self.active = 'external';
					self._change();
				}
			} else {
				return self._super( key, value );
			}
		},
		/**
		 * Returns the iris object if no new color is provided. If a new color is provided, it sets the new color.
		 *
		 * @param newColor {string|*} The new color to use. Can be undefined.
		 *
		 * @since 3.0.0
		 *
		 * @return {string} The element's color.
		 */
		color: function ( newColor ) {
			if ( newColor === true ) {
				return this._color.clone();
			}
			if ( newColor === undef ) {
				return this._getColor();
			}
			this.option( 'color', newColor );
		},
	} );

	/**
	 * wpColorPicker
	 */
	$.widget( 'wp.wpColorPicker', $.wp.wpColorPicker, {
		/**
		 * Alpha options
		 *
		 * @since 3.0.0
		 *
		 * @type {Object}
		 */
		alphaOptions: {
			alphaEnabled: false,
		},
		/**
		 * Get the alpha options.
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {object} The current alpha options.
		 */
		_getAlphaOptions: function () {
			var el = this.element,
				type = ( el.data( 'type' ) || this.options.type ),
				color = ( el.data( 'defaultColor' ) || el.val() ),
				options = {
					alphaEnabled: ( el.data( 'alphaEnabled' ) || false ),
					alphaCustomWidth: 130,
					alphaReset: false,
					alphaColorType: 'rgb',
					alphaColorWithSpace: false,
					alphaSkipDebounce: ( !!el.data( 'alphaSkipDebounce' ) || false ),
				};

			if ( options.alphaEnabled ) {
				options.alphaEnabled = ( el.is( 'input' ) && 'full' === type );
			}

			if ( !options.alphaEnabled ) {
				return options;
			}

			options.alphaColorWithSpace = ( color && color.match( /\s/ ) );

			$.each( options, function ( name, defaultValue ) {
				var value = ( el.data( name ) || defaultValue );
				switch ( name ) {
					case 'alphaCustomWidth':
						value = ( value ? parseInt( value, 10 ) : 0 );
						value = ( isNaN( value ) ? defaultValue : value );
						break;
					case 'alphaColorType':
						if ( !value.match( /^((octo)?hex|(rgb|hsl)a?)$/ ) ) {
							if ( color && color.match( /^#/ ) ) {
								value = 'hex';
							} else if ( color && color.match( /^hsla?/ ) ) {
								value = 'hsl';
							} else {
								value = defaultValue;
							}
						}
						break;
					default:
						value = !!value;
						break;
				}
				options[ name ] = value;
			} );

			return options;
		},
		/**
		 * Create widget
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_create: function () {
			// Return early if Iris support is missing.
			if ( !$.support.iris ) {
				return;
			}

			// Set the alpha options for the current instance.
			this.alphaOptions = this._getAlphaOptions();

			// Create widget.
			this._super();
		},
		/**
		 * Binds event listeners to the color picker and create options, etc...
		 *
		 * @since 3.0.0
		 * @access private
		 *
		 * @return {void}
		 */
		_addListeners: function () {
			if ( !this.alphaOptions.alphaEnabled ) {
				return this._super();
			}

			var self = this,
				el = self.element,
				isDeprecated = self.toggler.is( 'a' );

			this.alphaOptions.defaultWidth = el.width();
			if ( this.alphaOptions.alphaCustomWidth ) {
				el.width( parseInt( this.alphaOptions.defaultWidth + this.alphaOptions.alphaCustomWidth, 10 ) );
			}

			self.toggler.css( {
				'position': 'relative',
				'background-image': 'url(' + backgroundImage + ')'
			} );

			if ( isDeprecated ) {
				self.toggler.html( '<span class="color-alpha" />' );
			} else {
				self.toggler.append( '<span class="color-alpha" />' );
			}

			self.colorAlpha = self.toggler.find( 'span.color-alpha' ).css( {
				'width': '30px',
				'height': '100%',
				'position': 'absolute',
				'top': 0,
				'background-color': el.val(),
			} );

			// Define the correct position for ltr or rtl direction.
			if ( 'ltr' === self.colorAlpha.css( 'direction' ) ) {
				self.colorAlpha.css( {
					'border-bottom-left-radius': '2px',
					'border-top-left-radius': '2px',
					'left': 0
				} );
			} else {
				self.colorAlpha.css( {
					'border-bottom-right-radius': '2px',
					'border-top-right-radius': '2px',
					'right': 0
				} );
			}


			el.iris( {
				/**
				 * @summary Handles the onChange event if one has been defined in the options.
				 *
				 * Handles the onChange event if one has been defined in the options and additionally
				 * sets the background color for the toggler element.
				 *
				 * @since 3.0.0
				 *
				 * @param {Event} event    The event that's being called.
				 * @param {HTMLElement} ui The HTMLElement containing the color picker.
				 *
				 * @returns {void}
				 */
				change: function ( event, ui ) {
					self.colorAlpha.css( { 'background-color': ui.color.to_s( self.alphaOptions.alphaColorType ) } );

					// fire change callback if we have one
					if ( typeof self.options.change === 'function' ) {
						self.options.change.call( this, event, ui );
					}
				}
			} );


			/**
			 * Prevent any clicks inside this widget from leaking to the top and closing it.
			 *
			 * @since 3.0.0
			 *
			 * @param {Event} event The event that's being called.
			 *
			 * @return {void}
			 */
			self.wrap.on( 'click.wpcolorpicker', function ( event ) {
				event.stopPropagation();
			} );

			/**
			 * Open or close the color picker depending on the class.
			 *
			 * @since 3.0.0
			 */
			self.toggler.on( 'click', function () {
				if ( self.toggler.hasClass( 'wp-picker-open' ) ) {
					self.close();
				} else {
					self.open();
				}
			} );

			/**
			 * Checks if value is empty when changing the color in the color picker.
			 * If so, the background color is cleared.
			 *
			 * @since 3.0.0
			 *
			 * @param {Event} event The event that's being called.
			 *
			 * @return {void}
			 */
			el.on( 'change', function ( event ) {
				var val = $( this ).val();

				if ( el.hasClass( 'iris-error' ) || val === '' || val.match( /^(#|(rgb|hsl)a?)$/ ) ) {
					if ( isDeprecated ) {
						self.toggler.removeAttr( 'style' );
					}

					self.colorAlpha.css( 'background-color', '' );

					// fire clear callback if we have one
					if ( typeof self.options.clear === 'function' ) {
						self.options.clear.call( this, event );
					}
				}
			} );

			/**
			 * Enables the user to either clear the color in the color picker or revert back to the default color.
			 *
			 * @since 3.0.0
			 *
			 * @param {Event} event The event that's being called.
			 *
			 * @return {void}
			 */
			self.button.on( 'click', function ( event ) {
				if ( $( this ).hasClass( 'wp-picker-default' ) ) {
					el.val( self.options.defaultColor ).change();
				} else if ( $( this ).hasClass( 'wp-picker-clear' ) ) {
					el.val( '' );
					if ( isDeprecated ) {
						self.toggler.removeAttr( 'style' );
					}

					self.colorAlpha.css( 'background-color', '' );

					// fire clear callback if we have one
					if ( typeof self.options.clear === 'function' ) {
						self.options.clear.call( this, event );
					}

					el.trigger( 'change' );
				}
			} );
		},
	} );
} )( jQuery );