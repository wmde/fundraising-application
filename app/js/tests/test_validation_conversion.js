'use strict';

var test = require( 'tape' ),
	createInitialStateFromViolatedFields = require( '../lib/validation_conversion' ).createInitialStateFromViolatedFields
	;

test( 'empty violated fields return empty object', function ( t ) {
	t.deepEqual( createInitialStateFromViolatedFields( {} ), {} );
	t.end();
} );

test( 'violated amount returns amount messages and violations', function ( t ) {
	var violatedFields = { betrag: 'Amount too low' },
		expectedState = {
			validity: {
				amount: false
			},
			validationMessages: {
				amount: 'Amount too low'
			}
		};

	t.deepEqual( createInitialStateFromViolatedFields( violatedFields ), expectedState );
	t.end();
} );

test( 'violated payment type returns amount messages and violations', function ( t ) {
	var violatedFields = { zahlweise: 'Not supported' },
		expectedState = {
			validity: {
				amount: false
			},
			validationMessages: {
				paymentType: 'Not supported'
			}
		};

	t.deepEqual( createInitialStateFromViolatedFields( violatedFields ), expectedState );
	t.end();
} );
