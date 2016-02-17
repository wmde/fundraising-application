var test = require( 'tape' ),
	formPages = require( '../lib/form_pages' ),
	Promise = require( 'promise' ),
	pageSpy = {
		display: false,
		show: function () { this.display = true; },
		hide: function () { this.display = false; }
	};

test( 'When form is created, first page is shown', function ( t ) {
	'use strict';

	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy );
	formPages.createFormwithPages( [ firstPage, secondPage ] );
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

test( 'Form page without validation returns null promise.', function ( t ) {
	'use strict';
	var page = formPages.createFormPage( '#dummy' );
	t.plan( 1 );
	Promise.resolve( page.validate() ).then( function ( validationResult ) {
		t.equals( validationResult, null, 'empty validation must return null' );
	} );

} );

test( 'Form page with validation function returns promise with validation result.', function ( t ) {
	'use strict';
	var page = formPages.createFormPage( '#dummy', function () {
		return 'validation ok';
	} );
	t.plan( 1 );
	Promise.resolve( page.validate() ).then( function ( validationResult ) {
		t.equals( validationResult, 'validation ok', 'validation result must me returned' );
	} );
} );

