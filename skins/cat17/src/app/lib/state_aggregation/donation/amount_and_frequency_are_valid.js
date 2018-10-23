'use strict';

var validationResult = require( './../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	result.dataEntered = state.donationFormContent.paymentIntervalInMonths >= 0 || state.donationFormContent.amount !== 0;

	if ( state.donationFormContent.paymentIntervalInMonths >= 0 &&
		state.donationFormContent.amount !== 0 &&
		state.donationInputValidation.amount.isValid === Validity.VALID ) {
		result.isValid = Validity.VALID;
	} else if ( state.donationInputValidation.amount.isValid === Validity.INCOMPLETE ) {
		result.isValid = Validity.INCOMPLETE;
	} else {
		result.isValid = Validity.INVALID;
	}
	return result;
};
