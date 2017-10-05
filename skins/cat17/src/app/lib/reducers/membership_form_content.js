'use strict';

var formContentLib = require( './form_content' ),
	objectAssign = require( 'object-assign' ),
	initialState = {
		membershipType: '', // sustaining, active
		amount: 0,
		paymentType: 'BEZ',
		isCustomAmount: false,
		paymentIntervalInMonths: -1, // 1, 3, 6, 12
		debitType: 'sepa', // sepa and "non-sepa"
		addressType: '', // person, firma
		salutation: '',
		title: '',
		firstName: '',
		lastName: '',
		companyName: '',
		street: '',
		postcode: '',
		city: '',
		country: 'DE',
		email: '',
		dateOfBirth: '',
		phoneNumber: '',
		iban: '',
		bic: '',
		accountNumber: '',
		bankCode: '',
		bankname: '',
		confirmSepa: false,
    confirmNewsletter: false,
    contactPerson: '',
    activePresets: false
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
