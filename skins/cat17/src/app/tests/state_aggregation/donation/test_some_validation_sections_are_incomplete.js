'use strict';

var test = require( 'tape-catch' ),
	someSectionsAreIncomplete = require( '../../../lib/state_aggregation/donation/some_validation_sections_are_incomplete.js' ),
	Validity = require( '../../../lib/validation/validation_states' ).Validity
;

test( 'When all sections are valid, none are incomplete', function ( t ) {
	t.notOk( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.VALID,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'When all sections are invalid, none are incomplete', function ( t ) {
	t.notOk( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.INVALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.INVALID,
			address: Validity.INVALID,
			bankData: Validity.INVALID
		}
	} ) );
	t.end();
} );

test( 'Incomplete bank data ignored when not paying via debit', function ( t ) {
	t.notOk( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'PPL'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.VALID,
			bankData: Validity.INCOMPLETE
		}
	} ) );
	t.end();
} );

test( 'Incomplete payment data means incomplete', function ( t ) {
	t.ok( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.INCOMPLETE,
			address: Validity.VALID,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'Missing payment type means incomplete', function ( t ) {
	t.ok( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.INCOMPLETE,
				dataEntered: false
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.VALID,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'Incomplete address data means incomplete', function ( t ) {
	t.ok( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.INCOMPLETE,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'Incomplete bank data means incomplete', function ( t ) {
	t.ok( someSectionsAreIncomplete( {
		donationFormContent: {
			paymentType: 'BEZ'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.VALID,
			bankData: Validity.INCOMPLETE
		}
	} ) );
	t.end();
} );
