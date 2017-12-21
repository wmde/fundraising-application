'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../../lib/reducers/input_validation' ).inputValidation;

function newInitializeValidationAction( violatedFields, initialValidationResult, initialValues ) {
	return {
		type: 'INITIALIZE_VALIDATION',
		payload: {
			violatedFields: violatedFields,
			initialValidationResult: initialValidationResult,
			initialValues: initialValues
		}
	}
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
		inputValidation( stateBefore, newInitializeValidationAction( { email: 'Not valid' }, {}, {} ) ),
		expectedState
	);
	t.end();
} );