'use strict';

var _ = require( 'underscore' ),
	inputValidationLib = require( './input_validation' ),
	objectFields = {
		dataEntered: false,
		isValid: null
	},
	initialState = {
		amount: _.clone( objectFields ),
		salutation: _.clone( objectFields ),
		firstName: _.clone( objectFields ),
		lastName: _.clone( objectFields ),
		companyName: _.clone( objectFields ),
		street: _.clone( objectFields ),
		postcode: _.clone( objectFields ),
		city: _.clone( objectFields ),
		email: _.clone( objectFields ),
		iban: _.clone( objectFields ),
		bic: _.clone( objectFields ),
		account: _.clone( objectFields ),
		bankCode: _.clone( objectFields )
	};

module.exports = function donationInputValidation( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	return inputValidationLib.inputValidation( state, action );
};
