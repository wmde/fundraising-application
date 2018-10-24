'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity,
	PAYMENT_TYPE_DEBIT_VALUE = 'BEZ',
	hasValidDirectDebitPayment = function ( state ) {
		return (
			state.donationFormContent.paymentType === PAYMENT_TYPE_DEBIT_VALUE &&
			state.donationInputValidation.paymentType.isValid === true &&
			state.validity.bankData === Validity.VALID
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
		respectiveValidators = _.pick( state.donationInputValidation, [ 'paymentType', 'iban', 'bic' ] )
	;

	result.dataEntered = _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if ( hasValidDirectDebitPayment( state ) || hasOtherValidPayment( state ) ) {
		result.isValid = Validity.VALID;
	} else if ( !_.contains( _.pluck( respectiveValidators, 'isValid' ), false ) ) {
		result.isValid = Validity.INCOMPLETE;
	} else {
		result.isValid = Validity.INVALID;
	}

	return result;
};
