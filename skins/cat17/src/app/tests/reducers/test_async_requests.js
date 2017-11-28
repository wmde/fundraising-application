'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	asyncRequests = require( '../../lib/reducers/async_requests' );

test( 'there are no asynchronous processes when starting out', function ( t ) {
	var expectedState = { isValidating: false, runningValidations: 0 },
		action = { type: 'DUMMY_ACTION' };

	t.deepEqual( asyncRequests( undefined, action ), expectedState );
	t.end();
} );

test( 'BEGIN_XXX_VALIDATION actions increase counter and change isValidating ', function ( t ) {

	var stateBefore = { isValidating: false, runningValidations: 0 },
		expectedStateAfterFirstAction = { isValidating: true, runningValidations: 1 },
		expectedStateAfterSecondAction = { isValidating: true, runningValidations: 2 },
		action = { type: 'BEGIN_PAYMENT_DATA_VALIDATION', payload: { amount: '25,00', paymentType: 'BEZ' } };

	deepFreeze( stateBefore );
	t.deepEqual( asyncRequests( stateBefore, action ), expectedStateAfterFirstAction );
	deepFreeze( expectedStateAfterFirstAction );
	t.deepEqual( asyncRequests( expectedStateAfterFirstAction, action ), expectedStateAfterSecondAction );
	t.end();

} );

test( 'FINISH_XXX_VALIDATION actions decrease counter and change isValidating ', function ( t ) {

	var initialState = { isValidating: false, runningValidations: 0 },
		expectedStateAfterFirstAction = { isValidating: true, runningValidations: 1 },
		expectedStateAfterSecondAction = { isValidating: false, runningValidations: 0 },
		beginAction = { type: 'BEGIN_PAYMENT_DATA_VALIDATION', payload: { amount: '25,00', paymentType: 'BEZ' } },
		finishAction = { type: 'FINISH_PAYMENT_DATA_VALIDATION', payload: { status: 'OK' } },
		stateBefore, stateAfterOneFinishAction;

	stateBefore = asyncRequests( asyncRequests( initialState, beginAction ), beginAction );
	deepFreeze( stateBefore );

	stateAfterOneFinishAction = asyncRequests( stateBefore, finishAction );
	t.deepEqual( stateAfterOneFinishAction, expectedStateAfterFirstAction );
	deepFreeze( expectedStateAfterFirstAction );
	t.deepEqual( asyncRequests( expectedStateAfterFirstAction, finishAction ), expectedStateAfterSecondAction );
	t.end();

} );

test( 'Additional FINISH_XXX_VALIDATION actions do not modify state', function ( t ) {

	var initialState = { isValidating: false, runningValidations: 0 },
		action = { type: 'FINISH_PAYMENT_DATA_VALIDATION', payload: { status: 'OK' } };

	deepFreeze( initialState );

	t.equal( asyncRequests( initialState, action ), initialState  );

	t.end();

} );
