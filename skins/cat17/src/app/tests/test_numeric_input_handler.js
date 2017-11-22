'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	NumericInputHandler = require( '../lib/numeric_input_handler' )
;

test( 'Attaching adds listener on keypress', function ( t ) {
	var element = {
			on: sinon.stub()
		}
	;

	NumericInputHandler.attach( element );

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
		event, keyCode, number
	;

	for (number in numberKeys) {
		keyCode = numberKeys[ number ];
		event = {
			preventDefault: sinon.stub(),
			keyCode: keyCode,
			ctrlKey: false
		};

		NumericInputHandler.Handler.handle( event );

		t.ok( event.preventDefault.notCalled, 'not preventing default behavior' );
		t.equal( event.keyCode, keyCode, 'keycode unmodified' );
	}

	t.end();
} );
