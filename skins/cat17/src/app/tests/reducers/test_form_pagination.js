'use strict';

var test = require( 'tape-catch' ),
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

test( 'PREVIOUS_PAGE decreases current page', function ( t ) {
	var stateBefore = { pages: [ 'foo', 'bar' ], currentPage: 1 },
		expectedState = { pages: [ 'foo', 'bar' ], currentPage: 0 };

	deepFreeze( stateBefore );
	t.deepEqual( formPagination( stateBefore, { type: 'PREVIOUS_PAGE' } ), expectedState );
	t.end();
} );

test( 'When current page is the first, PREVIOUS_PAGE does not change current page', function ( t ) {
	var stateBefore = { pages: [ 'foo', 'bar' ], currentPage: 0 },
		expectedState = { pages: [ 'foo', 'bar' ], currentPage: 0 };

	deepFreeze( stateBefore );
	t.deepEqual( formPagination( stateBefore, { type: 'PREVIOUS_PAGE' } ), expectedState );
	t.end();
} );

test( 'When there are no pages, current page is initialized with negativeValue', function ( t ) {
	var expectedState = { pages: [], currentPage: -1 };

	t.deepEqual( formPagination( undefined, { type: 'NEXT_PAGE' } ), expectedState );
	t.end();
} );

test( 'When first page is added, current page is set to first page and does not change afterwards', function ( t ) {
	var firstPageState,
		secondPageState;

	firstPageState = formPagination( undefined, { type: 'ADD_PAGE', payload: { name: 'foo' } } );
	deepFreeze( firstPageState );
	secondPageState = formPagination( firstPageState, { type: 'ADD_PAGE', payload: { name: 'bar' } } );
	t.equal( secondPageState.currentPage, 0 );
	t.end();
} );

test( 'ADD_PAGE adds page names', function ( t ) {
	var firstPageState,
		secondPageState;

	firstPageState = formPagination( undefined, { type: 'ADD_PAGE', payload: { name: 'foo' } } );
	deepFreeze( firstPageState );
	secondPageState = formPagination( firstPageState, { type: 'ADD_PAGE', payload: { name: 'bar' } } );
	t.deepEqual( firstPageState.pages, [ 'foo' ] );
	t.deepEqual( secondPageState.pages, [ 'foo', 'bar' ] );
	t.end();
} );
