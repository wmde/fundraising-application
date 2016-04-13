'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	storeUpdateHandling = require( '../lib/store_update_handling' )
	;

function createFakeStore( storeData ) {
	return {
		subscribe: sinon.spy(),
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

	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, {} );
	store.fakeUpdate();

	t.ok( validator.dispatchIfChanged.calledOnce, 'validator dispatch method is only called once' );
	t.ok( validator.dispatchIfChanged.calledWith( storeData.formContent ), 'validator dispatch method is called with the form contents' );
	t.end();
} );

test( 'initial values are passed to validator factory and merged with initial store formContent values', function ( t ) {
	var validator = {
			dispatchIfChanged: sinon.spy()
		},
		validatorFactory = sinon.stub().returns( [ validator ] ),
		initialStoreFormContent = { paymentType: 'PPL' },
		storeData = { formContent: initialStoreFormContent },
		store = createFakeStore( storeData ),
		initialValues = { paymentType: 'BTC' };

	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, {} );
	storeUpdateHandling.connectValidatorsToStore( validatorFactory, store, initialValues );

	t.ok(
		validatorFactory.firstCall.calledWith( initialStoreFormContent ),
		'validator factory is called with initial values from store'
	);
	t.ok(
		validatorFactory.secondCall.calledWith( initialValues ),
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

	storeUpdateHandling.connectComponentsToStore( [ component ], store );
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
