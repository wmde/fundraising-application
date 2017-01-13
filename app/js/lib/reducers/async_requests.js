'use strict';

module.exports = function ( state, action ) {
	if ( typeof state === "undefined" ) {
		state = { isValidating: false, runningValidations: 0 };
	}
	switch ( action.type ) {
		case 'BEGIN_BANK_DATA_VALIDATION':
		case 'BEGIN_EMAIL_ADDRESS_VALIDATION':
		case 'BEGIN_ADDRESS_VALIDATION':
		case 'BEGIN_PAYMENT_DATA_VALIDATION':
			return { isValidating: true, runningValidations: state.runningValidations + 1 };
		case 'FINISH_BANK_DATA_VALIDATION':
		case 'FINISH_EMAIL_ADDRESS_VALIDATION':
		case 'FINISH_ADDRESS_VALIDATION':
		case 'FINISH_PAYMENT_DATA_VALIDATION':
			if ( state.runningValidations === 0 ) {
				return state;
			}
			return {
				isValidating: state.runningValidations > 1,
				runningValidations: state.runningValidations - 1
			};
	}
	return state;
};