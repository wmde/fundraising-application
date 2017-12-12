'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult );
	result.dataEntered = state.donationFormContent.paymentIntervalInMonths >= 0 &&
		state.donationFormContent.amount !== 0 &&
		state.donationFormContent.paymentType !== '';

	return result;
};
