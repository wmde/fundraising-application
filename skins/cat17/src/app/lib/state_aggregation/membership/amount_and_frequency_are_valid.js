'use strict';

var validationResult = require( './../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	result.dataEntered = state.membershipFormContent.paymentIntervalInMonths > 0 || state.membershipFormContent.amount !== 0;

	if ( state.membershipFormContent.paymentIntervalInMonths > 0 &&
		state.membershipFormContent.amount !== 0 &&
		state.membershipInputValidation.amount.isValid === Validity.VALID ) {
		result.isValid = Validity.VALID;
	} else if ( state.membershipInputValidation.amount.isValid === Validity.INCOMPLETE ) {
		result.isValid = Validity.INCOMPLETE;
	} else {
		result.isValid = Validity.INVALID;
	}
	return result;
};
