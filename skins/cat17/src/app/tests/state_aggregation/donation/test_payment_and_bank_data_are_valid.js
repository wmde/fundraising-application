'use strict';

var test = require( 'tape-catch' ),
	paymentAndBankDataAreValid = require( '../../../lib/state_aggregation/donation/payment_and_bank_data_are_valid' ),
	Validity = require( '../../../lib/validation/validation_states' ).Validity
;

test( 'No payment type and no bank data given is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: '',
				iban: '',
				bic: ''
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				},
				iban: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				},
				bic: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				}
			},
			validity: {
				bankData: null
			}
		} ),
		{
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		}
	);
	t.end();
} );

test( 'BEZ payment type and sane bank data given is valid and has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: 'BEZ',
				iban: 'DE12500105170648489890',
				bic: 'INGDDEFFXXX'
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: Validity.VALID
				},
				iban: {
					dataEntered: true,
					isValid: Validity.VALID
				},
				bic: {
					dataEntered: true,
					isValid: Validity.VALID
				}
			},
			validity: {
				bankData: Validity.VALID
			}
		} ),
		{
			dataEntered: true,
			isValid: Validity.VALID
		}
	);
	t.end();
} );

test( 'BEZ payment type and wrong bank data given is invalid but has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: 'BEZ',
				iban: 'DE1250010517',
				bic: ''
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: Validity.VALID
				},
				iban: {
					dataEntered: true,
					isValid: Validity.INVALID
				},
				bic: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				}
			},
			validity: {
				bankData: Validity.INVALID
			}
		} ),
		{
			dataEntered: true,
			isValid: Validity.INVALID
		}
	);
	t.end();
} );

test( 'SUB payment type and no bank data given is valid and has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: 'SUB',
				iban: '',
				bic: ''
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: Validity.VALID
				},
				iban: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				},
				bic: {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				}
			},
			validity: {
				bankData: Validity.INCOMPLETE
			}
		} ),
		{
			dataEntered: true,
			isValid: Validity.VALID
		}
	);
	t.end();
} );

test( 'SUB payment type and invalid bank data given is valid and has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: 'SUB',
				iban: '7777',
				bic: 'foo'
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: Validity.VALID
				},
				iban: {
					dataEntered: true,
					isValid: Validity.INVALID
				},
				bic: {
					dataEntered: true,
					isValid: Validity.INCOMPLETE
				}
			},
			validity: {
				bankData: Validity.INCOMPLETE
			}
		} ),
		{
			dataEntered: true,
			isValid: Validity.VALID
		}
	);
	t.end();
} );
