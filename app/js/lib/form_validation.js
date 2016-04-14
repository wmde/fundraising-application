'use strict';

var jQuery = require( 'jquery' ),
	objectAssign = require( 'object-assign' ),

	AddressValidator = {
		validationUrl: '',
		postFunction: null,
		validate: function ( formValues ) {
			if ( formValues.addressType === 'anonym' ) {
				return { status: 'OK' };
			}
			return this.postFunction( this.validationUrl, formValues, null, 'json' );
		}
	},

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

	createAddressValidator = function ( validationUrl, postFunction ) {
		return objectAssign( Object.create( AddressValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, postFunction ) {
		return objectAssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	};

module.exports = {
	createAmountValidator: createAmountValidator,
	createAddressValidator: createAddressValidator
};
