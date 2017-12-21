'use strict';

var test = require( 'tape-catch' ),
	paymentAndBankDataAreValid = require( '../../../lib/state_aggregation/membership/payment_and_bank_data_are_valid' )
;

test( 'No payment type and no bank data given is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			membershipFormContent: {
				paymentType: null,
				iban: '',
				bic: '',
				accountNumber: '',
				bankCode: ''
			},
			membershipInputValidation: {
				iban: {
					dataEntered: false,
					isValid: null
				},
				bic: {
					dataEntered: false,
					isValid: null
				},
				accountNumber: {
					dataEntered: false,
					isValid: null
				},
				bankCode: {
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
			membershipFormContent: {
				paymentType: 'BEZ',
				iban: 'DE12500105170648489890',
				bic: 'INGDDEFFXXX',
				accountNumber: '0648489890',
				bankCode: '50010517'
			},
			membershipInputValidation: {
				iban: {
					dataEntered: true,
					isValid: true
				},
				bic: {
					dataEntered: true,
					isValid: true
				},
				accountNumber: {
					dataEntered: true,
					isValid: true
				},
				bankCode: {
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
			membershipFormContent: {
				paymentType: 'BEZ',
				iban: 'DE1250010517',
				bic: '',
				accountNumber: '',
				bankCode: ''
			},
			membershipInputValidation: {
				iban: {
					dataEntered: true,
					isValid: false
				},
				bic: {
					dataEntered: false,
					isValid: null
				},
				accountNumber: {
					dataEntered: false,
					isValid: null
				},
				bankCode: {
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

test( 'PPL payment type and no bank data given is valid and has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			membershipFormContent: {
				paymentType: 'PPL',
				iban: '',
				bic: '',
				accountNumber: '',
				bankCode: ''
			},
			membershipInputValidation: {
				iban: {
					dataEntered: false,
					isValid: null
				},
				bic: {
					dataEntered: false,
					isValid: null
				},
				accountNumber: {
					dataEntered: false,
					isValid: null
				},
				bankCode: {
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

test( 'PPL payment type and wrong bank data given is valid and has data entered', function ( t ) {
	t.deepEqual(
		paymentAndBankDataAreValid( {
			membershipFormContent: {
				paymentType: 'PPL',
				iban: 'DE1250010517',
				bic: '',
				accountNumber: '',
				bankCode: ''
			},
			membershipInputValidation: {
				iban: {
					dataEntered: true,
					isValid: false
				},
				bic: {
					dataEntered: false,
					isValid: null
				},
				accountNumber: {
					dataEntered: false,
					isValid: null
				},
				bankCode: {
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
			isValid: true
		}
	);
	t.end();
} );
