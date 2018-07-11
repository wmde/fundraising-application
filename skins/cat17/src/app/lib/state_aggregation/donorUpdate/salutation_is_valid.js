'use strict';

var validationResult = require( './../validation_result' );

function namesAreFilled( state ) {
	return state.donorUpdateFormContent.firstName !== '' && state.donorUpdateFormContent.lastName !== '';
}

function addressRequiresSalutation( state ) {
	return state.donorUpdateFormContent.addressType === 'person';
}

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult();
	if ( state.donorUpdateFormContent.salutation !== '' ) {
		result.dataEntered = true;
		result.isValid = true;
	} else if ( addressRequiresSalutation( state ) && namesAreFilled( state ) ) {
		result.dataEntered = true;
		result.isValid = false;
	}

	return result;
};
