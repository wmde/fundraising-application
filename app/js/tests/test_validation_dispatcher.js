'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createValidationDispatcher = require( '../lib/validation_dispatcher' ).createValidationDispatcher;

test( 'ValidationDispatcher calls validationFunction and dispatches action', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		initialData = {},
		testData = { importantField: 'just some data', ignoredData: 'this won\'t be validated' },
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = createValidationDispatcher(
			validationFunction,
			actionCreationFunction,
			[ 'importantField' ],
			initialData
		),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is called once' );
	t.ok( validationFunction.calledWith( { importantField: 'just some data' }  ), 'validation function is called with selected fields' );
	t.ok( actionCreationFunction.calledOnce, 'action is created' );
	t.ok( actionCreationFunction.calledWith( successResult ), 'action is created with validation result' );
	t.ok( testStore.dispatch.calledWith( dummyAction ), 'validation dispatcher should dispatch action object' );
	t.end();
} );

test( 'ValidationDispatcher calls validationFunction and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		initialData = {},
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = createValidationDispatcher(
			validationFunction,
			actionCreationFunction,
			[ 'importantField' ],
			initialData
		),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( { importantField: 'just some data' }, testStore );
	dispatcher.dispatchIfChanged( { importantField: 'data changed again' }, testStore );

	t.ok( validationFunction.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.calledTwice, 'action is dispatched for every change' );

	t.end();
} );

test( 'ValidationDispatcher calls validationFunction and dispatches action only if data changes', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		testData = { importantField: 'just some data' },
		initialData = {},
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = createValidationDispatcher(
			validationFunction,
			actionCreationFunction,
			[ 'importantField' ],
			initialData
		),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );
	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is called once' );
	t.ok( testStore.dispatch.calledOnce, 'action is dispatched once' );

	t.end();
} );

test( 'ValidationDispatcher does nothing if ignored data changes', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		testData = { importantField: 'just some data', ignoredData: 'this won\'t be validated' },
		initialData = {},
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = createValidationDispatcher(
			validationFunction,
			actionCreationFunction,
			[ 'importantField' ],
			initialData
		),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );

	testData.ignoredData = 'data changed, but in ignored field';
	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is not called when ignored field change' );
	t.ok( testStore.dispatch.calledOnce, 'action is is not dispatched when ignored field change' );

	t.end();
} );

test( 'createValidationDispatcher accepts validator object as validation function', function ( t ) {
	var validatorSpy = {
			// use internal delegation to check if 'this' is bound correctly
			validatorDelegate: sinon.spy(),
			validate: function ( formValues ) {
				return this.validatorDelegate( formValues );
			}
		},
		testData = { importantField: 'just some data which will be ignored' },
		actionCreationFunction = sinon.stub(),
		dispatcher = createValidationDispatcher(
			validatorSpy,
			actionCreationFunction,
			[ 'importantField' ],
			{}
		),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validatorSpy.validatorDelegate.calledOnce, 'validate function is called once' );
	t.ok( validatorSpy.validatorDelegate.calledWith( testData ), 'validation function is called with test data' );
	t.end();
} );

