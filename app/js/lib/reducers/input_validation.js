'use strict';

var _ = require( 'underscore' ),
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
		case 'MARK_EMPTY_FIELD_INVALID':
			_.each( action.payload.requiredFields, function ( key ) {
				if ( newValidationState[ key ].isValid === null ) {
					newValidationState[ key ].isValid = false;
				}
			} );
			_.each( action.payload.neutralFields, function ( key ) {
				newValidationState[ key ].isValid = null;
			} );
			return newValidationState;
		case 'FINISH_AMOUNT_VALIDATION':
			newValidationState.amount = { dataEntered: true, isValid: action.payload.status !== 'ERR' };
			return newValidationState;
		case 'FINISH_BANK_DATA_VALIDATION':
			bankDataIsValid = action.payload.status !== 'ERR';
			newValidationState.iban = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.bic = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.account = { dataEntered: true, isValid: bankDataIsValid };
			newValidationState.bankCode = { dataEntered: true, isValid: bankDataIsValid };
			return newValidationState;
		case 'FINISH_EMAIL_ADDRESS_VALIDATION':
			newValidationState.email = { dataEntered: true, isValid: action.payload.status !== 'ERR' };
			return newValidationState;
		case 'FINISH_ADDRESS_VALIDATION':
			_.forEach( newValidationState, function ( value, key ) {
				if ( newValidationState[ key ].dataEntered === true ) {
					newValidationState[ key ] = {
						dataEntered: true,
						isValid: newValidationState[ key ].isValid !== false && !( _.has( action.payload.messages, key ) )
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
