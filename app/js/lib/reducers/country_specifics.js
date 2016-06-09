'use strict';

var getCountrySpecifics = require( '../country_specifics_repository' ).getCountrySpecifics;

function countrySpecificValidation( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = {};
	}
	switch ( action.type ) {
		case 'CHANGE_CONTENT':
			if ( action.payload.contentName !== 'country' ) {
				return state;
			}
			return getCountrySpecifics( action.payload.value );
		case 'INITIALIZE_CONTENT':
			return getCountrySpecifics( action.payload.country );
		default:
			return state;
	}
}

module.exports = countrySpecificValidation;
