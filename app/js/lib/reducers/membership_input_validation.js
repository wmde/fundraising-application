'use strict';

var inputValidationLib = require( './input_validation' ),
	initialState = {
		firstName: null,
		lastName: null,
		companyName: null,
		street: null,
		postcode: null,
		city: null,
		dateOfBirth: null,
		phoneNumber: null,
		iban: null,
		bic: null,
		account: null,
		bankCode: null
	};

module.exports = function membershipInputValidation( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	return inputValidationLib.inputValidation( state, action );
};
