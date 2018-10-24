'use strict';

var _ = require( 'underscore' ),
	inputValidationLib = require( './input_validation' ),
	defaultFields = {
		dataEntered: false,
		isValid: null
	},
	initialState = {
		amount: _.clone( defaultFields ),
		title: _.clone( defaultFields ),
		salutation: _.clone( defaultFields ),
		firstName: _.clone( defaultFields ),
		lastName: _.clone( defaultFields ),
		companyName: _.clone( defaultFields ),
		street: _.clone( defaultFields ),
		postcode: _.clone( defaultFields ),
		city: _.clone( defaultFields ),
		email: _.clone( defaultFields ),
		dateOfBirth: _.clone( defaultFields ),
		iban: _.clone( defaultFields ),
		bic: _.clone( defaultFields )
	},
	optionalFields = [
		'dateOfBirth'
	],

	setValidityOnSalutationChange = function ( state, action ) {
		if ( action.type !== 'CHANGE_CONTENT' ||
			action.payload.contentName !== 'salutation' ) {
			return state;
		}
		return _.extend( {}, state, {
			salutation: { dataEntered: true, isValid: true }
		} );
	},

	clearCompanyValidityOnActiveMembershipChange = function ( state, action ) {
		if ( action.type !== 'CHANGE_CONTENT' ||
			action.payload.contentName !== 'membershipType' ||
			action.payload.value === 'sustaining'
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
	state = clearCompanyValidityOnActiveMembershipChange( state, action );
	state = clearOptionalFieldValidityOnEmptying( state, action );
	state = setValidityOnSalutationChange( state, action );

	return inputValidationLib.inputValidation( state, action );
};
