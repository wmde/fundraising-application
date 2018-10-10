'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../../lib/reducers/input_validation' ).inputValidation;

// Test handling results of bank data validation

function newBankDataValidationAction( payload ) {
	return {
		type: 'FINISH_BANK_DATA_VALIDATION',
		payload: payload
	};
}

test( 'If bank data validation is successful, all fields in payload have valid status', function ( t ) {
	var stateBefore = {
			iban: { dataEntered: false, isValid: null },
			bic: { dataEntered: false, isValid: null },
		},
		expectedState = {
			iban: { dataEntered: true, isValid: true },
			bic: { dataEntered: true, isValid: true },
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( {
		status: 'OK',
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX'
	} ) ), expectedState );
	t.end();
} );

test( 'If bank data validation is successful, only fields in payload change status', function ( t ) {
	var stateBefore = {
			iban: { dataEntered: false, isValid: null },
			bic: { dataEntered: false, isValid: null },
		},
		expectedState = {
			iban: { dataEntered: true, isValid: true },
			bic: { dataEntered: false, isValid: null },
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( {
		status: 'OK',
		iban: 'DE12500105170648489890'
	} ) ), expectedState );
	t.end();
} );

test( 'If bank data validation fails, all fields retrieve invalid status', function ( t ) {
	var stateBefore = {
			iban: { dataEntered: false, isValid: null },
			bic: { dataEntered: false, isValid: null },
		},
		expectedState = {
			iban: { dataEntered: true, isValid: false },
			bic: { dataEntered: true, isValid: false },
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( { status: 'ERR' } ) ), expectedState );
	t.end();
} );
