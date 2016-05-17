'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	validation = require( '../lib/form_validation' );

test( 'Amount validation sends values to server', function ( t ) {
	var positiveResult = { status: 'OK' },
		postFunctionSpy = sinon.stub().returns( positiveResult ),
		amountValidator = validation.createAmountValidator(
			'http://spenden.wikimedia.org/validate-amount',
			postFunctionSpy
		),
		callParameters, validationResult;

	validationResult = amountValidator.validate( { amount: 23, paymentType: 'BEZ', otherStuff: 'foo' } );

	t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equal( callParameters[ 0 ], 'http://spenden.wikimedia.org/validate-amount', 'validation calls configured URL' );
	t.deepEqual( callParameters[ 1 ], { amount: 23, paymentType: 'BEZ' }, 'validation sends only necessary data' );
	t.equal( callParameters[ 3 ], 'json', 'validation expects JSON data' );
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

test( 'Address validation is valid for anonymous address', function ( t ) {
	var positiveResult = { status: 'OK' },
		postFunctionSpy = sinon.spy(),
		addressValidator = validation.createAddressValidator(
			'http://spenden.wikimedia.org/validate-address',
			postFunctionSpy
		),
		validationResult;

	validationResult = addressValidator.validate( { addressType: 'anonym', otherStuff: 'foo' } );

	t.ok( !postFunctionSpy.called, 'post function is not called' );
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

test( 'Given a private adddress, address validation sends values to server', function ( t ) {
	var positiveResult = { status: 'OK' },
		postFunctionSpy = sinon.stub().returns( positiveResult ),
		addressValidator = validation.createAddressValidator(
			'http://spenden.wikimedia.org/validate-address',
			postFunctionSpy
		),
		formData = {
			addressType: 'person',
			title: 'Dr.',
			firstName: 'Hank',
			lastName: 'Scorpio',
			street: 'Hammock District',
			postCode: '12345',
			city: 'Cypress Creek',
			email: 'hank@globex.com'
		},
		callParameters, validationResult;

	validationResult = addressValidator.validate( formData );

	t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equal( callParameters[ 0 ], 'http://spenden.wikimedia.org/validate-address', 'validation calls configured URL' );
	t.deepEqual( callParameters[ 1 ], formData, 'validation sends all data' );
	t.equal( callParameters[ 3 ], 'json', 'validation expects JSON data' );
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

test( 'Given an incomplete private adddress, address validation sends no values to server', function ( t ) {
	var negativeResult = { status: 'INCOMPLETE' },
		postFunctionSpy = sinon.spy(),
		addressValidator = validation.createAddressValidator(
			'http://spenden.wikimedia.org/validate-address',
			postFunctionSpy
		),
		formData = {
			addressType: 'person',
			title: 'Dr.',
			firstName: '',
			lastName: 'Scorpio',
			street: 'Hammock District',
			postCode: '12345',
			city: '',
			email: ''
		},
		validationResult;

	validationResult = addressValidator.validate( formData );

	t.ok( !postFunctionSpy.called, 'post function is not called' );
	t.deepEqual( validationResult, negativeResult, 'validation function returns expected status' );
	t.end();
} );

test( 'Given sepa debit type, bank data validation sends IBAN to server', function ( t ) {
	var positiveResult = { status: 'OK' }, // all other fields are not relevenat to the test
		postFunctionSpy = sinon.stub().returns( positiveResult ),
		bankDataValidator = validation.createBankDataValidator(
			'http://spenden.wikimedia.org/check-iban',
			'http://spenden.wikimedia.org/generate-iban',
			postFunctionSpy
		),
		callParameters, validationResult;

	validationResult = bankDataValidator.validate( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'sepa',
		paymentType: 'BEZ'
	} );

	t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equal( callParameters[ 0 ], 'http://spenden.wikimedia.org/check-iban', 'validation calls URL for SEPA' );
	t.deepEqual( callParameters[ 1 ], { iban: 'DE12500105170648489890' }, 'validation sends only necessary data' );
	t.equal( callParameters[ 3 ], 'json', 'validation expects JSON data' );
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

test( 'Given non-sepa debit type, bank data validation sends account number and bank code to server', function ( t ) {
	var positiveResult = { status: 'OK' }, // all other fields are not relevenat to the test
		postFunctionSpy = sinon.stub().returns( positiveResult ),
		bankDataValidator = validation.createBankDataValidator(
			'http://spenden.wikimedia.org/check-iban',
			'http://spenden.wikimedia.org/generate-iban',
			postFunctionSpy
		),
		callParameters, validationResult;

	validationResult = bankDataValidator.validate( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'non-sepa',
		paymentType: 'BEZ'
	} );

	t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equal( callParameters[ 0 ], 'http://spenden.wikimedia.org/generate-iban', 'validation calls URL for SEPA' );
	t.deepEqual( callParameters[ 1 ], { accountNumber: '0648489890', bankCode: '50010517' }, 'validation sends only necessary data' );
	t.equal( callParameters[ 3 ], 'json', 'validation expects JSON data' );
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

test( 'Given a non-debit payment type, bank data validation succeeds', function ( t ) {
	var postFunctionSpy = sinon.spy(),
		bankDataValidator = validation.createBankDataValidator(
			'http://spenden.wikimedia.org/check-iban',
			'http://spenden.wikimedia.org/generate-iban',
			postFunctionSpy
		),
		expectedValidationResult = { status: 'OK' },
		validationResult;

	validationResult = bankDataValidator.validate( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'non-sepa',
		paymentType: 'PPL'
	} );

	t.equal( postFunctionSpy.callCount, 0, 'data is not sent' );
	t.deepEqual( validationResult, expectedValidationResult, 'validation is successful' );
	t.end();
} );

