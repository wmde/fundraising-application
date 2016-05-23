'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	inputValidation = require( '../../lib/reducers/input_validation' ).inputValidation;

function newAction( value ) {
	return {
		type: 'VALIDATE_INPUT',
		payload: {
			contentName: 'testField',
			value: value,
			pattern: '^[a-z0-9]+$'
		}
	};
}

function newBankDataValidationAction( returnedStatus ) {
	return {
		type: 'FINISH_BANK_DATA_VALIDATION',
		payload: {
			status: returnedStatus
		}
	};
}

test( 'If invalid data was entered, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: false };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'ShouldFail' ) ), expectedState );
	t.end();
} );

test( 'If valid data was entered, validation returns valid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: true };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'abc123' ) ), expectedState );
	t.end();
} );

test( 'If valid data was changed to invalid data, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: null },
		expectedState = { testField: false };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );

test( 'If bank data validation is successful, all fields retrieve valid status', function ( t ) {
	var stateBefore = { iban: null, bic: null, account: null, bankCode: null },
		expectedState = { iban: true, bic: true, account: true, bankCode: true };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( 'OK' ) ), expectedState );
	t.end();
} );

test( 'If bank data validation is successful, all fields retrieve invalid status', function ( t ) {
	var stateBefore = { iban: null, bic: null, account: null, bankCode: null },
		expectedState = { iban: false, bic: false, account: false, bankCode: false };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( 'ERR' ) ), expectedState );
	t.end();
} );
