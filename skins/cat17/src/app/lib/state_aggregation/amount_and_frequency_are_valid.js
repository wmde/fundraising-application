'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult );
	// todo Why is amount.isValid always bogous?
	result.dataEntered = state.donationFormContent.paymentIntervalInMonths >= 0 || state.donationFormContent.amount != 0;

	if ( state.donationFormContent.paymentIntervalInMonths >= 0 && state.donationFormContent.amount != 0 ) {
		result.isValid = true;
	} else if ( state.donationInputValidation.amount.isValid === null ) {
		result.isValid = null;
	} else {
		result.isValid = false;
	}
	return result;
};
