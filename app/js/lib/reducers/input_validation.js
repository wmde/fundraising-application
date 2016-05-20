'use strict';

var objectAssign = require( 'object-assign' );

function inputIsValid( value, pattern ) {
	return new RegExp( pattern ).test( value );
}

function inputValidation( validationState, action ) {
	var newValidationState = objectAssign( {}, validationState );

	switch ( action.type ) {
		case 'VALIDATE_INPUT':
			newValidationState[ action.payload.contentName ] = inputIsValid( action.payload.value, action.payload.pattern );
			return newValidationState;
		default:
			return validationState;
	}
}

module.exports = {
	inputValidation: inputValidation
};
