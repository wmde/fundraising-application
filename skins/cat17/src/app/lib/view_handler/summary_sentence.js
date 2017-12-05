'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	SummarySentence = {
		defaultSentence: null,
		summarySentence: null,
		update: function ( paymentDataIsValid, userHasInteracted ) {
			// if !paymentDataIsValid { return}
			// if userHasInteracted {
			// 		if (this.previousValue != userHasInteracted) {
			//          this.clonedSummarySentence.fadeOut()
			//		}
			//      return;
			// }
			// clone summarySentence, add .cloned class to it

			// TODO CSS changes:
			// make sure z-index of .cloned is higher than regular summary elements so it effectively hides them
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
			summarySentence: summarySentence,
			regularSummary: regularSummary
		} );
	}
};