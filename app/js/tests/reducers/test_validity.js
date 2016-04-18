'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	validity = require( '../../lib/reducers/validity' );

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

test( 'FINISH_AMOUNT_VALIDATION sets amount validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createValidPayload() } ).amount );
	t.notOk( validity( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createInvalidPayload } ).amount );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION sets amount validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createValidPayload() } ).address );
	t.notOk( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createInvalidPayload } ).address );
	t.end();
} );

