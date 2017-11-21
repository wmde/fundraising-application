'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	inputHandler = require( '../lib/numeric_input_handler' )
;

test( 'Attaching adds listener on keypress', function ( t ) {
	var element = {
			on: sinon.stub()
		}
	;

	inputHandler.attach( element );

	t.ok( element.on.withArgs( 'keypress' ).calledOnce );
	t.end();
} );
