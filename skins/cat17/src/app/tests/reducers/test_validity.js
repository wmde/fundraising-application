'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	{ ValidationStates, Validity } = require( '../../lib/validation/validation_states' ),
	validity = require( '../../lib/reducers/validity' );

function createValidPayload() {
	return {
		status: ValidationStates.OK
	};
}

function createInvalidPayload() {
	return {
		status: ValidationStates.ERR,
		message: 'there was an error'
	};
}

function createIncompletePayload() {
	return {
		status: ValidationStates.INCOMPLETE
	};
}

test( 'Construction with off-type state sets default state', function ( t ) {
	t.deepEqual(
		validity(
			null,
			{ type: 'something', payload: {} }
		),
		{
			paymentData: null,
			address: null,
			bankData: null
		}
	);
	t.end();
} );

test( 'Construction with incomplete state merges default state', function ( t ) {
	t.deepEqual(
		validity(
			{ address: true },
			{ type: 'something', payload: {} }
		),
		{
			paymentData: null,
			address: true,
			bankData: null
		}
	);
	t.end();
} );

test( 'FINISH_PAYMENT_DATA_VALIDATION sets paymentData validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_PAYMENT_DATA_VALIDATION', payload: createValidPayload() } ).paymentData );
	t.notOk( validity( beforeState, {
		type: 'FINISH_PAYMENT_DATA_VALIDATION',
		payload: createInvalidPayload
	} ).paymentData );
	t.ok( validity( beforeState, {
		type: 'FINISH_PAYMENT_DATA_VALIDATION',
		payload: createIncompletePayload()
	} ).paymentData === null );
	t.end();
} );

test( 'FINISH_ADDRESS_VALIDATION sets amount validation state', function ( t ) {
	var beforeState = { amount: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createValidPayload() } ).address );
	t.notOk( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createInvalidPayload } ).address );
	t.ok( validity( beforeState, { type: 'FINISH_ADDRESS_VALIDATION', payload: createIncompletePayload() } ).address === Validity.INCOMPLETE );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION with BIC sets bank data validation state to valid', function ( t ) {
	var beforeState = { bankData: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, {
		type: 'FINISH_BANK_DATA_VALIDATION', payload: {
			status: ValidationStates.OK,
			iban: 'DE12500105170648489890',
			bic: 'INGDDEFFXXX'
		}
	} ).bankData );
	t.notOk( validity( beforeState, { type: 'FINISH_BANK_DATA_VALIDATION', payload: createInvalidPayload } ).bankData );
	t.ok( validity( beforeState, { type: 'FINISH_BANK_DATA_VALIDATION', payload: createIncompletePayload() } ).bankData === Validity.INCOMPLETE );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION without BIC does not set bank data validation state to invalid', function ( t ) {
	var beforeState = { bankData: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, {
		type: 'FINISH_BANK_DATA_VALIDATION', payload: {
			status: ValidationStates.OK,
			iban: 'AT022050302101023600'
		}
	} ).bankData );
	t.end();
} );
