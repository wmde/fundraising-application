'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../../lib/reducers/input_validation' ).inputValidation;

// Test pattern validation functions with VALIDATE_INPUT

function newAction( value, optionalField ) {
	return {
		type: 'VALIDATE_INPUT',
		payload: {
			contentName: 'testField',
			value: value,
			pattern: '^[a-z0-9]+$',
			optionalField: optionalField || false
		}
	};
}

test( 'If no data was entered yet, validation returns previous state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: false, isValid: null } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );

test( 'If invalid data was entered, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: true, isValid: false } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'ShouldFail' ) ), expectedState );
	t.end();
} );

test( 'If valid data was entered, validation returns valid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: true, isValid: true } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'abc123' ) ), expectedState );
	t.end();
} );

test( 'If valid data was changed to invalid data, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: true, isValid: true } },
		expectedState = { testField: { dataEntered: true, isValid: false } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );

test( 'If valid data was changed to empty data on optional field, validation returns empty state', function ( t ) {
	var stateBefore = { testField: { dataEntered: true, isValid: true } },
		expectedState = { testField: { dataEntered: false, isValid: null } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '', true ) ), expectedState );
	t.end();
} );
