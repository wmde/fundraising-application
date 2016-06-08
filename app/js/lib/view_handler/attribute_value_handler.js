'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	AttributeValueHandler = {
		'post-code': null,
		city: null,
		email: null,

		update: function ( elements ) {
			var self = this;
			_.each( elements, function ( attributes, elementId ) {
				self.setAttributesForElement( self[ elementId ], attributes );
			} );
		},

		setAttributesForElement: function ( element, attributes ) {
			_.each( attributes, function ( value, key ) {
				element.attr( key, value );
			} );
		}
	};

module.exports = {
	createAttributeValueHandler: function ( postCodeElement, cityElement, emailElement ) {
		return objectAssign( Object.create( AttributeValueHandler ), {
			'post-code': postCodeElement,
			city: cityElement,
			email: emailElement
		} );
	}
};
