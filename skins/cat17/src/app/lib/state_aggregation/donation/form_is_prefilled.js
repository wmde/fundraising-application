'use strict';

var validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	result.dataEntered = state.donationFormContent.paymentIntervalInMonths >= 0 &&
		state.donationFormContent.amount !== 0 &&
		state.donationFormContent.paymentType !== '';

	return result;
};
