'use strict';

var _ = require( 'lodash' ),
	inputValidationLib = require( './input_validation' ),
	objectFields = {
		dataEntered: false,
		isValid: null
	},
	initialState = {
		firstName: _.clone( objectFields ),
		lastName: _.clone( objectFields ),
		companyName: _.clone( objectFields ),
		street: _.clone( objectFields ),
		postcode: _.clone( objectFields ),
		city: _.clone( objectFields ),
		email: _.clone( objectFields ),
		dateOfBirth: _.clone( objectFields ),
		phoneNumber: _.clone( objectFields ),
		iban: _.clone( objectFields ),
		bic: _.clone( objectFields ),
		account: _.clone( objectFields ),
		bankCode: _.clone( objectFields )
	};

module.exports = function membershipInputValidation( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	return inputValidationLib.inputValidation( state, action );
};
