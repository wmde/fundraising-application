'use strict';

var test = require( 'tape' ),
	getCountrySpecifics = require( '../lib/country_specifics_repository' ).getCountrySpecifics;

test( 'Specifics for Germany', function ( t ) {

	var countryCode = 'DE';

	t.test( 'Method returns attributes for relevant fields', function ( t ) {
		var countrySpecifics = getCountrySpecifics( countryCode, 'post-code' ),
			expectedAttributes = {
				'post-code': {
					'data-pattern': '\\s*[0-9]{5}\\s*',
					placeholder: 'z. B. 10117',
					title: 'Fünfstellige Postleitzahl'
				},
				city: {
					placeholder: 'z. B. Berlin'
				},
				email: {
					placeholder: 'z. B. name@domain.de'
				}
			};
		t.deepEqual( countrySpecifics, expectedAttributes, 'Country specific attributes are retrieved' );
		t.end();
	} );

} );

test( 'Generics', function ( t ) {

	var countryCode = 'IE';

	t.test( 'Method returns generic attributes for given field and undefined country code', function ( t ) {
		var countrySpecifics = getCountrySpecifics( countryCode ),
			expectedAttributes = {
				'post-code': {
					'data-pattern': '{1,}',
					placeholder: 'z. B. 10117',
					title: 'Postleitzahl'
				},
				city: {
					placeholder: 'z. B. Berlin'
				},
				email: {
					placeholder: 'z. B. name@domain.com'
				}
			};
		t.deepEqual( countrySpecifics, expectedAttributes, 'Generic attributes are retrieved' );
		t.end();
	} );
} );

