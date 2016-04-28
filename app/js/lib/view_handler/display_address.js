'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),
	AddressDisplayHandler = {
		fullName: null,
		street: null,
		postcode: null,
		city: null,
		country: null,
		email: null,
		update: function ( formContent ) {
			var name;
			if ( formContent.addressType === 'person' ) {
				name = [
					formContent.salutation,
					formContent.title,
					formContent.firstName,
					formContent.lastName
				].join( ' ' );
			} else if ( formContent.addressType === 'firma' ) {
				name = formContent.company;
			} else {
				name = '';
			}
			this.fullName.text( name );
			this.street.text( formContent.street );
			this.postcode.text( formContent.postcode );
			this.city.text( formContent.city );
			this.country.text( formContent.country );
			this.email.text( formContent.email );
		}
	};

module.exports = {
	createDisplayAddressHandler: function ( elementConfig ) {
		var expectedConfigProperties = [
				'fullName',
				'street',
				'postcode',
				'city',
				'country',
				'email'
			],
			unconfiguredElements = _.difference( expectedConfigProperties, _.keys( elementConfig ) );
		if ( unconfiguredElements.length > 0 ) {
			throw new Error( 'The following elements were not configured: ' + unconfiguredElements.join( ', ' ) );
		}
		return objectAssign( Object.create( AddressDisplayHandler ), elementConfig );
	}
};
