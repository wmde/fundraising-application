'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createBankDataValidationDispatcher = require( '../../lib/validation_dispatchers/bankdata' );

test( 'BankDataValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { iban: 'DE12500105170648489890', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createBankDataValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok( validator.validate.calledWith( { iban: 'DE12500105170648489890' }  ), 'validation function is called with selected fields' );
	t.end();
} );

test( 'BankDataValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { iban: 'DE12500105170648489890', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createBankDataValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishBankDataValidationAction( successResult ) ) );
	t.end();
} );

test( 'BankDataValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createBankDataValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { iban: 'DE12500105170648489890' }, testStore );
	dispatcher.dispatchIfChanged( { iban: 'AT022050302101023600' }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.calledTwice, 'action is dispatched for every change' );

	t.end();
} );

test( 'BankDataValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = { amount: '2.00' },
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createBankDataValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( initialData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );

test( 'ValidationDispatcher does nothing if ignored data changes', function ( t ) {
	var testData = { ignoredData: 'this won\'t be validated' },
		initialData = {},
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createBankDataValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
