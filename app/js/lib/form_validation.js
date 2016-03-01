'use strict';

var jQuery = require( 'jquery' ),
	objectAsssign = require( 'object-assign' ),

	AmountValidator = {
		previousAmount: null,
		previousPaymentType: null,
		validationUrl: '',
		postFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( formValues.amount === this.previousAmount && formValues.paymentType === this.previousPaymentType ) {
				return;
			}
			this.storePreviousValues( formValues );
			postData = {
				amount: formValues.amount,
				paymentType: formValues.paymentType
			};
			return this.postFunction( this.validationUrl, postData, null, 'json' );
		},
		storePreviousValues: function ( formValues ) {
			this.previousAmount = formValues.amount;
			this.previousPaymentType = formValues.paymentType;
		}
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, postFunction ) {
		return objectAsssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	};

module.exports = {
	createAmountValidator: createAmountValidator
};
