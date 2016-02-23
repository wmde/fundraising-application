'use strict';

var formPage = require( './form_pages' );

function currentPage( page, pageLength, action ) {
	var defaultPage = page || ( pageLength > 0 ? 0 : -1 );
	switch ( action.type ) {
		case 'ADD_PAGE':
			if ( defaultPage === -1 ) {
				return 0;
			} else {
				return defaultPage;
			}
			break;
		case 'NEXT_PAGE':
			return pageLength > defaultPage + 1 ? defaultPage + 1 : defaultPage ;
		default:
			return defaultPage;
	}
}

function formPagination( pageState, action ) {
	var defaultPageState = pageState || {},
		pages = defaultPageState.pages || [];
	return {
		pages: formPage( defaultPageState.pages, action ),
		currentPage: currentPage( defaultPageState.currentPage, pages.length, action )
	};
}

module.exports = formPagination;
