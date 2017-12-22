'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' );

function namesAreFilled( state ) {
	return state.donationFormContent.firstName !== '' && state.donationFormContent.lastName !== '';
}

function addressRequiresSalutation( state ) {
	return state.donationFormContent.addressType === 'person'
}

module.exports = function ( state ) {
	if ( state.donationFormContent.salutation !== '' ) {
		validationResult.dataEntered = true;
		validationResult.isValid = true;
	} else if ( addressRequiresSalutation( state ) && namesAreFilled( state ) ) {
		validationResult.dataEntered = true;
		validationResult.isValid = false;
	}

	return validationResult
};
