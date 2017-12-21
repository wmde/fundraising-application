'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../../lib/reducers/input_validation' ).inputValidation;

// Test Address data validation

function newAddressValidationAction( returnedStatus ) {
	return {
		type: 'FINISH_ADDRESS_VALIDATION',
		payload: {
			status: returnedStatus
		}
	};
}

test( 'If address data validation is successful, related fields retrieve valid status', function ( t ) {
	var stateBefore = {
			firstName: { dataEntered: true, isValid: null },
			lastName: { dataEntered: true, isValid: null },
			street: { dataEntered: true, isValid: null },
			city: { dataEntered: true, isValid: null },
			postcode: { dataEntered: true, isValid: null },
			email: { dataEntered: true, isValid: null },
			iAmUnrelated: { dataEntered: true, isValid: null }
		},
		expectedState = {
			firstName: { dataEntered: true, isValid: true },
			lastName: { dataEntered: true, isValid: true },
			street: { dataEntered: true, isValid: true },
			city: { dataEntered: true, isValid: true },
			postcode: { dataEntered: true, isValid: true },
			email: { dataEntered: true, isValid: true },
			iAmUnrelated: { dataEntered: true, isValid: null }
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAddressValidationAction( 'OK' ) ), expectedState );
	t.end();
} );

test( 'If address data validation is successful, related invalid fields remain invalid', function ( t ) {
	var stateBefore = {
			postcode: { dataEntered: true, isValid: false },
			city: { dataEntered: true, isValid: true },
			iAmUnrelated: { dataEntered: true, isValid: null }
		},
		expectedState = {
			postcode: { dataEntered: true, isValid: false },
			city: { dataEntered: true, isValid: true },
			iAmUnrelated: { dataEntered: true, isValid: null }
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAddressValidationAction( 'OK' ) ), expectedState );
	t.end();
} );
