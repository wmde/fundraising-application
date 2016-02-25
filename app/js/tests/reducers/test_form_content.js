'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formContent = require( '../../lib/reducers/form_content' );

test( 'SELECT_AMOUNT sets amount and isCustomAmount', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 5, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: 5 } } ), expectedState );
	t.end();

} );

test( 'SELECT_AMOUNT keeps amount if selected amount is null', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 99, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: null } } ), expectedState );
	t.end();

} );

test( 'INPUT_AMOUNT sets amount and isCustomAount', function ( t ) {
	var stateBefore = { amount: 5, isCustomAmount: false },
		expectedState = { amount: '42.23', isCustomAmount: true };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'INPUT_AMOUNT', payload: { amount: '42.23' } } ), expectedState );
	t.end();

} );

test( 'SELECT_PAYMENT_TYPE sets payment type', function ( t ) {
	var stateBefore = { paymentType: 'PPL' },
		expectedState = { paymentType: 'BEZ' };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_PAYMENT_TYPE', payload: { paymentType: 'BEZ' } } ), expectedState );
	t.end();

} );
