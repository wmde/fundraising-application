'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	validationMessages = require( '../../lib/reducers/validation_messages' );

function createValidPayload() {
	return {
		status: 'OK'
	};
}

function createInvalidPayload() {
	return {
		status: 'ERR',
		message: 'there was an error'
	};
}

test( 'FINISH_AMOUNT_VALIDATION with valid payload does not change state', function ( t ) {
	var beforeState = {};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createValidPayload() } ), beforeState );
	t.end();
} );

test( 'FINISH_AMOUNT_VALIDATION with invalid payload does gets error message from payload', function ( t ) {
	var beforeState = {},
		expectedState = {
			amount: 'there was an error'
		};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createInvalidPayload() } ), expectedState );
	t.end();
} );

test( 'switching from invalid to valid removes error message', function ( t ) {
	var beforeState = {
			amount: 'there was an error'
		},
		expectedState = {};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createValidPayload() } ), expectedState );
	t.end();
} );
