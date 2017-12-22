'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' ),
	PAYMENT_TYPE_DEBIT_VALUE = 'BEZ',
	hasValidDirectDebitPayment = function ( state ) {
		return (
			state.membershipFormContent.paymentType === PAYMENT_TYPE_DEBIT_VALUE &&
			state.validity.bankData === true
		);
	},
	hasOtherValidPayment = function ( state ) {
		return (
			state.membershipFormContent.paymentType !== PAYMENT_TYPE_DEBIT_VALUE &&
			state.membershipFormContent.paymentType !== null
		);
	}
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult(),
		respectiveValidators = _.pick( state.membershipInputValidation, [ 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = state.membershipFormContent.paymentType !== null || _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if ( hasValidDirectDebitPayment( state ) || hasOtherValidPayment( state ) ) {
		result.isValid = true;
	}
	else if ( !_.contains( _.pluck( respectiveValidators, 'isValid' ), false ) ) {
		result.isValid = null;
	}
	else {
		result.isValid = false;
	}

	return result;
};
