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

test( 'FINISH_PAYMENT_DATA_VALIDATION sets paymentData validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_PAYMENT_DATA_VALIDATION', payload: createValidPayload() } ).paymentData );
	t.notOk( validity( beforeState, { type: 'FINISH_PAYMENT_DATA_VALIDATION', payload: createInvalidPayload } ).paymentData );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION sets amount validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createValidPayload() } ).address );
	t.notOk( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createInvalidPayload } ).address );
	t.end();
} );

