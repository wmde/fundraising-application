'use strict';

var objectAsssign = require( 'object-assign' ),
	FormPageVisibilityHandler = {
		sections: {},
		update: function ( pageState ) {
			var self = this;
			pageState.pages.forEach( function ( page, index ) {
				if ( index === pageState.currentPage ) {
					self.sections[ page ].show();
				} else {
					self.sections[ page ].hide();
				}
			} );
		}
	},
	createHandler = function ( $sections ) {
		return objectAsssign( Object.create( FormPageVisibilityHandler ), {
			sections: $sections
		} );
	};

module.exports = {
	createHandler: createHandler
};
