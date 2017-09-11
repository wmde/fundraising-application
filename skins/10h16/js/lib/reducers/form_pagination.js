'use strict';

function currentPageIsLastPage( paginationState ) {
	return paginationState.currentPage + 1 < paginationState.pages.length;
}

function currentPageIsFirstPage( paginationState ) {
	return paginationState.currentPage - 1 < 0;
}

function formPagination( paginationState, action ) {
	var newPaginationState;
	if ( typeof paginationState !== 'object' ) {
		newPaginationState = {
			pages: [],
			currentPage: -1
		};
	} else {
		// TODO use Immutable.js instead?
		newPaginationState = {
			pages: paginationState.pages,
			currentPage: paginationState.currentPage
		};
	}
	switch ( action.type ) {
		case 'ADD_PAGE':
			newPaginationState.pages = newPaginationState.pages.concat( action.payload.name );
			if ( newPaginationState.currentPage === -1 ) {
				newPaginationState.currentPage = 0;
			}
			return newPaginationState;
		case 'NEXT_PAGE':
			if ( currentPageIsLastPage( newPaginationState )  ) {
				newPaginationState.currentPage += 1;
			}
			return newPaginationState;
		case 'PREVIOUS_PAGE':
			if ( !currentPageIsFirstPage( newPaginationState ) ) {
				newPaginationState.currentPage -= 1;
			}
			return newPaginationState;
		default:
			return newPaginationState;
	}
}

module.exports = formPagination;
