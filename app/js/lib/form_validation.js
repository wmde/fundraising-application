'use strict';

var jQuery = require( 'jquery' ),
	objectAsssign = require( 'object-assign' ),

	AmountValidator = {
		amountCollector: null,
		paymentType: null,
		validationUrl: '',
		postFunction: null,
		validate: function () {
			var data = {
				amount: this.amountCollector.getAmount(),
				paymentType: this.paymentType.val()
			};
			return this.postFunction( this.validationUrl, data, null, 'json' );
		}
	},

	/**
	 *
	 * @param {AmountCollector} amountCollector
	 * @param {jQuery} $paymentType
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @returns {AmountValidator}
	 */
	createAmountValidator = function ( amountCollector, $paymentType, validationUrl, postFunction ) {
		return objectAsssign( Object.create( AmountValidator ), {
			amountCollector: amountCollector,
			paymentType: $paymentType,
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	};

module.exports = {
	createAmountValidator: createAmountValidator
};
