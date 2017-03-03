'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createFeeValidationDispatcher = require( '../../lib/validation_dispatchers/fee' ),
	testData = { amount: '2.00', paymentIntervalInMonths: 3, addressType: 'privat', ignoredData: 'this won\'t be validated' };

test( 'FeeValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok(
		validator.validate.calledWith( { amount: '2.00', paymentIntervalInMonths: 3, addressType: 'privat' } ),
		'validation function is called with selected fields'
	);
	t.end();
} );

test( 'FeeValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishPaymentDataValidationAction( successResult ) ) );
	t.end();
} );

test( 'FeeValidationDispatcher dispatches "begin" action', function ( t ) {
	var initialData = {},
		testData = { amount: '2.00', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newBeginPaymentDataValidationAction( { amount: '2.00' } ) ) );
	t.end();
} );

test( 'FeeValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { amount: '2.00' }, testStore );
	dispatcher.dispatchIfChanged( { amount: '99.00' }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.callCount === 4, 'begin and finish action is dispatched for every change' );

	t.end();
} );

test( 'FeeValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = testData,
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );

test( 'ValidationDispatcher does nothing if ignored data changes', function ( t ) {
	var initialData = {},
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { ignoredData: 'this won\'t be validated' }, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );

test( 'ValidationDispatcher does nothing if required fields don\'t have a value', function ( t ) {
	var initialData = { amount: 0, addressType: 'privat', paymentIntervalInMonths: 12 },
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createFeeValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { amount: 0, addressType: 'wtf', paymentIntervalInMonths: 12 }, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
