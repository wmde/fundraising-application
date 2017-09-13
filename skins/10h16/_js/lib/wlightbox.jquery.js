( function ( $ ) {
	var IE = ( !!window.ActiveXObject && +( /msie\s(\d+)/i.exec( navigator.userAgent )[ 1 ] ) ) || undefined;

	function getArrowDirection( elementOptions ) {

		var arrowDirections = {
				Top: 'left',
				Bottom: 'left',
				Left: 'top',
				Right: 'top'
			},
			d, optionName;
		for ( d in arrowDirections ) {
			if ( arrowDirections.hasOwnProperty( d ) ) {
				optionName = 'arrow' + d;
				if ( elementOptions[ optionName ] ) {
					return { direction: d.toLowerCase(), cssProperty: arrowDirections[ d ], optionName: optionName };
				}
			}
		}
		return false;
	}

	$.fn.wlightbox = function ( options ) {
		return this.each( function () {
			var s = this,
				self = $( this ),
				$inlineContentObj = $( $( self ).attr( 'data-href' ) ),
				$inlineContentObjContainer = $( $( self ).attr( 'data-href' ) ).parent(),
				$wlightbox = $( '<div id="wlightbox"></div>' ),
				$wlightboxContent = $( '<div id="wlightbox-content"></div>' ),
				$wlightboxClose = $( '<a href="#" id="wlightbox-close" class="icon-remove-sign" title="close"></a>' ),
				$wlightboxOverlay = $( '<div id="wlightbox-overlay"/>' ),
				$wlightboxCss, open = false;

			s.init = function () {
				s.options = $.extend( {}, $.fn.wlightbox.defaultOptions, options );

				// change numeric value into css value with 'px'
				$.each( 'top bottom left right arrowLeft arrowRight'.split( ' ' ), function ( index, item ) {
					if ( $.isNumeric( s.options[ item ] ) ) {
						s.options[ item ] += 'px';
					}
				} );

				// open wlightbox
				self.click( function ( e ) {
					s.create();
					s.initEventHandling();
					s.open();

					e.preventDefault();
				} );
			};

			s.create = function () {
				var arrowData;
				if ( $( '#wlightbox' ).length == 0 ) {
					$( s.options.container ).append( $wlightbox );
				}

				$wlightbox.empty();

				if ( $( '#wlightbox-content' ).length == 0 ) {
					$wlightbox.append( $wlightboxContent );
				}
				if ( $( '#wlightbox-overlay' ).length == 0 ) {
					$( s.options.overlayContainer ).append( $wlightboxOverlay );
				}

				// close button
				if ( s.options.closeButton && $( '#wlightbox-close' ).length == 0 ) {
					$wlightbox.append( $wlightboxClose );
				}

				// position & size
				$.each( 'top bottom left right width height maxWidth maxHeight'.split( ' ' ), function ( index, item ) {
					if ( s.options[ item ] != undefined ) {
						$wlightbox.css( item, s.options[ item ] );
					}
				} );

				s.initArrow();

			};

			s.initArrow = function () {
				var arrowData = getArrowDirection( s.options ),
					cssString;
				if ( !arrowData ) {
					return;
				}
				cssString = '#wlightbox.arrow-box-' + arrowData.direction + '::before { ' +
							arrowData.cssProperty + ': ' + s.options[ arrowData.optionName ] + ' }';
				if ( !IE ) {
					$wlightboxCss = $( '<style type="text/css" id="wlightbox-css"></style>' );
					$wlightboxCss.empty();

					if ( $( '#wlightbox-css' ).length == 0 ) {
						$( 'head' ).append( $wlightboxCss );
					}

					// init arrow-box
					$wlightboxCss.append( cssString );

				} else {
					$.fn.wlightbox.css.cssText = cssString;
				}
				$wlightbox.attr( 'class', 'arrow-box-' + arrowData.direction );
			};

			s.initEventHandling = function () {
				// close
				if ( s.options.closeButton ) {
					$wlightboxClose.on( 'click', function ( e ) {
						e.preventDefault();
						s.close();
					} );
				}

				if ( s.options.overlayClose ) {
					$wlightboxOverlay.click( function ( e ) {
						e.preventDefault();
						s.close();
					} );
				}

				$( document ).keydown( function ( e ) {
					var key = e.keyCode;
					if ( open && s.options.escKey && key === 27 ) {
						e.preventDefault();
						s.close();
					}
				} );
			};

			s.open = function () {
				if ( $inlineContentObj.length > 0 ) {
					$inlineContentObj.appendTo( $wlightboxContent );
				}

				$wlightbox.hide().fadeIn( s.options.speedIn );
				$wlightboxOverlay.hide().fadeTo( s.options.speedIn, s.options.opacity / 100, function () {
					open = true;
				} );
			};

			s.close = function () {
				// hide & remove content
				$wlightboxOverlay.fadeOut( s.options.speedOut, function () {
					$wlightboxOverlay.remove();
				} );

				$wlightbox.fadeOut( s.options.speedOut, function () {
					$inlineContentObj.appendTo( $inlineContentObjContainer );

					$wlightbox.remove();
				} );

				open = false;
			};

			s.init();
		} );
	};
	$.fn.wlightbox.defaultOptions = {
		container: 'body',
		overlayContainer: 'body',
		speedIn: 300,
		speedOut: 300,
		opacity: 60,
		overlayClose: true,
		escKey: true,
		closeButton: true,
		top: false,
		bottom: false,
		left: false,
		right: false,
		arrowBox: true,
		arrowLeft: false,
		arrowRight: false,
		width: false,
		height: false,
		maxWidth: false,
		maxHeight: false
	};

	if ( IE ) {
		$.fn.wlightbox.css = document.createStyleSheet( '' );
	}

} )( jQuery );
