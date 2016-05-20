'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../lib/reducers/input_validation' ).inputValidation;

function newAction( value ) {
	return {
		type: 'VALIDATE_INPUT',
		payload: {
			contentName: 'testField',
			value: value,
			pattern: '^[a-z0-9]+$'
		}
	};
}

test( 'If invalid data was entered, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: false };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'ShouldFail' ) ), expectedState );
	t.end();
} );

test( 'If valid data was entered, validation returns valid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: true };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'abc123' ) ), expectedState );
	t.end();
} );

test( 'If valid data was changed to invalid data, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: false };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );
