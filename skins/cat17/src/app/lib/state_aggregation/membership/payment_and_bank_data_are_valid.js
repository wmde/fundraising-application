'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity,
	PAYMENT_TYPE_DEBIT_VALUE = 'BEZ',
	hasValidDirectDebitPayment = function ( state ) {
		return (
			state.membershipFormContent.paymentType === PAYMENT_TYPE_DEBIT_VALUE &&
			state.validity.bankData === Validity.VALID
		);
	},
	hasOtherValidPayment = function ( state ) {
		return (
			state.membershipFormContent.paymentType !== PAYMENT_TYPE_DEBIT_VALUE &&
			state.membershipFormContent.paymentType !== Validity.INCOMPLETE
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
	} else if ( !_.contains( _.pluck( respectiveValidators, 'isValid' ), Validity.INVALID ) ) {
		result.isValid = Validity.INCOMPLETE;
	} else {
		result.isValid = Validity.INVALID;
	}

	return result;
};
