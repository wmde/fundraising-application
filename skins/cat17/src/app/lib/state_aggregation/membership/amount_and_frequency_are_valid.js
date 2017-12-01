'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult );
	// todo Why is amount.isValid always bogous?
	result.dataEntered = state.membershipFormContent.paymentIntervalInMonths >= 0 || state.membershipFormContent.amount != 0;

	if ( state.membershipFormContent.paymentIntervalInMonths >= 0 && state.membershipFormContent.amount != 0 ) {
		result.isValid = true;
	} else if ( state.membershipInputValidation.amount.isValid === null ) {
		result.isValid = null;
	} else {
		result.isValid = false;
	}
	return result;
};
