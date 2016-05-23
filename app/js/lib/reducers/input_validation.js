'use strict';

var _ = require( 'lodash' ),
	objectAssign = require( 'object-assign' );

function inputIsValid( value, pattern ) {
	return new RegExp( pattern ).test( value );
}

function inputValidation( validationState, action ) {
	var newValidationState = objectAssign( {}, validationState ),
		bankDataIsValid;

	switch ( action.type ) {
		case 'VALIDATE_INPUT':
			if ( validationState[ action.payload.contentName ].dataEntered === false && action.payload.value === '' ) {
				return validationState;
			}

			newValidationState[ action.payload.contentName ] = {
				dataEntered: true,
				isValid: inputIsValid( action.payload.value, action.payload.pattern )
			};
			return newValidationState;
		case 'FINISH_BANK_DATA_VALIDATION':
			bankDataIsValid = action.payload.status !== 'ERR';
			newValidationState.iban = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.bic = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.account = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.bankCode = { dataEntered: true, isValid: bankDataIsValid };
			return newValidationState;
		case 'FINISH_ADDRESS_VALIDATION':
			_.forEach( newValidationState, function ( value, key ) {
				if ( newValidationState[ key ].dataEntered === true ) {
					newValidationState[ key ] = {
						dataEntered: true,
						isValid: !( _.has( action.payload.messages, key ) )
					};
				}
			} );
			return newValidationState;
		default:
			return validationState;
	}
}

module.exports = {
	inputValidation: inputValidation
};
