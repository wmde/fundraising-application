'use strict';

var test = require( 'tape-catch' ),
	amountAndFrequencyAreValid = require( '../../../lib/state_aggregation/membership/amount_and_frequency_are_valid' )
;

test( 'Amount, and interval type given is valid and has data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			membershipFormContent: {
				paymentIntervalInMonths: 1,
				amount: 5000
			},
			membershipInputValidation: {
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

test( 'All but amount selected is invalid but has data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			membershipFormContent: {
				paymentIntervalInMonths: 1,
				amount: 0
			},
			membershipInputValidation: {
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

test( 'All but payment interval selected is invalid but has data entered', function ( t ) {
	t.deepEqual(
		amountAndFrequencyAreValid( {
			membershipFormContent: {
				paymentIntervalInMonths: -1,
				amount: 10000
			},
			membershipInputValidation: {
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
			membershipFormContent: {
				paymentIntervalInMonths: -1,
				amount: 0
			},
			membershipInputValidation: {
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
