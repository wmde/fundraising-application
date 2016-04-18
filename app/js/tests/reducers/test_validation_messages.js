'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	validationMessages = require( '../../lib/reducers/validation_messages' );

function createValidPayload() {
	return {
		status: 'OK'
	};
}

function createInvalidAmountPayload() {
	return {
		status: 'ERR',
		message: 'there was an error'
	};
}

function createInvalidAddressPayload(  ) {
	return {
		status: 'ERR',
		messages: {
			firstName: 'too short',
			lastName: 'too long',
			postcode: 'invalid postcode'
		}
	};
}

test( 'FINISH_AMOUNT_VALIDATION with valid payload does not change state', function ( t ) {
	var beforeState = {};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createValidPayload() } ), beforeState );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION with valid payload does not change state', function ( t ) {
	var beforeState = {};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createValidPayload() } ), beforeState );
	t.end();
} );

test( 'FINISH_AMOUNT_VALIDATION with invalid payload does gets error message from payload', function ( t ) {
	var beforeState = {},
		expectedState = {
			amount: 'there was an error'
		};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createInvalidAmountPayload() } ), expectedState );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION with invalid payload sets error messages from payload', function ( t ) {
	var beforeState = {},
		expectedState = {
			firstName: 'too short',
			lastName: 'too long',
			postcode: 'invalid postcode'
		};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createInvalidAddressPayload() } ), expectedState );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION with different invalid payload changes error messages', function ( t ) {
	var beforeState = {
			firstName: 'offensive name',
			city: 'must not be empty'
		},
		expectedState = {
			firstName: 'too short',
			lastName: 'too long',
			postcode: 'invalid postcode'
		};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createInvalidAddressPayload() } ), expectedState );
	t.end();
} );

test( 'switching from invalid to valid amount removes error message', function ( t ) {
	var beforeState = {
			amount: 'there was an error'
		},
		expectedState = {};

	deepFreeze( beforeState );
	t.deepEqual( validationMessages( beforeState, { type: 'FINISH_AMOUNT_VALIDATION', payload: createValidPayload() } ), expectedState );
	t.end();
} );
