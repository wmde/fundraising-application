'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	_ = require( 'underscore' ),
	userInteractionCount = require( '../../lib/reducers/user_interaction_count' );

test( 'Changes in form values add to interaction count', function ( t ) {
	var beforeState = { count: 0 },
		expectedState = { count: 1 };

	deepFreeze( beforeState );
	_.each( [ 'CHANGE_CONTENT', 'SELECT_AMOUNT', 'INPUT_AMOUNT' ], function ( action ) {
		t.deepEqual(
			userInteractionCount( beforeState, { type: action, payload: {} } ),
			expectedState, 'Form changes should increase counter'
		);
	} );
	t.end();
} );

test( 'User interaction adds to interaction count', function ( t ) {
	var beforeState = { count: 0 },
		expectedState = { count: 1 };

	deepFreeze( beforeState );
	t.deepEqual(
		userInteractionCount( beforeState, { type: 'USER_INTERACTION' } ),
		expectedState, 'User Interaction should increase count'
	);
	t.end();
} );

test( 'Interaction count counts up for multiple events', function ( t ) {
		var action = { type: 'CHANGE_CONTENT', payload: {} },
			beforeState = { count: 0 },
			expectedState = { count: 3 };

	deepFreeze( beforeState );
	t.deepEqual(
		userInteractionCount( userInteractionCount( userInteractionCount( beforeState, action ), action ), action ),
		expectedState, 'Form changes should be additive'
	);
	t.end();
} );

test( 'Other actions leave interaction count untouched', function ( t ) {
	var beforeState = { count: 0 };

	deepFreeze( beforeState );
	t.equal(
		userInteractionCount( beforeState, { type: 'TESTING' } ),
		beforeState, 'Count should not increase'
	);
	t.end();
} );

test( 'State is initialized', function ( t ) {
	t.deepEqual(
		userInteractionCount( undefined, { type: 'TESTING' } ),
		{ count: 0 }
	);
	t.end();
} );