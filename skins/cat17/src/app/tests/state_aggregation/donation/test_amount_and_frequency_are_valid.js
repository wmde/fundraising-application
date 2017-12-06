'use strict';

var test = require( 'tape-catch' ),
	amountAndFrequencyAreValid = require( '../../../lib/state_aggregation/donation/amount_and_frequency_are_valid' )
;

test( 'Amount, and interval type given is valid and has data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			donationFormContent: {
				paymentIntervalInMonths: 1,
				amount: 5000
			},
			donationInputValidation: {
				amount: {
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

test( 'Amount, and interval type given have valid client values but server-side has declared them invalid', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			donationFormContent: {
				paymentIntervalInMonths: 1,
				amount: 99
			},
			donationInputValidation: {
				amount: {
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

test( 'Interval valid but amount not entered thus invalid results in invalid', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			donationFormContent: {
				paymentIntervalInMonths: 1,
				amount: 0
			},
			donationInputValidation: {
				amount: {
					dataEntered: false,
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

test( 'Payment interval not selected results in invalid but with data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			donationFormContent: {
				paymentIntervalInMonths: -1,
				amount: 10000
			},
			donationInputValidation: {
				amount: {
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


test( 'No data selected is not validated and has no data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			donationFormContent: {
				paymentIntervalInMonths: -1,
				amount: 0
			},
			donationInputValidation: {
				amount: {
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
