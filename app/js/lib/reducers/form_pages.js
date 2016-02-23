'use strict';

/**
 *
 * @param {Array} pages
 * @param {Object} action Redux standard action
 * @return {Array}
 */
function formPage( pages, action ) {
	pages = pages || [];
	switch ( action.type ) {
		case 'ADD_PAGE':
			return pages.concat( action.payload.name );
		default:
			return pages;
	}

}

module.exports = formPage;
