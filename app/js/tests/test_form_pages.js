var test = require( 'tape' ),
	formPages = require( '../lib/form_pages' ),
	pageSpy = {
		display: false,
		show: function () { this.display = true; },
		hide: function () { this.display = false; }
	};

test( 'When form is created, first page is shown', function ( t ) {
	'use strict';

	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );
	t.ok( firstPage.display, 'First page is  displayed' );
	t.notOk( secondPage.display, 'Second page is hidden' );
	t.end();
} );


test( 'Form shows and hides pages correctly', function ( t ) {
	'use strict';

	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );
	form.displayPage( 1 );
	t.notOk( firstPage.display, 'First page is hidden' );
	t.ok( secondPage.display, 'Second page is displayed' );
	t.end();
} );

