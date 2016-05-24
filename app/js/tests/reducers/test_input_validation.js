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

function newAddressValidationAction( returnedStatus ) {
	return {
		type: 'FINISH_ADDRESS_VALIDATION',
		payload: {
			status: returnedStatus
		}
	};
}

test( 'If no data was entered yet, validation returns previous state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: false, isValid: null } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );

test( 'If invalid data was entered, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: true, isValid: false } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'ShouldFail' ) ), expectedState );
	t.end();
} );

test( 'If valid data was entered, validation returns valid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: false, isValid: null } },
		expectedState = { testField: { dataEntered: true, isValid: true } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( 'abc123' ) ), expectedState );
	t.end();
} );

test( 'If valid data was changed to invalid data, validation returns invalid state', function ( t ) {
	var stateBefore = { testField: { dataEntered: true, isValid: true } },
		expectedState = { testField: { dataEntered: true, isValid: false } };

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAction( '' ) ), expectedState );
	t.end();
} );

test( 'If bank data validation is successful, all fields retrieve valid status', function ( t ) {
	var stateBefore = {
			iban: { dataEntered: false, isValid: null },
			bic: { dataEntered: false, isValid: null },
			account: { dataEntered: false, isValid: null },
			bankCode: { dataEntered: false, isValid: null }
		},
		expectedState = {
			iban: { dataEntered: true, isValid: true },
			bic: { dataEntered: true, isValid: true },
			account: { dataEntered: true, isValid: true },
			bankCode: { dataEntered: true, isValid: true }
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( 'OK' ) ), expectedState );
	t.end();
} );

test( 'If bank data validation fails, all fields retrieve invalid status', function ( t ) {
	var stateBefore = {
			iban: { dataEntered: false, isValid: null },
			bic: { dataEntered: false, isValid: null },
			account: { dataEntered: false, isValid: null },
			bankCode: { dataEntered: false, isValid: null }
		},
		expectedState = {
			iban: { dataEntered: true, isValid: false },
			bic: { dataEntered: true, isValid: false },
			account: { dataEntered: true, isValid: false },
			bankCode: { dataEntered: true, isValid: false }
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newBankDataValidationAction( 'ERR' ) ), expectedState );
	t.end();
} );

test( 'If address data validation is successful, all fields retrieve valid status', function ( t ) {
	var stateBefore = {
			firstName: { dataEntered: true, isValid: null },
			lastName: { dataEntered: true, isValid: null },
			street: { dataEntered: true, isValid: null },
			city: { dataEntered: true, isValid: null },
			postCode: { dataEntered: true, isValid: null },
			email: { dataEntered: true, isValid: null }
		},
		expectedState = {
			firstName: { dataEntered: true, isValid: true },
			lastName: { dataEntered: true, isValid: true },
			street: { dataEntered: true, isValid: true },
			city: { dataEntered: true, isValid: true },
			postCode: { dataEntered: true, isValid: true },
			email: { dataEntered: true, isValid: true }
		};

	deepFreeze( stateBefore );
	t.deepEqual( inputValidation( stateBefore, newAddressValidationAction( 'OK' ) ), expectedState );
	t.end();
} );
