'use strict';

var formContentLib = require( './form_content' ),
	objectAssign = require( 'object-assign' ),
	initialState = {
		addressType: '',
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
		confirmNewsletter: false,
		activePresets: false,
		donationReceipt: false
	};

module.exports = function donorUpdateFormContent( state, action ) {
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
