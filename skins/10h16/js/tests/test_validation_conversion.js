'use strict';

var test = require( 'tape' ),
	createInitialStateFromViolatedFields = require( '../lib/validation_conversion' ).createInitialStateFromViolatedFields
	;

test( 'empty violated fields return empty object', function ( t ) {
	t.deepEqual( createInitialStateFromViolatedFields( {}, {} ), { validity: {} } );
	t.end();
} );

test( 'violated amount returns violations', function ( t ) {
	var violatedFields = { betrag: 'Amount too low' },
		expectedState = { validity: { paymentData: false } };

	t.deepEqual( createInitialStateFromViolatedFields( violatedFields, {} ), expectedState );
	t.end();
} );

test( 'violated payment type returns violations', function ( t ) {
	var violatedFields = { zahlweise: 'Not supported' },
		expectedState = { validity: { paymentData: false } };

	t.deepEqual( createInitialStateFromViolatedFields( violatedFields, {} ), expectedState );
	t.end();
} );

test( 'passing initial validity returns proper state', function ( t ) {
	t.deepEqual( createInitialStateFromViolatedFields( {}, { paymentData: true } ), { validity: { paymentData: true } } );
	t.end();
} );
