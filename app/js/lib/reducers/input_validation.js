'use strict';

var objectAssign = require( 'object-assign' );

function inputIsValid( value, pattern ) {
	return new RegExp( pattern ).test( value );
}

function inputValidation( validationState, action ) {
	var newValidationState = objectAssign( {}, validationState ),
		bankDataIsValid;

	switch ( action.type ) {
		case 'VALIDATE_INPUT':
			newValidationState[ action.payload.contentName ] = inputIsValid( action.payload.value, action.payload.pattern );
			return newValidationState;
		case 'FINISH_BANK_DATA_VALIDATION':
			bankDataIsValid = action.payload.status !== 'ERR';
			newValidationState.iban = bankDataIsValid;
			newValidationState.bic = bankDataIsValid;
			newValidationState.account = bankDataIsValid;
			newValidationState.bankCode = bankDataIsValid;
			return newValidationState;
		default:
			return validationState;
	}
}

module.exports = {
	inputValidation: inputValidation
};
