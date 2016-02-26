'use strict';

var jQuery = require( 'jquery' ),
	objectAsssign = require( 'object-assign' ),

	AmountValidator = {
		amountAccessor: null,
		paymentTypeAccessor: null,
		validationUrl: '',
		postFunction: null,
		validate: function () {
			var data = {
				amount: this.amountAccessor.getValue(),
				paymentType: this.paymentTypeAccessor.getValue()
			};
			return this.postFunction( this.validationUrl, data, null, 'json' );
		}
	},

	/**
	 *
	 * @param {*} amountAccessor object that supports the getValue interface
	 * @param {*} paymentTypeAccessor object that supports the getValue interface
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( amountAccessor, paymentTypeAccessor, validationUrl, postFunction ) {
		return objectAsssign( Object.create( AmountValidator ), {
			amountAccessor: amountAccessor,
			paymentTypeAccessor: paymentTypeAccessor,
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	};

module.exports = {
	createAmountValidator: createAmountValidator
};
