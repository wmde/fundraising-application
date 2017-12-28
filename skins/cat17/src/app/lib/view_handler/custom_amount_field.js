'use strict';

var objectAssign = require( 'object-assign' ),
	CustomAmountField = {
		field: null,
		update: function () {
		}
	};

module.exports = {
	createCustomAmountField: function ( field ) {
		field.on( 'focus focusout', function ( event ) {
			jQuery( this ).parent( '.wrap-amount-typed' ).toggleClass( 'focused', event.type === 'focus' );
		} );

		return objectAssign( Object.create( CustomAmountField ), {
			field: field
		} );
	}
};
