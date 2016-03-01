'use strict';

var test = require( 'tape' ),
	actions = require( '../lib/actions' );

test( 'newAddPageAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'ADD_PAGE',
		payload: { name: 'firstPage' }
	};
	t.deepEqual( actions.newAddPageAction( 'firstPage' ), expectedAction );
	t.end();
} );

test( 'newNextPageAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'NEXT_PAGE'
	};
	t.deepEqual( actions.newNextPageAction(), expectedAction );
	t.end();
} );

test( 'newValidateAmountAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'VALIDATE_AMOUNT',
		payload: { status: 'OK' }
	};
	t.deepEqual( actions.newValidateAmountAction( { status: 'OK' } ), expectedAction );
	t.end();
} );

test( 'newSelectAmountAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'SELECT_AMOUNT',
		payload: { amount: '1,99' }
	};
	t.deepEqual( actions.newSelectAmountAction( '1,99' ), expectedAction );
	t.end();
} );

test( 'newInputAmountAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'INPUT_AMOUNT',
		payload: { amount: '1,99' }
	};
	t.deepEqual( actions.newInputAmountAction( '1,99' ), expectedAction );
	t.end();
} );

test( 'newSelectPaymentTypeAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'SELECT_PAYMENT_TYPE',
		payload: { paymentType: 'BTC' }
	};
	t.deepEqual( actions.newSelectPaymentTypeAction( 'BTC' ), expectedAction );
	t.end();
} );
