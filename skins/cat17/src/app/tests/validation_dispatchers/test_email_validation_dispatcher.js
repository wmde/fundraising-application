'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createEmailValidationDispatcher = require( '../../lib/validation_dispatchers/email' ),
	testData = { email: 'gandalf@example.com', ignoredData: 'this won\'t be validated' };

test( 'EmailValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createEmailValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok( validator.validate.calledWith( { email: 'gandalf@example.com' }  ), 'validation function is called with selected fields' );
	t.end();
} );

test( 'EmailValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createEmailValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishEmailAddressValidationAction( successResult ) ) );
	t.end();
} );

test( 'EmailValidationDispatcher dispatches "begin" action', function ( t ) {
	var initialData = {},
		testData = { email: 'gandalf@example.com', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createEmailValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newBeginEmailAddressValidationAction( { email: 'gandalf@example.com' } ) ) );
	t.end();
} );

test( 'EmailValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createEmailValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { email: 'gandalf@example.com' }, testStore );
	dispatcher.dispatchIfChanged( { email: 'frodo@example.com' }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.callCount === 4, 'begin and finish action is dispatched for every change' );

	t.end();
} );

test( 'EmailValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = testData,
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createEmailValidationDispatcher(
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
		dispatcher = createEmailValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { ignoredData: 'this won\'t be validated' }, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
