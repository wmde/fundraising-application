'use strict';

var test = require( 'tape-catch' ),
	allSectionsAreValid = require( '../../../lib/state_aggregation/donation/all_validation_sections_are_valid.js' ),
	Validity = require( '../../../lib/validation/validation_states' ).Validity
;

test( 'Valid all over means valid', function ( t ) {
	t.ok( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
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

test( 'Bank data validity ignored when not paying via debit', function ( t ) {
	t.ok( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'PPL',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
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

test( 'No validity means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: '',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.INVALID,
				dataEntered: false
			},
			email: {
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

test( 'Faulty payment data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.INVALID,
			address: Validity.VALID,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'Faulty payment type data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: '',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.INVALID,
				dataEntered: true
			},
			email: {
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

test( 'Faulty address data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
				isValid: Validity.VALID,
				dataEntered: true
			}
		},
		validity: {
			paymentData: Validity.VALID,
			address: Validity.INVALID,
			bankData: Validity.VALID
		}
	} ) );
	t.end();
} );

test( 'Faulty email data means invalid', function ( t ) {
	t.notOk( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ',
			addressType: 'person'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
				isValid: Validity.INVALID,
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

test( 'Faulty email data is ignored for anonymous', function ( t ) {
	t.ok( allSectionsAreValid( {
		donationFormContent: {
			paymentType: 'BEZ',
			addressType: 'anonym'
		},
		donationInputValidation: {
			paymentType: {
				isValid: Validity.VALID,
				dataEntered: true
			},
			email: {
				isValid: Validity.INVALID,
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
