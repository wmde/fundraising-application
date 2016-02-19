'use strict';

var test = require( 'tape' ),
	formPages = require( '../lib/form_pages' ),
	Promise = require( 'promise' ),
	pageSpy = {
		isVisible: false,
		validationResult: null,
		show: function () { this.isVisible = true; },
		hide: function () { this.isVisible = false; },
		validate: function () {
			var self = this;
			return new Promise( function ( resolve ) {
				return resolve( self.validationResult );
			} );
		}
	};

test( 'When form is created, first page is shown', function ( t ) {
	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy );

	formPages.createFormwithPages( [ firstPage, secondPage ] );
	t.ok( firstPage.isVisible, 'First page is displayed' );
	t.notOk( secondPage.isVisible, 'Second page is hidden' );
	t.end();
} );

test( 'Form shows and hides pages correctly', function ( t ) {
	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );

	form.displayPage( 1 );
	t.notOk( firstPage.isVisible, 'First page is hidden' );
	t.ok( secondPage.isVisible, 'Second page is displayed' );
	t.end();
} );

test( 'Form page without validation returns null promise.', function ( t ) {
	var page = formPages.createFormPage( '#dummy' );

	t.plan( 1 );
	Promise.resolve( page.validate() ).then( function ( validationResult ) {
		t.equals( validationResult, null, 'empty validation must return null' );
	} );

} );

test( 'Form page with validation function returns promise with validation result.', function ( t ) {
	var page = formPages.createFormPage( '#dummy', function () {
			return 'validation ok';
		} );

	t.plan( 1 );
	Promise.resolve( page.validate() ).then( function ( validationResult ) {
		t.equals( validationResult, 'validation ok', 'validation result must me returned' );
	} );
} );

test( 'nextPage switches to next page when validation status is ok', function ( t ) {

	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );

	firstPage.validationResult = { status: 'OK' };
	t.plan( 2 );
	Promise.resolve( form.nextPage() ) .then( function () {
		t.notOk( firstPage.isVisible, 'First page is hidden' );
		t.ok( secondPage.isVisible, 'Second page is displayed' );
	} );
} );

test( 'nextPage stays on page when validation status is not ok', function ( t ) {
	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );

	firstPage.validationResult = { status: 'ERR' };
	t.plan( 2 );
	Promise.resolve( form.nextPage() ) .then( function () {
		t.ok( firstPage.isVisible, 'First page is displayed' );
		t.notOk( secondPage.isVisible, 'Second page is hidden' );
	} );
} );

test( 'nextPage generates error when there are no more pages', function ( t ) {
	var firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		form = formPages.createFormwithPages( [ firstPage, secondPage ] );

	secondPage.validationResult = { status: 'OK' };
	form.displayPage( 1 );
	Promise.resolve( form.nextPage() ) .then( function () {
		t.fail( 'nextPage should throw an error' );
		t.end();
	} ).catch( function () {
		t.notOk( firstPage.isVisible, 'First page is displayed' );
		t.ok( secondPage.isVisible, 'Second page is hidden' );
		t.end();
	} );
} );
