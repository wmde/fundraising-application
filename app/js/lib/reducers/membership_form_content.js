'use strict';

var formContentLib = require( './form_content' ),
	objectAssign = require( 'object-assign' ),
	initialState = {
		membershipType: 'sustaining', // sustaining, active
		amount: 0,
		isCustomAmount: false,
		paymentPeriodInMonths: 12, // 1, 3, 6, 12
		debitType: 'sepa', // sepa and "non-sepa"
		addressType: 'person', // person, firma
		salutation: 'Frau',
		title: '',
		firstName: '',
		lastName: '',
		company: '',
		street: '',
		postcode: '',
		city: '',
		countryCode: 'DE',
		email: '',
		dateOfBirth: '',
		phoneNumber: '',
		iban: '',
		bic: '',
		accountNumber: '',
		bankCode: '',
		bankname: ''
	};

module.exports = function membershipFormContent( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	switch ( action.type ) {
		case 'INITIALIZE_CONTENT':
			if ( formContentLib.stateContainsUnknownKeys( action.payload, initialState ) ) {
				throw new Error(
					'Initial state contains unknown keys: ' +
					formContentLib.getInvalidKeys( action.payload, initialState ).join( ', ' )
				);
			}
			return objectAssign( {}, state, action.payload );
		default:
			return formContentLib.formContent( state, action );
	}
};
