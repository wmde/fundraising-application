'use strict';

function validity( state, action ) {
	switch ( action.type ) {
		case 'VALIDATION_RESULT':
			return {
				isValid: !!action.payload.isValid,
				isValidated: true
			};
		default:
			if ( typeof state === 'undefined' ) {
				return {
					isValid: true,
					isValidated: false
				};
			}
			return state;
	}
}

module.exports = validity;
