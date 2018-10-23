'use strict';

var validationResult = require( '../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity;

function namesAreFilled( state ) {
	return state.donationFormContent.firstName !== '' && state.donationFormContent.lastName !== '';
}

function addressRequiresSalutation( state ) {
	return state.donationFormContent.addressType === 'person';
}

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	if ( state.donationFormContent.salutation !== '' ) {
		result.dataEntered = true;
		result.isValid = Validity.VALID;
	} else if ( addressRequiresSalutation( state ) && namesAreFilled( state ) ) {
		result.dataEntered = true;
		result.isValid = Validity.INVALID;
	}

	return result;
};
