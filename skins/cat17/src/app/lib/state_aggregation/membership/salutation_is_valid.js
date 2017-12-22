'use strict';

var validationResult = require( './../validation_result' );

function namesAreFilled( state ) {
	return state.membershipFormContent.firstName !== '' && state.membershipFormContent.lastName !== '';
}

function addressRequiresSalutation( state ) {
	return state.membershipFormContent.addressType === 'person';
}

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	if ( state.membershipFormContent.salutation !== '' ) {
		result.dataEntered = true;
		result.isValid = true;
	} else if ( addressRequiresSalutation( state ) && namesAreFilled( state ) ) {
		result.dataEntered = true;
		result.isValid = false;
	}

	return result;
};
