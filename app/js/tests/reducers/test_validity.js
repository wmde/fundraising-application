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

test( 'FINISH_BANK_DATA_VALIDATION with BIC sets bank data validation state to valid', function ( t ) {
	var beforeState = { bankData: null };

	deepFreeze( beforeState );
	t.ok( validity( beforeState, { type: 'FINISH_BANK_DATA_VALIDATION', payload: {
		status: 'OK',
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		bankCode: '50010517',
		accountNumber: '064847930'
	} } ).bankData );
	t.notOk( validity( beforeState, { type: 'FINISH_BANK_DATA_VALIDATION', payload: createInvalidPayload } ).bankData );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION without BIC sets bank data validation state to invalid', function ( t ) {
	var beforeState = { bankData: null };

	deepFreeze( beforeState );
	t.notOk( validity( beforeState, { type: 'FINISH_BANK_DATA_VALIDATION', payload: {
		status: 'OK',
		iban: 'AT022050302101023600'
	} } ).bankData );
	t.end();
} );
