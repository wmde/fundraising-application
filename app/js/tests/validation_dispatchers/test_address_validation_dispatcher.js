'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	Actions = require( '../../lib/actions' ),
	createAddressValidationDispatcher = require( '../../lib/validation_dispatchers/address' );

test( 'AddressValidationDispatcher calls validator', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { street: 'The Yellow Brick Road', city: 'Emerald City', ignoredData: 'this won\'t be validated' },
		expectedData = { street: 'The Yellow Brick Road', city: 'Emerald City' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.stub() },
		dispatcher = createAddressValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( validator.validate.calledOnce, 'validation function is called once' );
	t.ok( validator.validate.calledWith( expectedData ), 'validation function is called with selected fields' );
	t.end();
} );

test( 'AddressValidationDispatcher dispatches result as action', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		testData = { street: 'The Yellow Brick Road', city: 'Emerald City', ignoredData: 'this won\'t be validated' },
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAddressValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.ok( testStore.dispatch.calledWith( Actions.newFinishAddressValidationAction( successResult ) ) );
	t.end();
} );

test( 'AddressValidationDispatcher calls validator and dispatches action every time the data changes', function ( t ) {
	var successResult = { status: 'OK' },
		initialData = {},
		validator = { validate: sinon.stub().returns( successResult ) },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAddressValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( { street: 'The Yellow Brick Road' }, testStore );
	dispatcher.dispatchIfChanged( { street: 'Emerald Lane' }, testStore );

	t.ok( validator.validate.calledTwice, 'validation function is called for every change' );
	t.ok( testStore.dispatch.calledTwice, 'action is dispatched for every change' );

	t.end();
} );

test( 'AddressValidationDispatcher does nothing if data does not change', function ( t ) {
	var initialData = { amount: '2.00' },
		validator = { validate: sinon.spy() },
		testStore = { dispatch: sinon.spy() },
		dispatcher = createAddressValidationDispatcher(
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
		dispatcher = createAddressValidationDispatcher(
			validator,
			initialData
		);

	dispatcher.dispatchIfChanged( testData, testStore );

	t.notOk( validator.validate.called, 'validation function is never called' );
	t.notOk( testStore.dispatch.called, 'no action is dispatched' );

	t.end();
} );
