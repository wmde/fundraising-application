'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	ElementStart = {
		MARGIN: 'MARGIN',
		ELEMENT: 'ELEMENT',
		PADDDING: 'PADDING'
	},

	calculateFixedHeaderElementHeight = function ( $fixedHeaderElements ) {
		return _.reduce( $fixedHeaderElements.get(), function ( offset, element ) {
			var $elm = jQuery( element );
			if ( $elm.is( ':visible' ) ) {
				offset += $elm.height();
			}
			return offset;
		}, 0 );
	},

	calculateElementPadding = function ( $element ) {
		var matchedElemPadding = $element.css( 'padding-top' ).match( /^(\d+)px$/ );

		if ( !matchedElemPadding ) {
			return 0;
		}
		return parseInt( matchedElemPadding[ 1 ] );
	},

	calculateElementMargin = function ( $element ) {
		var matchedElemPadding = $element.css( 'margin-top' ).match( /^(\d+)px$/ );

		if ( !matchedElemPadding ) {
			return 0;
		}
		return parseInt( matchedElemPadding[ 1 ] );
	},

	findElementWithLowestOffset = function ( elements ) {
		return _.reduce( elements, function ( acc, element ) {
			if ( element.length < 1 ) {
				return acc;
			}
			if ( acc === null ) {
				return element;
			}
			if ( acc.offset().top > element.offset().top ) {
				return element;
			}
			return acc;
		}, null );
	},

	/**
	 *
	 * @param {jQuery} $element Element whose offset will be taken
	 * @param {[jQuery]} $fixedHeaderElements Elements whose height will be subtracted from the offset
	 * @param {Object} options
	 * @param {string} options.elementStart
	 * @return {number}
	 */
	calculateElementOffset = function ( $element, $fixedHeaderElements, options ) {
		var offset = $element.offset().top - calculateFixedHeaderElementHeight( $fixedHeaderElements );
		options = _.extend( { elementStart: ElementStart.ELEMENT }, options );
		switch ( options.elementStart ) {
			case ElementStart.PADDDING:
				return offset + calculateElementPadding( $element );
			case ElementStart.MARGIN:
				return offset - calculateElementMargin( $element );
		}
		return offset;
	},

	AnimatedScroller = {
		fixedHeaderElements: null,
		scrollTo: function ( $element, options, seconds ) {
			var self = this;
			seconds = seconds || 1000;
			this.fixedHeaderElements.addClass( 'scrolling' );
			jQuery( 'html, body' ).stop( true ).animate( {
				scrollTop: calculateElementOffset( $element, this.fixedHeaderElements, options )
			}, seconds, function () {
				// Callback after animation
				self.fixedHeaderElements.removeClass( 'scrolling' );
				// Must change focus!
				$element.focus();
				if ( $element.is( ':focus' ) ) { // Checking if the target was focused
					return false;
				} else {
					$element.focus(); // Set focus again
				}
			} );

		}
	},

	LinkScroller = {
		scroller: null,
		linkIsInsideCompletedSummaryOnSmallScreen: function ( link ) {
			// only the completed fields at the bottom summary are inside a .wrap-field.completed
			return jQuery( window ).width() < 1200 && jQuery( link ).closest( '.wrap-field.has-longtext.completed .wrap-input' ).length > 0;
		},
		scrollToTarget: function ( evt ) {
			var target;

			evt.preventDefault();

			if ( this.linkIsInsideCompletedSummaryOnSmallScreen( evt.currentTarget ) ) {
				return;
			}

			target = jQuery( evt.currentTarget.hash );
			target = target.length ? target : jQuery( '[name=' + evt.currentTarget.hash.slice( 1 ) + ']' );
			if ( target.length > 0 ) {
				this.scroller.scrollTo( target, { elementStart: ElementStart.PADDDING } );
			}
		}
	}
;

module.exports = {
	createAnimatedScroller: function ( fixedHeaderElements ) {
		return objectAssign( Object.create( AnimatedScroller ), { fixedHeaderElements: fixedHeaderElements } );
	},
	scrollOnSuboptionChange: function ( $suboptionInput, $suboptionContainer, scroller ) {
		$suboptionInput.on( 'change', function ( evt ) {
			var inputWrapper = $suboptionContainer.find( '.wrap-field input[value=' + evt.target.value + ']' ).parents( '.wrap-field' ),
				infoText = inputWrapper.find( '.info-text' ),
				scrollTarget = findElementWithLowestOffset( [ inputWrapper, infoText ] );
			if ( scrollTarget !== null ) {
				scroller.scrollTo( scrollTarget, { elementStart: ElementStart.ELEMENT } );
			}
		} );
	},
	/**
	 * Ensure smooth scroll to the given anchor links. Make sure to only pass links on the same page that can be scrolled to.
	 *
	 * @param {jQuery} $links
	 * @param {Object} scroller
	 */
	addScrollToLinkAnchors: function ( $links, scroller ) {
		var linkScroller = objectAssign( Object.create( LinkScroller ), { scroller: scroller } );
		$links.not( '[href="#"]' )
			.not( '[href="#0"]' )
			.not( '.state-overview .wrap-field.completed .wrap-input' )
			.click( linkScroller.scrollToTarget.bind( linkScroller ) );
	},
	ElementStart: ElementStart,
	// exposed for testing
	calculateElementOffset: calculateElementOffset,
	findElementWithLowestOffset: findElementWithLowestOffset
};
