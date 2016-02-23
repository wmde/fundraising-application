'use strict';

var objectAsssign = require( 'object-assign' ),
	EnabledWhenValidHandler = {
		elements: null,
		update: function ( state ) {
			if ( typeof state  !== 'undefined' && state.isValid && state.isValidated ) {
				this.elements.prop( 'disabled', false );
			} else {
				this.elements.prop( 'disabled', true );
			}
		}
	},
	createHandler = function ( $elements ) {
		return objectAsssign( Object.create( EnabledWhenValidHandler ), {
			elements: $elements
		} );
	};

module.exports = {
	createHandler: createHandler
};
