'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createAmountValidationDispatcher = require( '../../lib/validation_dispatchers/amount' );

test( 'AmountValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { amount: '2.00', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createAmountValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok( validator.validate.calledWith( { amount: '2.00' }  ), 'validation function is called with selected fields' );
	t.end();
} );

test( 'AmountValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { amount: '2.00', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAmountValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishPaymentDataValidationAction( successResult ) ) );
	t.end();
} );

test( 'AmountValidationDispatcher dispatches "begin" action', function ( t ) {
	var initialData = {},
		testData = { amount: '2.00', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAmountValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newBeginPaymentDataValidationAction( { amount: '2.00' } ) ) );
	t.end();
} );

test( 'AmountValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAmountValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { amount: '2.00' }, testStore );
	dispatcher.dispatchIfChanged( { amount: '99.00' }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.callCount === 4, 'begin and finish action is dispatched for every change' );

	t.end();
} );

test( 'AmountValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = { amount: '2.00' },
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAmountValidationDispatcher(
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
		dispatcher = createAmountValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
