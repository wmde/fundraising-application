'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	storeUpdateHandling = require( '../lib/store_update_handling' )
	;

function createFakeStore( storeData ) {
	var unsubscribe = sinon.spy();
	return {
		// not an actual method of Redux store but convenient to access in tests
		unsubscribe: unsubscribe,
		subscribe: sinon.stub().returns( unsubscribe ),
		getState: sinon.stub().returns( storeData ),
		// Call all callback functions that were registered through calls to `subscribe`
		fakeUpdate: function () {
			var subscribeCalls = this.subscribe.args;
			subscribeCalls.forEach( function ( subscribeArguments ) {
				// get the first argument to the `subscribe` call
				var updateCallback = subscribeArguments[ 0 ];
				// call the callback, using its own context
				updateCallback.call( updateCallback );
			} );
		}
	};
}

test( 'connect validators to store updates', function ( t ) {
	var validator = {
			dispatchIfChanged: sinon.spy()
		},
		validatorFactory = sinon.stub().returns( [ validator ] ),
		storeData = { formContent: { test: 'dummy store contents' } },
		store = createFakeStore( storeData );

	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, {}, 'formContent' );
	store.fakeUpdate();

	t.ok( validator.dispatchIfChanged.calledOnce, 'validator dispatch method is only called once' );
	t.ok( validator.dispatchIfChanged.calledWith( storeData.formContent ), 'validator dispatch method is called with the form contents' );
	t.end();
} );

test( 'initial values passed to validator factory default to formValues from the store', function ( t ) {
	var validator = {
			dispatchIfChanged: sinon.spy()
		},
		validatorFactory = sinon.stub().returns( [ validator ] ),
		initialStoreFormContent = { paymentType: 'PPL' },
		storeData = { formContent: initialStoreFormContent },
		store = createFakeStore( storeData );

	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, {}, 'formContent' );

	t.ok(
		validatorFactory.firstCall.calledWith( initialStoreFormContent ),
		'validator factory is called with initial values from store'
	);
	t.end();
} );

test( 'initial values are passed to validator factory can be changed on initialization', function ( t ) {
	var validator = {
			dispatchIfChanged: sinon.spy()
		},
		validatorFactory = sinon.stub().returns( [ validator ] ),
		initialStoreFormContent = { paymentType: 'PPL' },
		storeData = { formContent: initialStoreFormContent },
		store = createFakeStore( storeData ),
		initialValues = { paymentType: 'BTC' };

	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, initialValues, 'formContent' );

	t.ok(
		validatorFactory.firstCall.calledWith( initialValues ),
		'validator factory is called with initial values from store'
	);
	t.end();
} );

test( 'connect components to store updates', function ( t ) {
	var component = {
			render: sinon.spy()
		},
		storeData = { formContent: { test: 'dummy store contents' } },
		store = createFakeStore( storeData );

	storeUpdateHandling.connectComponentsToStore( [ component ], store, 'formContent' );
	store.fakeUpdate();

	t.ok( component.render.calledOnce, 'render method of component is called once' );
	t.ok( component.render.calledWith( storeData.formContent ), 'render method is called with the form contents' );
	t.end();
} );

test( 'connect view handlers to store updates', function ( t ) {
	var viewHandler = {
			update: sinon.spy()
		},
		viewHandlerConfig = {
			viewHandler: viewHandler,
			stateKey: 'numberOfCats'
		},
		storeData = { numberOfCats: 42, numberOfDogs: 23 },
		store = createFakeStore( storeData );

	storeUpdateHandling.connectViewHandlersToStore( [ viewHandlerConfig ], store );
	store.fakeUpdate();

	t.ok( viewHandler.update.calledOnce, 'view handler update method is only called once' );
	t.ok( viewHandler.update.calledWith( 42 ), 'view handler get passed only selected state parts' );
	t.end();
} );

test( 'connect view handlers to deeply nested store values', function ( t ) {
	var viewHandler = {
			update: sinon.spy()
		},
		viewHandlerConfig = {
			viewHandler: viewHandler,
			stateKey: 'facts.cats.number'
		},
		storeData = { facts: { cats: { number: 42, owners: 3 } } },
		store = createFakeStore( storeData );

	storeUpdateHandling.connectViewHandlersToStore( [ viewHandlerConfig ], store );
	store.fakeUpdate();

	t.ok( viewHandler.update.calledOnce, 'view handler update method is only called once' );
	t.ok( viewHandler.update.calledWith( 42 ), 'view handler get passed only selected state parts' );
	t.end();
} );

test( 'when not validating, makeEventHandlerWaitForAsyncFinish executes handler instantly ', function ( t ) {
	var eventHandler = sinon.spy(),
		storeData = { asynchronousRequests: { isValidating: false } },
		store = createFakeStore( storeData ),
		wrappedHandler = storeUpdateHandling.makeEventHandlerWaitForAsyncFinish( eventHandler, store );

	wrappedHandler();

	t.ok( eventHandler.calledOnce );
	t.end();
} );

test( 'when validating, makeEventHandlerWaitForAsyncFinish adds a new subscription to store ', function ( t ) {
	var eventHandler = sinon.spy(),
		storeData = { asynchronousRequests: { isValidating: true } },
		store = createFakeStore( storeData ),
		wrappedHandler = storeUpdateHandling.makeEventHandlerWaitForAsyncFinish( eventHandler, store );

	wrappedHandler();

	t.ok( store.subscribe.calledOnce, 'a new subscription was added' );
	t.notOk( eventHandler.called, 'event handler should not be called' );
	t.end();
} );

test( 'calling the subscription handler created by makeEventHandlerWaitForAsyncFinish and still validating, nothing happens', function ( t ) {
	var eventHandler = sinon.spy(),
		storeData = { asynchronousRequests: { isValidating: true } },
		store = createFakeStore( storeData ),
		wrappedHandler = storeUpdateHandling.makeEventHandlerWaitForAsyncFinish( eventHandler, store );

	wrappedHandler();
	store.fakeUpdate();

	t.notOk( eventHandler.called, 'event handler should not be called' );
	t.end();
} );

test( 'calling the subscription handler created by makeEventHandlerWaitForAsyncFinish and no longer validating, event handler is called', function ( t ) {
	var eventHandler = sinon.spy(),
		storeData = { asynchronousRequests: { isValidating: true } },
		store = createFakeStore( storeData ),
		wrappedHandler = storeUpdateHandling.makeEventHandlerWaitForAsyncFinish( eventHandler, store );

	wrappedHandler();
	store.getState = sinon.stub().returns( { asynchronousRequests: { isValidating: false } } );
	store.fakeUpdate();

	t.ok( eventHandler.calledOnce, 'event handler must be be called' );
	t.end();
} );

test( 'calling the subscription handler created by makeEventHandlerWaitForAsyncFinish and no longer validating, unsubscribes from store', function ( t ) {
	var eventHandler = sinon.spy(),
		storeData = { asynchronousRequests: { isValidating: true } },
		store = createFakeStore( storeData ),
		wrappedHandler = storeUpdateHandling.makeEventHandlerWaitForAsyncFinish( eventHandler, store );

	wrappedHandler();
	store.getState = sinon.stub().returns( { asynchronousRequests: { isValidating: false } } );
	store.fakeUpdate();

	t.ok( store.unsubscribe.calledOnce, 'event handler must be be called' );
	t.end();
} );
