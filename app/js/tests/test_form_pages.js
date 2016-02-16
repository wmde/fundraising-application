var test = require( 'tape' ),
	formPages = require( '../lib/form_pages' );

test( 'Form shows and hides pages correctly', function ( t ) {
	'use strict';

	var pageSpy = {
			display: false,
			show: function () { this.display = true; },
			hide: function () { this.display = false; }
		},
		firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( { first: firstPage, second: secondPage } );
	form.displayPage( 'first' );
	t.ok( firstPage.display, 'First page is not displayed' );
	t.notOk( secondPage.display, 'Second page is not hidden' );
	t.end();
} );
