'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../../lib/reducers/input_validation' ).inputValidation;

function newInitializeValidationAction( violatedFields, initialValues ) {
	return {
		type: 'INITIALIZE_VALIDATION',
		payload: {
			violatedFields: violatedFields,
			initialValues: initialValues
		}
	};
}

test( 'Given violated fields, their validation state should change', function ( t ) {
	var stateBefore = {
			firstName: { dataEntered: false, isValid: null },
			email: { dataEntered: false, isValid: null }
		},
		expectedState = {
			firstName: { dataEntered: false, isValid: null },
			email: { dataEntered: true, isValid: false }
		};

	deepFreeze( stateBefore );
	t.deepEqual(
		inputValidation( stateBefore, newInitializeValidationAction( { email: 'Not valid' }, {} ) ),
		expectedState
	);
	t.end();
} );

test( 'Given initial values, their state should change, based on violated fields', function ( t ) {
	var stateBefore = {
			firstName: { dataEntered: false, isValid: null },
			lastName: { dataEntered: false, isValid: null },
			email: { dataEntered: false, isValid: null }
		},
		expectedState = {
			firstName: { dataEntered: false, isValid: null },
			lastName: { dataEntered: true, isValid: true },
			email: { dataEntered: true, isValid: false }
		};

	deepFreeze( stateBefore );
	t.deepEqual(
		inputValidation( stateBefore, newInitializeValidationAction(
			{ email: 'Wrong animal provider' },
			{ lastName: 'McGoatface', email: 'maaah@yougoatmail.com' }
		) ),
		expectedState
	);
	t.end();
} );

test( 'Unknown initial values are ignored', function ( t ) {
	var stateBefore = {
			firstName: { dataEntered: false, isValid: null },
			email: { dataEntered: false, isValid: null }
		},
		expectedState = {
			firstName: { dataEntered: false, isValid: null },
			email: { dataEntered: false, isValid: null }
		};

	deepFreeze( stateBefore );
	t.deepEqual(
		inputValidation( stateBefore, newInitializeValidationAction(
			{ unrelated: "Not enough sprockets" },
			{ unrelated: '1', superfluous: "Floo" }
		) ),
		expectedState
	);
	t.end();
} );
