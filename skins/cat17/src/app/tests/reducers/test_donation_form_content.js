'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	donationFormContent = require( '../../lib/reducers/donation_form_content' );

test( 'INITIALIZE_CONTENT changes multiple fields', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0 },
		expectedState = { paymentType: 'BEZ', amount: '25,00' },
		action = { type: 'INITIALIZE_CONTENT', payload: { amount: '25,00', paymentType: 'BEZ' } };

	deepFreeze( stateBefore );
	t.deepEqual( donationFormContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'INITIALIZE_CONTENT throws an error if a field name is not allowed', function ( t ) {
	var action = { type: 'INITIALIZE_CONTENT', payload: {
		amount: '25,00',
		paymentType: 'BEZ',
		unknownField: 'supercalifragilistic'
	} };

	t.throws( function () {
		donationFormContent( {}, action );
	}, /unknownField/ );
	t.end();
} );
