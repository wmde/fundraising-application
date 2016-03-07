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
	},

	/**
	 *
	 * @param {Object|Promise} validationResult
	 * @return {{type: string, payload: *}}
	 */
	newFinishAmountValidationAction: function ( validationResult ) {
		return {
			type: 'FINISH_AMOUNT_VALIDATION',
			payload: validationResult
		};
	}

};
