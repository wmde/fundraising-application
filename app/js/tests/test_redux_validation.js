'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	reduxValidation = require( '../lib/redux_validation' );

test( 'ValidationDispatcher calls validationFunction and dispatches action', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		testData = { importantField: 'just some data', ignoredData: 'this won\'t be validated' },
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = reduxValidation.createValidationDispatcher( validationFunction, actionCreationFunction, [ 'importantField' ] ),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is called once' );
	t.ok( validationFunction.calledWith( { importantField: 'just some data' }  ), 'validation function is called with selected fields' );
	t.ok( actionCreationFunction.calledOnce, 'action is created' );
	t.ok( actionCreationFunction.calledWith( successResult ), 'action is created with validation result' );
	t.ok( testStore.dispatch.calledWith( dummyAction ), 'validation dispatcher should dispatch action object' );
	t.end();
} );

test( 'ValidationDispatcher calls validationFunction and dispatches action only if data changes', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		testData = { importantField: 'just some data', ignoredData: 'this won\'t be validated' },
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		dispatcher = reduxValidation.createValidationDispatcher( validationFunction, actionCreationFunction, [ 'importantField' ] ),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );
	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is called once' );
	t.ok( testStore.dispatch.calledOnce, 'action is dispatched once' );

	testData.ignoredData = 'data changed, but in ignored field';
	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledOnce, 'validation function is not called when ignored field change' );
	t.ok( testStore.dispatch.calledOnce, 'action is is not dispatched when ignored field change' );

	testData.importantField = 'data changed';
	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validationFunction.calledTwice, 'validation function is called once' );
	t.ok( actionCreationFunction.calledTwice, 'new action is created' );
	t.ok( testStore.dispatch.calledTwice, 'action is dispatched again' );

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
		dispatcher = reduxValidation.createValidationDispatcher( validatorSpy, actionCreationFunction, [ 'importantField' ] ),
		testStore = { dispatch: sinon.spy() };

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validatorSpy.validatorDelegate.calledOnce, 'validate function is called once' );
	t.ok( validatorSpy.validatorDelegate.calledWith( testData ), 'validation function is called with test data' );
	t.end();
} );

test( 'createValidationDispatcher can set initial values from object', function ( t ) {
	var initialDataBag = { importantField: 'initial data', ignoredData: 'this won\'t be added to initial data' },
		validationFunction = sinon.stub(),
		actionCreationFunction = sinon.stub(),
		dispatcher = reduxValidation.createValidationDispatcher( validationFunction, actionCreationFunction, [ 'importantField' ], initialDataBag ),
		expectedPreviousFieldValues = { importantField: 'initial data' };

	t.deepEqual( dispatcher.previousFieldValues, expectedPreviousFieldValues, 'Previous field values should only contain selected values' );

	t.end();
} );

test( 'ValidationDispatcherCollection listens to store updates', function ( t ) {
	var storeSpy = {
			subscribe: sinon.spy()
		};

	reduxValidation.createValidationDispatcherCollection( storeSpy, [] );

	t.ok( storeSpy.subscribe.calledOnce, 'mapper subscribes to store updates' );
	t.end();
} );

test( 'ValidationDispatcherCollection update method calls dispatchers', function ( t ) {
	var formContent = { amount: 42 },
		storeSpy = {
			subscribe: sinon.spy(),
			getState: sinon.stub().returns( formContent )
		},
		validatorSpy = { dispatchIfChanged: sinon.spy() },
		collection = reduxValidation.createValidationDispatcherCollection( storeSpy, [ validatorSpy ] );

	collection.onUpdate();

	t.ok( storeSpy.getState.calledOnce, 'onUpdate gets state from the store' );
	t.ok( validatorSpy.dispatchIfChanged.calledOnce, 'dispatchers are called' );
	t.end();
} );

