'use strict';

var test = require( 'tape' ),
	formPageVisibilityHandler = require( '../../lib/view_handler/form_page_visibility' ),
	pageSpy = {
		isVisible: false,
		show: function () { this.isVisible = true; },
		hide: function () { this.isVisible = false; }
	};

test( 'When current page changes, visibility jquery objects is altered', function ( t ) {
	var pageState = {
			pages: [ 'pageOne', 'pageTwo' ],
			currentPage: 1
		},
		firstPage = Object.create( pageSpy ),
		secondPage = Object.create( pageSpy ),
		handler = formPageVisibilityHandler.createHandler( {
			pageOne: firstPage,
			pageTwo: secondPage
		} );
	firstPage.show();
	handler.update( pageState );
	t.notOk( firstPage.isVisible, 'first page should be hidden' );
	t.ok( secondPage.isVisible, 'second page should be shown' );
	t.end();
} );
