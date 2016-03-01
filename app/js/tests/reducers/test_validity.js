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
		messages: [ 'there was an error' ]
	};
}

test( 'VALIDATE_AMOUNT returns validated amount', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'VALIDATE_AMOUNT', payload: createValidPayload() } ).amount );
	t.notOk( validity( beforeState, { type: 'VALIDATION_RESULT', payload: createInvalidPayload } ).amount );
	t.end();
} );
