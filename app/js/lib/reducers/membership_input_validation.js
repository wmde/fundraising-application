'use strict';

var _ = require( 'underscore' ),
	inputValidationLib = require( './input_validation' ),
	defaultFields = {
		dataEntered: false,
		isValid: null
	},
	initialState = {
		amount: _.clone( defaultFields ),
		firstName: _.clone( defaultFields ),
		lastName: _.clone( defaultFields ),
		companyName: _.clone( defaultFields ),
		street: _.clone( defaultFields ),
		postcode: _.clone( defaultFields ),
		city: _.clone( defaultFields ),
		email: _.clone( defaultFields ),
		dateOfBirth: _.clone( defaultFields ),
		phoneNumber: _.clone( defaultFields ),
		iban: _.clone( defaultFields ),
		bic: _.clone( defaultFields ),
		account: _.clone( defaultFields ),
		bankCode: _.clone( defaultFields ),
		confirmSepa: _.clone( defaultFields )
	},
	optionalFields = [
		'dateOfBirth', 'phoneNumber'
	],

	clearAddressValidityOnAddressTypeChange = function ( state, action ) {
		if ( action.type !== 'CHANGE_CONTENT' || action.payload.contentName !== 'addressType' ) {
			return state;
		}
		switch ( action.payload.value ) {
			case 'person':
				return _.extend( {}, state, { companyName: _.clone( defaultFields ) } );
			case 'firma':
				return _.extend( {}, state, {
					firstName: _.clone( defaultFields ),
					lastName: _.clone( defaultFields )
				} );
			default:
				// just a guard against field value changes, should not happen normally
				throw new Error( 'invalid address type:' + action.payload.value );
		}
	},

	clearCompanyValidityOnActiveMembershipChange = function ( state, action ) {
		if ( action.type !== 'CHANGE_CONTENT' ||
			action.payload.contentName !== 'membershipType' ||
				action.value === 'sustaining'
		) {
			return state;
		}
		return _.extend( {}, state, { companyName: _.clone( defaultFields ) } );
	},

	clearOptionalFieldValidityOnEmptying = function ( state, action ) {
		var clearedField = {};
		if ( action.type === 'CHANGE_CONTENT' ) {
			if ( _.contains( optionalFields, action.payload.contentName ) ) {
				clearedField[ action.payload.contentName ] = _.clone( defaultFields );
				return _.extend( {}, state, clearedField );
			}
		}

		return state;
	};

module.exports = function membershipInputValidation( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	state = clearAddressValidityOnAddressTypeChange( state, action );
	state = clearCompanyValidityOnActiveMembershipChange( state, action );
	state = clearOptionalFieldValidityOnEmptying( state, action );
	return inputValidationLib.inputValidation( state, action );
};
