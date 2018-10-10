'use strict';

var test = require( 'tape-catch' ),
	paymentAndBankDataAreValid = require( '../../../lib/state_aggregation/donation/payment_and_bank_data_are_valid' )
;

test( 'No payment type and no bank data given is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			donationFormContent: {
				paymentType: '',
				iban: '',
				bic: '',
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: false,
					isValid: null
				},
				iban: {
					dataEntered: false,
					isValid: null
				},
				bic: {
					dataEntered: false,
					isValid: null
				}
			},
			validity: {
				bankData: null
			}
		} ),
		{
			dataEntered: false,
			isValid: null
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
				bic: 'INGDDEFFXXX',
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: true
				},
				iban: {
					dataEntered: true,
					isValid: true
				},
				bic: {
					dataEntered: true,
					isValid: true
				}
			},
			validity: {
				bankData: true
			}
		} ),
		{
			dataEntered: true,
			isValid: true
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
				bic: '',
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: true
				},
				iban: {
					dataEntered: true,
					isValid: false
				},
				bic: {
					dataEntered: false,
					isValid: null
				}
			},
			validity: {
				bankData: false
			}
		} ),
		{
			dataEntered: true,
			isValid: false
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
				bic: '',
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: true
				},
				iban: {
					dataEntered: false,
					isValid: null
				},
				bic: {
					dataEntered: false,
					isValid: null
				}
			},
			validity: {
				bankData: null
			}
		} ),
		{
			dataEntered: true,
			isValid: true
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
				bic: 'foo',
			},
			donationInputValidation: {
				paymentType: {
					dataEntered: true,
					isValid: true
				},
				iban: {
					dataEntered: true,
					isValid: false
				},
				bic: {
					dataEntered: true,
					isValid: null
				}
			},
			validity: {
				bankData: null
			}
		} ),
		{
			dataEntered: true,
			isValid: true
		}
	);
	t.end();
} );
