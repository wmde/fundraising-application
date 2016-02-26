'use strict';

var objectAsssign = require( 'object-assign' ),
	/**
	 * Handler to add the "active" class to an element and remove it from the others
	 *
	 */
	FormPageHighlightHandler = {
		elements: null,
		highlightClass: '',
		selectorPrefix: '',
		update: function ( state ) {
			var currentPage = state.pages[ state.currentPage ];
			this.elements.removeClass( this.highlightClass );
			this.elements.filter( this.selectorPrefix + currentPage ).addClass( this.highlightClass );
		}
	},
	createHandler = function ( $elements, highlightClass, selectorPrefix ) {
		return objectAsssign( Object.create( FormPageHighlightHandler ), {
			elements: $elements,
			highlightClass: highlightClass || 'active',
			selectorPrefix: selectorPrefix || '#highlight-'
		} );
	};

module.exports = {
	createHandler: createHandler
};
