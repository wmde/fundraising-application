'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formPagination = require( '../../lib/reducers/form_pagination' );

test( 'NEXT_PAGE increases current page', function ( t ) {
	var stateBefore = { pages: [ 'foo', 'bar' ], currentPage: 0 },
		expectedState = { pages: [ 'foo', 'bar' ], currentPage: 1 };

	deepFreeze( stateBefore );
	t.deepEqual( formPagination( stateBefore, { type: 'NEXT_PAGE' } ), expectedState );
	t.end();
} );

test( 'When current page is the last, NEXT_PAGE does not change current page', function ( t ) {
	var stateBefore = { pages: [ 'foo', 'bar' ], currentPage: 1 },
		expectedState = { pages: [ 'foo', 'bar' ], currentPage: 1 };

	deepFreeze( stateBefore );
	t.deepEqual( formPagination( stateBefore, { type: 'NEXT_PAGE' } ), expectedState );
	t.end();
} );

test( 'When there are no pages, current page is initialized with negativeValue', function ( t ) {
	var expectedState = { pages: [], currentPage: -1 };

	t.deepEqual( formPagination( undefined, { type: 'NEXT_PAGE' } ), expectedState );
	t.end();
} );

test( 'When first page is added, current page is set to first page and does not change afterwards', function ( t ) {
	var stateBefore = { pages: [], currentPage: -1 },
		expectedState = { pages: [ 'foo', 'bar' ], currentPage: 0 },
		currentState;

	deepFreeze( stateBefore );
	currentState = formPagination( stateBefore, { type: 'ADD_PAGE', payload: { name: 'foo' } } );
	currentState = formPagination( currentState, { type: 'ADD_PAGE', payload: { name: 'bar' } } );
	t.deepEqual( currentState, expectedState );
	t.end();
} );
