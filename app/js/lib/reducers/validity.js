'use strict';

var objectAssign = require( 'object-assign' ),
	defaultState = {
		amount: null
	};

function isValid( validationResult ) {
	return validationResult.status === 'OK';
}

function validity( state, action ) {
	if ( typeof state !== 'object' ) {
		state = defaultState;
	}
	switch ( action.type ) {
		case 'VALIDATE_AMOUNT':
			return objectAssign( {}, state, { amount: isValid( action.payload ) } );
		default:
			return state;
	}
}

module.exports = validity;
