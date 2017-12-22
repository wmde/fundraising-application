'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' );

function namesAreFilled( state ) {
	return state.membershipFormContent.firstName !== '' && state.membershipFormContent.lastName !== '';
}

function addressRequiresSalutation( state ) {
	return state.membershipFormContent.addressType === 'person'
}

module.exports = function ( state ) {
	if ( state.membershipFormContent.salutation !== '' ) {
		validationResult.dataEntered = true;
		validationResult.isValid = true;
	} else if ( addressRequiresSalutation( state ) && namesAreFilled( state ) ) {
		validationResult.dataEntered = true;
		validationResult.isValid = false;
	}

	return validationResult
};
