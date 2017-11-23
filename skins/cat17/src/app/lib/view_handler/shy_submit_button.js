'use strict';

/**
 * @todo For semantic DOM and intuitive behavior consider using disabled attribute and corresponding CSS
 */
var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	ShySubmitButton = {
		buttons: null,
		update: function ( validity ) {
			this.buttons.toggleClass( 'btn-unactive', _.contains( validity, false ) );
		}
	};

module.exports = {
	createShySubmitButtonHandler: function ( buttons ) {
		return objectAssign( Object.create( ShySubmitButton ), {
			buttons: buttons
		} );
	}
};
