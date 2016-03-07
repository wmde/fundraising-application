'use strict';

var jQuery = require( 'jquery' ),
	objectAsssign = require( 'object-assign' ),

	AmountValidator = {
		validationUrl: '',
		postFunction: null,
		validate: function ( formValues ) {
			var postData = {
				amount: formValues.amount,
				paymentType: formValues.paymentType
			};
			return this.postFunction( this.validationUrl, postData, null, 'json' );
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
