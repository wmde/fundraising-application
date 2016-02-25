'use strict';

module.exports = {
	newAddPageAction: function ( pageName ) {
		return {
			type: 'ADD_PAGE',
			payload: { name: pageName }
		};
	},

	newNextPageAction: function () {
		return {
			type: 'NEXT_PAGE'
		};
	},

	newStoreValidationResultAction: function ( isValid ) {
		return {
			type: 'VALIDATION_RESULT',
			payload: { isValid: isValid }
		};
	},

	newSelectAmountAction: function ( amount ) {
		return {
			type: 'SELECT_AMOUNT',
			payload: { amount: amount }
		};
	},

	newInputAmountAction: function ( amount ) {
		return {
			type: 'INPUT_AMOUNT',
			payload: { amount: amount }
		};
	},

	newSelectPaymentTypeAction: function ( paymentType ) {
		return {
			type: 'SELECT_PAYMENT_TYPE',
			payload: { paymentType: paymentType }
		};
	}

};
