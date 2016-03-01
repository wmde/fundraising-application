'use strict';

var objectAssign = require( 'object-assign' ),
	defaultState = {
		amount: null
	};

/**
 * Check if action has error flag set (happens when the AJAX request fails) and if payload.status is 'OK'
 *
 * @param {Object} action Redux standard action
 * @return {boolean}
 */
function isValid( action ) {
	return !action.error && action.payload.status === 'OK';
}

function validity( state, action ) {
	if ( typeof state !== 'object' ) {
		state = defaultState;
	}
	switch ( action.type ) {
		case 'VALIDATE_AMOUNT':
			return objectAssign( {}, state, { amount: isValid( action ) } );
		default:
			return state;
	}
}

module.exports = validity;
