'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	validity = require( '../../lib/reducers/validity' );

test( 'initial state is valid and unvalidated', function ( t ) {
	var expectedState = { isValid: true, isValidated: false };

	t.deepEqual( validity( undefined, { type: 'UNSUPPORTED_ACTION' } ), expectedState );
	t.end();
} );

test( 'VALIDATION_RESULT returns new valid and validated state', function ( t ) {
	var beforeState = { isValid: true, isValidated: false },
		expectedValidState = { isValid: true, isValidated: true },
		expectedInvalidState = { isValid: false, isValidated: true };

	deepFreeze( beforeState );
	t.deepEqual( validity( beforeState, { type: 'VALIDATION_RESULT', payload: { isValid: true } } ), expectedValidState );
	t.deepEqual( validity( beforeState, { type: 'VALIDATION_RESULT', payload: { isValid: false } } ), expectedInvalidState );
	t.end();
} );
