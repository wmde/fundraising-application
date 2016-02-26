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

test( 'newStoreValidationResultAction returns action object', function ( t ) {
	var expectedAction = {
		type: 'VALIDATION_RESULT',
		payload: { isValid: true }
	};
	t.deepEqual( actions.newStoreValidationResultAction( true ), expectedAction );
	t.end();
} );
