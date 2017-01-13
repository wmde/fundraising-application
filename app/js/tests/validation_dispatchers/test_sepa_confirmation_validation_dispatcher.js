'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createSepaConfirmationValidationDispatcher = require( '../../lib/validation_dispatchers/sepa_confirmation' );

test( 'SepaConfirmationValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { confirmSepa: true, ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createSepaConfirmationValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok( validator.validate.calledWith( { confirmSepa: true }  ), 'validation function is called with selected fields' );
	t.end();
} );

test( 'SepaConfirmationValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { confirmSepa: true, ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createSepaConfirmationValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishSepaConfirmationValidationAction( successResult ) ) );
	t.end();
} );

test( 'SepaConfirmationValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createSepaConfirmationValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { confirmSepa: true }, testStore );
	dispatcher.dispatchIfChanged( { confirmSepa: false }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.calledTwice, 'action is dispatched for every change' );

	t.end();
} );

test( 'SepaConfirmationValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = { amount: '2.00' },
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createSepaConfirmationValidationDispatcher(
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
		dispatcher = createSepaConfirmationValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
