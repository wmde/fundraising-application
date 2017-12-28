'use strict';

var test = require( 'tape-catch' ),
	allSectionsAreValid = require( '../../../lib/state_aggregation/membership/all_validation_sections_are_valid.js' )
;

test( 'Unselected membership type is invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'person',
			membershipType: null,
			paymentType: 'PPL'
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'Active membership payed by BEZ with sane bank data, address, and payment info is valid', function ( t ) {
	t.ok( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'person',
			membershipType: 'active',
			paymentType: 'BEZ'
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: true
		}
	} ) );
	t.end();
} );

test( 'Active membership payed by PPL with address, and payment info is valid without bank data', function ( t ) {
	t.ok( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'person',
			membershipType: 'active',
			paymentType: 'PPL'
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'Sustaining membership payed by PPL with address, and payment info is valid without bank data', function ( t ) {
	t.ok( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'firma',
			membershipType: 'sustaining',
			paymentType: 'PPL'
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'Sustaining membership payed by PPL with with invalid address is invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'person',
			membershipType: 'sustaining',
			paymentType: 'PPL'
		},
		validity: {
			paymentData: true,
			address: false,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'Sustaining membership payed by PPL with invalid paymentData is invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'person',
			membershipType: 'sustaining',
			paymentType: 'PPL'
		},
		validity: {
			paymentData: false,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );

test( 'Active membership for company donors is invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		membershipFormContent: {
			addressType: 'firma',
			membershipType: 'active',
			paymentType: 'PPL'
		},
		validity: {
			paymentData: true,
			address: true,
			bankData: null
		}
	} ) );
	t.end();
} );
