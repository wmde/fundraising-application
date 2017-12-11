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
			var $elm = $( element );
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

	/**
	 *
	 * @param {jQuery} $element Element whose offset will be taken
	 * @param {[jQuery]} $fixedHeaderElements Elements whose height will be subtracted from the offset
	 * @param {Object} options
	 * @param {string} options.elementStart
	 * @return {number}
	 */
	calculateElementOffset = function ( $element, $fixedHeaderElements, options ) {
		options = _.extend( { elementStart: ElementStart.ELEMENT }, options );
		var offset = $element.offset().top - calculateFixedHeaderElementHeight( $fixedHeaderElements );
		switch ( options.elementStart ) {
			case ElementStart.PADDDING:
				return offset + calculateElementPadding( $element );
			case ElementStart.MARGIN:
				return offset - calculateElementMargin( $element);
		}
		return offset;
	},

	AnimatedScroller = {
		fixedHeaderElements: null,
		scrollTo: function( $element, options ) {
			$( 'html, body' ).stop( true ).animate( {
				scrollTop: calculateElementOffset( $element, this.fixedHeaderElements, options )
			}, 1000, function () {
				// Callback after animation
				// Must change focus!
				$element.focus();
				if ($element.is( ':focus' ) ) { // Checking if the target was focused
					return false;
				} else {
					$element.attr( 'tabindex', '-1' ); // Adding tabindex for elements not focusable
					$element.focus(); // Set focus again
				}
			} );

		}
	},

	LinkScroller = {
		scroller: null,
		linkIsInsideCompletedSummaryOnSmallScreen: function( link ) {
			// only the completed fields at the bottom summary are inside a .wrap-field.completed
			return $( window ).width() < 1200 && $( link ).closest( '.wrap-field.completed .wrap-input' ).length > 0;
		},
		linkIsOnDifferentPage: function( link ) {
			return location.pathname.replace(/^\//, '') !== link.pathname.replace(/^\//, '') ||
				location.hostname !== link.hostname
		},
		scrollToTarget: function( evt ) {
			evt.preventDefault();
			if ( this.linkIsInsideCompletedSummaryOnSmallScreen( evt.currentTarget ) || this.linkIsOnDifferentPage( evt.currentTarget ) ) {
				return;
			}
			var target = $( evt.currentTarget.hash );
			target = target.length ? target : $( '[name=' + evt.currentTarget.hash.slice( 1 ) + ']' );
			if ( target.length > 0 ) {
				this.scroller.scrollTo( target, { elementStart: ElementStart.PADDDING } );
			}
		}
	}
	;

module.exports ={
	createAnimatedScroller: function ( fixedHeaderElements ) {
		return objectAssign( Object.create( AnimatedScroller ), { fixedHeaderElements: fixedHeaderElements } );
	},
	scrollOnSuboptionChange: function( $suboptionInput, $suboptionContainer, scroller ) {
		$suboptionInput.on( 'change', function ( evt ) {
			var wrapper = $suboptionContainer.find( '.wrap-field input[value=' + evt.target.value + ']' ).parents( '.wrap-field' );
			if (wrapper.length) {
				scroller.scrollTo( wrapper, { elementStart: ElementStart.ELEMENT } );
			}
		} )
	},
	addScrollToLinkAnchors: function( $links, scroller ) {
		var linkScroller = objectAssign( Object.create( LinkScroller ), { scroller: scroller } );
		$links.not('[href="#"]')
			.not('[href="#0"]')
			.not('.state-overview .wrap-field.completed .wrap-input')
			.click( linkScroller.scrollToTarget.bind( linkScroller ) );
	},
	ElementStart: ElementStart,
	// exposed for testing
	calculateElementOffset: calculateElementOffset
};