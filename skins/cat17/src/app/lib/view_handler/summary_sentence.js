'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	SummarySentence = {
		defaultSentence: null,
		summarySentence: null,
		userHasInteracted: false,
		initialized: false,
		update: function ( paymentDataIsValid, userInteractionCount ) {
			if ( !paymentDataIsValid ) {
				return;
			}

			if ( userInteractionCount > 0 ) {
				this.hideSummarySentenceContainerOnFirstUserInteraction();

				return;
			}

			this.initialize();
		},
		initialize: function () {
			if ( this.initialized ) {
				return;
			}

			this.defaultSentence.addClass( 'hidden' );
			this.summarySentence.removeClass( 'hidden' );
			this.showSummarySentenceInFixedContainer();

			this.initialized = true;
		},
		showSummarySentenceInFixedContainer: function() {
			if ( typeof this.clonedSummarySentence !== 'undefined' ) {
				return;
			}
			this.clonedSummarySentence = $( '<div class="summary-container"></div>' );
			this.summarySentence.clone().removeClass( 'banner' ).appendTo( this.clonedSummarySentence );
			this.clonedSummarySentence.prependTo( this.summarySentence.parent() );
		},
		hideSummarySentenceContainerOnFirstUserInteraction: function( userInteractionCount ) {
			if ( this.userHasInteracted ) {
				return;
			}
			this.clonedSummarySentence.fadeOut();
			this.userHasInteracted = true;
		}
	};

module.exports = {
	/**
	 * Summary banner shown after user comes to the page with valid payment data (from banner)
	 * @param {jQuery} defaultSentence Element to show when the payment data is not valid
	 * @param {jQuery} summarySentence
	 * @return {SummarySentence}
	 */
	createSummarySentence: function ( defaultSentence, summarySentence ) {
		return objectAssign( Object.create( SummarySentence ), {
			defaultSentence: defaultSentence,
			summarySentence: summarySentence
		} );
	}
};