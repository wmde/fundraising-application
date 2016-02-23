'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formPages = require( '../../lib/reducers/form_pages' );

test( 'ADD_PAGE adds form page data', function ( t ) {
	var pagesBefore = [],
		expectedPages = [ 'payment' ];

	deepFreeze( pagesBefore );
	t.deepEqual( formPages( pagesBefore, { type: 'ADD_PAGE', payload: { name: 'payment' } } ), expectedPages );
	t.end();
} );

test( 'unknown actions leave pages unchanged', function ( t ) {
	var pagesBefore =  [ 'payment', 'personal-data' ];

	deepFreeze( pagesBefore );
	t.equal( formPages( pagesBefore, { type: 'UNSUPPORTED' } ), pagesBefore );
	t.end();
} );
