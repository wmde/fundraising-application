'use strict';

var validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	result.dataEntered = state.membershipFormContent.paymentIntervalInMonths > 0 || state.membershipFormContent.amount !== 0;

	if ( state.membershipFormContent.paymentIntervalInMonths > 0 && state.membershipFormContent.amount !== 0 && state.membershipInputValidation.amount.isValid ) {
		result.isValid = true;
	} else if ( state.membershipInputValidation.amount.isValid === null ) {
		result.isValid = null;
	} else {
		result.isValid = false;
	}
	return result;
};
