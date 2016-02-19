'use strict';

var objectAsssign = require( 'object-assign' ),
	AmountCollector = {
		amountSelection: null,
		customAmountField: null,
		getAmount: function () {
			return this.amountSelection.val() || this.customAmountField.val() || 0;
		}
	},
	hasValFunction = function ( obj ) {
		return typeof obj.val === 'function';
	},
	createAmountCollector = function ( amountSelection, customAmountField ) {
		if ( hasValFunction( amountSelection )  && hasValFunction( customAmountField ) ) {
			return objectAsssign( Object.create( AmountCollector ), {
				amountSelection: amountSelection,
				customAmountField: customAmountField
			} ) ;
		} else {
			throw new Error( 'Amount parameters must have functions to get values!' );
		}
	};

module.exports = {
	createAmountCollector: createAmountCollector
};
