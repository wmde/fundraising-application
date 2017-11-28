'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	createValidationDispatcherCollection = require( '../lib/validation_dispatcher_collection' ).createValidationDispatcherCollection;

test( 'ValidationDispatcherCollection listens to store updates', function ( t ) {
	var storeSpy = {
			subscribe: sinon.spy()
		};

	createValidationDispatcherCollection( storeSpy, [], 'dummy' );

	t.ok( storeSpy.subscribe.calledOnce, 'mapper subscribes to store updates' );
	t.end();
} );

test( 'ValidationDispatcherCollection update method calls dispatchers', function ( t ) {
	var formContent = { amount: 42 },
		state = { donationForm: formContent },
		storeSpy = {
			subscribe: sinon.spy(),
			getState: sinon.stub().returns( state )
		},
		validatorSpy = { dispatchIfChanged: sinon.spy() },
		collection = createValidationDispatcherCollection( storeSpy, [ validatorSpy ], 'donationForm' );

	collection.onUpdate();

	t.ok( storeSpy.getState.calledOnce, 'onUpdate gets state from the store' );
	t.ok( validatorSpy.dispatchIfChanged.calledOnce, 'dispatchers are called' );
	t.ok( validatorSpy.dispatchIfChanged.calledWith( formContent, storeSpy ), 'dispatchers are called' );
	t.end();
} );

