'use strict';

var test = require( 'tape-catch' ),
	donorTypeAndAddressAreValid = require( '../../../lib/state_aggregation/membership/donor_type_and_address_are_valid' )
;

test( 'Empty form is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			membershipFormContent: {
				addressType: '',
				salutation: '',
				companyName: '',
				firstName: '',
				lastName: '',
				street: '',
				postcode: '',
				city: '',
				email: ''
			},
			membershipInputValidation: {
				salutation: {
					dataEntered: false,
					isValid: null
				},
				companyName: {
					dataEntered: false,
					isValid: null
				},
				firstName: {
					dataEntered: false,
					isValid: null
				},
				lastName: {
					dataEntered: false,
					isValid: null
				},
				street: {
					dataEntered: false,
					isValid: null
				},
				postcode: {
					dataEntered: false,
					isValid: null
				},
				city: {
					dataEntered: false,
					isValid: null
				},
				email: {
					dataEntered: false,
					isValid: null
				}
			}
		} ),
		{
			dataEntered: false,
			isValid: null
		}
	);
	t.end();
} );

test( 'All private person information given is valid and has data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			membershipFormContent: {
				addressType: 'person',
				salutation: 'Herr',
				firstName: 'Testr',
				lastName: 'Usa',
				street: 'Demostreet 42',
				postcode: '10112',
				city: 'Bärlin',
				email: 'me@you.com'
			},
			membershipInputValidation: {
				salutation: {
					dataEntered: true,
					isValid: true
				},
				firstName: {
					dataEntered: true,
					isValid: true
				},
				lastName: {
					dataEntered: true,
					isValid: true
				},
				street: {
					dataEntered: true,
					isValid: true
				},
				postcode: {
					dataEntered: true,
					isValid: true
				},
				city: {
					dataEntered: true,
					isValid: true
				},
				email: {
					dataEntered: true,
					isValid: true
				}
			}
		} ),
		{
			dataEntered: true,
			isValid: true
		}
	);
	t.end();
} );

test( 'All company information given is valid and has data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			membershipFormContent: {
				addressType: 'firma',
				companyName: 'ACME',
				street: 'Demostreet 42',
				postcode: '10112',
				city: 'Bärlin',
				email: 'me@you.com'
			},
			membershipInputValidation: {
				companyName: {
					dataEntered: true,
					isValid: true
				},
				street: {
					dataEntered: true,
					isValid: true
				},
				postcode: {
					dataEntered: true,
					isValid: true
				},
				city: {
					dataEntered: true,
					isValid: true
				},
				email: {
					dataEntered: true,
					isValid: true
				}
			}
		} ),
		{
			dataEntered: true,
			isValid: true
		}
	);
	t.end();
} );

test( 'Bad ZIP code makes aggregation invalid but has data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			membershipFormContent: {
				addressType: 'person',
				salutation: 'Herr',
				firstName: 'Testr',
				lastName: 'Usa',
				street: 'Demostreet 42',
				postcode: '101124711',
				city: 'Bärlin',
				email: 'me@you.com'
			},
			membershipInputValidation: {
				salutation: {
					dataEntered: true,
					isValid: true
				},
				firstName: {
					dataEntered: true,
					isValid: true
				},
				lastName: {
					dataEntered: true,
					isValid: true
				},
				street: {
					dataEntered: true,
					isValid: true
				},
				postcode: {
					dataEntered: true,
					isValid: false
				},
				city: {
					dataEntered: true,
					isValid: true
				},
				email: {
					dataEntered: true,
					isValid: true
				}
			}
		} ),
		{
			dataEntered: true,
			isValid: false
		}
	);
	t.end();
} );