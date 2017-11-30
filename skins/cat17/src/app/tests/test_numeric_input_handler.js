'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	NumericInputHandler = require( '../lib/numeric_input_handler' )
;

test( 'Attaching adds listener on keypress', function ( t ) {
	var element = {
			on: sinon.stub()
		}
	;

	NumericInputHandler.createNumericInputHandler( element );

	t.ok( element.on.withArgs( 'keypress' ).calledOnce );
	t.end();
} );

test( 'Handler lets number presses pass', function ( t ) {
	var numberKeys = {
			0: 48,
			1: 49,
			2: 50,
			3: 51,
			4: 52,
			5: 53,
			6: 54,
			7: 55,
			8: 56,
			9: 57
		},
		element = {
			on: sinon.stub(),
			val: sinon.stub().returns('')
		},
		handler = NumericInputHandler.createNumericInputHandler( element ),
		event, keyCode, number
	;

	for (number in numberKeys) {
		keyCode = numberKeys[ number ];
		event = {
			preventDefault: sinon.stub(),
			keyCode: keyCode,
			ctrlKey: false
		};

		handler.handle( event );

		t.ok( event.preventDefault.notCalled, 'not preventing default behavior' );
	}

	t.end();
} );

test( 'Handler lets configured delimiter press pass', function ( t ) {
	var element = {
			on: sinon.stub(),
			val: sinon.stub().returns('')
		},
		handler = NumericInputHandler.createNumericInputHandler( element, ',' ),
		event = {
			preventDefault: sinon.stub(),
			keyCode: 44,
			ctrlKey: false
		};

	handler.handle( event );

	t.ok( event.preventDefault.notCalled, 'not preventing default behavior' );

	t.end();
} );

test( 'Prevents invalid delimiter press', function ( t ) {
	var element = {
			on: sinon.stub(),
			val: sinon.stub().returns('')
		},
		handler = NumericInputHandler.createNumericInputHandler( element, ',' ),
		event = {
			preventDefault: sinon.stub(),
			keyCode: 46,
			ctrlKey: false
		};

	handler.handle( event );

	t.ok( event.preventDefault.calledOnce, 'preventing default behavior' );

	t.end();
} );

test( 'Prevents multiple valid delimiter pressed', function ( t ) {
	var element = {
			on: sinon.stub(),
			val: sinon.stub().returns('.')
		},
		handler = NumericInputHandler.createNumericInputHandler( element, '.' ),
		event = {
			preventDefault: sinon.stub(),
			keyCode: 46,
			ctrlKey: false
		};

	handler.handle( event );

	t.ok( event.preventDefault.calledOnce, 'preventing default behavior' );

	t.end();
} );

