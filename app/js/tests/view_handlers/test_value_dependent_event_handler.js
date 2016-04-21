'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createValueDependentEventHandler = require( '../../lib/view_handler/value_dependent_event_handler' ).createHandler;

test( 'When the handler is created it attaches it attaches an event listener', function ( t ) {
	var eventHandlerConfig = [],
		eventName = 'click',
		element = {
			on: sinon.spy()
		},
		onArguments;

	createValueDependentEventHandler( element, eventName, eventHandlerConfig );
	t.ok( element.on.calledOnce, 'An event handler was attached' );
	onArguments = element.on.args[ 0 ];
	t.equals( onArguments[ 0 ], eventName );
	t.equals( typeof onArguments[ 1 ], 'function' );
	t.end();

} );

test( 'Given a matching value, only the configured event handler is triggered', function ( t ) {
	var fooHandler = sinon.spy(),
		barHandler = sinon.spy(),
		eventHandlerConfig = [
			[ /foo/, fooHandler ],
			[ /bar/, barHandler ]
		],
		eventName = 'click',
		element = {
			on: sinon.spy()
		},
		handler = createValueDependentEventHandler( element, eventName, eventHandlerConfig );

	handler.update( 'confoosed' );
	element.on.args[ 0 ][ 1 ].call();

	t.ok( fooHandler.calledOnce, 'matching handler was called' );
	t.ok( barHandler.callCount === 0, 'non-matching handler was not called' );
	t.end();
} );

test( 'Given a changed value, only the matching event handlers are triggered', function ( t ) {
	var fooHandler = sinon.spy(),
		barHandler = sinon.spy(),
		ianHandler = sinon.spy(),
		eventHandlerConfig = [
			[ /foo/, fooHandler ],
			[ /bar/, barHandler ],
			[ /ian/, ianHandler ]
		],
		eventName = 'click',
		element = {
			on: sinon.spy()
		},
		handler = createValueDependentEventHandler( element, eventName, eventHandlerConfig );

	handler.update( 'confoosed' );
	handler.update( 'barbarian' );
	element.on.args[ 0 ][ 1 ].call();

	t.ok( fooHandler.callCount === 0, 'non-matching handler was not called' );
	t.ok( barHandler.calledOnce && ianHandler.calledOnce, 'matching handlers were called' );
	t.end();
} );
