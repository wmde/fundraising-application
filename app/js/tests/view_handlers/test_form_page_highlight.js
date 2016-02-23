'use strict';

var test = require( 'tape' ),
	formPageHighlightHandler = require( '../../lib/view_handler/form_page_highlight' ),
	ElementSpy = {
		selectedPage: '',
		addedClass: '',
		removedClass: '',
		filter: function ( pageName ) {
			var self = this;
			return { addClass: function ( className ) {
				self.selectedPage = pageName;
				self.addedClass = className;
			} };
		},
		removeClass: function ( className ) {
			this.removedClass = className;
		}
	};

test( 'When current page changes, highlight for page is set', function ( t ) {
	var pageState = {
			pages: [ 'pageOne', 'pageTwo' ],
			currentPage: 1
		},
		highlightElement = Object.create( ElementSpy ),
		handler = formPageHighlightHandler.createHandler( highlightElement );
	handler.update( pageState );
	t.equals( highlightElement.selectedPage, '#highlight-pageTwo' );
	t.equals( highlightElement.addedClass, 'active' );
	t.equals( highlightElement.removedClass, 'active' );
	t.end();

} );
