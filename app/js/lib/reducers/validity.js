'use strict';

function validity( state, action ) {
	if ( typeof state === 'undefined' ) {
		return {
			isValid: true,
			isValidated: false
		};
	}
	switch ( action.type ) {
		case 'VALIDATION_RESULT':
			return {
				isValid: !!action.payload.isValid,
				isValidated: true
			};
		default:
			return state;
	}
}

module.exports = validity;
