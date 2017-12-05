'use strict';

var test = require( 'tape-catch' ),
	donorTypeAndAddressAreValid = require( '../../../lib/state_aggregation/donation/donor_type_and_address_are_valid' )
;

test( 'Empty form is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			donationFormContent: {
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
			donationInputValidation: {
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

test( 'Anonym address type makes makes address validity irrelevant', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			donationFormContent: {
				addressType: 'anonym',
				salutation: '',
				companyName: '',
				firstName: '',
				lastName: '',
				street: '',
				postcode: '',
				city: '',
				email: ''
			},
			donationInputValidation: {
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
			dataEntered: true,
			isValid: true
		}
	);
	t.end();
} );


test( 'All private person information given is valid and has data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			donationFormContent: {
				addressType: 'person',
				salutation: 'Herr',
				firstName: 'Testr',
				lastName: 'Usa',
				street: 'Demostreet 42',
				postcode: '10112',
				city: 'Bärlin',
				email: 'me@you.com'
			},
			donationInputValidation: {
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
			donationFormContent: {
				addressType: 'firma',
				companyName: 'ACME',
				street: 'Demostreet 42',
				postcode: '10112',
				city: 'Bärlin',
				email: 'me@you.com'
			},
			donationInputValidation: {
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


test( 'Bad email makes aggregation invalid but has data entered', function ( t ) {
	t.deepEqual(
		donorTypeAndAddressAreValid( {
			donationFormContent: {
				addressType: 'person',
				salutation: 'Herr',
				firstName: 'Testr',
				lastName: 'Usa',
				street: 'Demostreet 42',
				postcode: '10112',
				city: 'Bärlin',
				email: 'wont tell'
			},
			donationInputValidation: {
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
					isValid: false
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