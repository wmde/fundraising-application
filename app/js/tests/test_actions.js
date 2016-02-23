'use strict';

var test = require( 'tape' ),
	actions = require( '../lib/actions' );

test( 'addPage returns action object', function ( t ) {
	var expectedAction = {
		type: 'ADD_PAGE',
		payload: { name: 'firstPage' }
	};
	t.deepEqual( actions.addPage( 'firstPage' ), expectedAction );
	t.end();
} );

test( 'nextPage returns action object', function ( t ) {
	var expectedAction = {
		type: 'NEXT_PAGE'
	};
	t.deepEqual( actions.nextPage(), expectedAction );
	t.end();
} );

test( 'storeValidationResult returns action object', function ( t ) {
	var expectedAction = {
		type: 'VALIDATION_RESULT',
		payload: { isValid: true }
	};
	t.deepEqual( actions.storeValidationResult( true ), expectedAction );
	t.end();
} );
