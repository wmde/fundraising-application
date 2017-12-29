'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' ),
	PAYMENT_TYPE_DEBIT_VALUE = 'BEZ',
	hasValidDirectDebitPayment = function ( state ) {
		return (
			state.donationFormContent.paymentType === PAYMENT_TYPE_DEBIT_VALUE &&
			state.donationInputValidation.paymentType.isValid === true &&
			state.validity.bankData === true
		);
	},
	hasOtherValidPayment = function ( state ) {
		return (
			state.donationFormContent.paymentType !== PAYMENT_TYPE_DEBIT_VALUE &&
			state.donationInputValidation.paymentType.isValid === true
		);
	}
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult(),
		respectiveValidators = _.pick( state.donationInputValidation, [ 'paymentType', 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if ( hasValidDirectDebitPayment( state ) || hasOtherValidPayment( state ) ) {
		result.isValid = true;
	} else if ( !_.contains( _.pluck( respectiveValidators, 'isValid' ), false ) ) {
		result.isValid = null;
	} else {
		result.isValid = false;
	}

	return result;
};
