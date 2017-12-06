'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	calculateElementOffset = function ( $element, $fixedHeaderElements ) {
		var offset = 0,
			matchedElemPadding = $element.css( 'padding-top' ).match( /^(\d+)px$/ );
		_.each( $fixedHeaderElements.get(), function ( elm ) {
			var $elm = $( elm );
			if ( $elm.is( ':visible' ) ) {
				offset += $elm.height();
			}
		} );

		if ( matchedElemPadding ) {
			offset -= parseInt( matchedElemPadding[ 1 ] );
		}

		return $element.offset().top - offset;
	},

	RequiredElementOffsetCalculator = {
		bankDataElement: null,
		personalDataElement: null,
		fixedHeaderElements: null,
		getRequiredElementOffset: function ( formData ) {
			var requiredElement = formData.paymentType === 'BEZ' ? this.bankDataElement : this.personalDataElement;
			return calculateElementOffset( requiredElement, this.fixedHeaderElements );
		}
	},

	AnimatedScroller = {
		fixedHeaderElements: null,
		scrollTo: function( $element ) {
			$( 'html, body' ).stop( true ).animate( {
				scrollTop: calculateElementOffset( $element, this.fixedHeaderElements )
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
				this.scroller.scrollTo( target );
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
				scroller.scrollTo( wrapper );
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
	createRequiredElementOffsetCalculator: function ( fixedHeaderElements, bankDataElement, personalDataElement ) {
		return objectAssign( Object.create( RequiredElementOffsetCalculator ), {
			bankDataElement: bankDataElement,
			personalDataElement: personalDataElement,
			fixedHeaderElements: fixedHeaderElements
		})
	},
	// exposed for testing
	calculateElementOffset: calculateElementOffset
};