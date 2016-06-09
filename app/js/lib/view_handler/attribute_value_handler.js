'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	CountrySpecificAttributesHandler = {
		postCode: null,
		city: null,
		email: null,

		update: function ( countrySpecificAttributes ) {
			this.setAttributesForElement( this.postCode, countrySpecificAttributes[ 'post-code' ] );
			this.setAttributesForElement( this.city, countrySpecificAttributes.city );
			this.setAttributesForElement( this.email, countrySpecificAttributes.email );
		},

		setAttributesForElement: function ( element, attributes ) {
			_.each( attributes, function ( value, key ) {
				element.attr( key, value );
			} );
		}
	};

module.exports = {
	createCountrySpecificAttributesHandler: function ( postCodeElement, cityElement, emailElement ) {
		return objectAssign( Object.create( CountrySpecificAttributesHandler ), {
			postCode: postCodeElement,
			city: cityElement,
			email: emailElement
		} );
	}
};
