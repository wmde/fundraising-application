'use strict';

var getCountrySpecifics = require( '../country_specifics_repository' ).getCountrySpecifics;

function countrySpecificValidation( state, action ) {
	var newState;
	if ( typeof state !== 'object' ) {
		newState = getCountrySpecifics( 'DE' );
	} else {
		newState = Object.assign( {}, state );
	}

	switch ( action.type ) {
		case 'UPDATE_ELEMENT_ATTRIBUTES':
			newState = getCountrySpecifics( action.payload.countryCode );
			return newState;
		default:
			return newState;
	}
}

module.exports = countrySpecificValidation;
