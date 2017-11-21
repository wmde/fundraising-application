'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	amountField = require( '../../lib/view_handler/custom_amount_field' )
;

test( 'Creation appends special focus events', function ( t ) {
	var element = {
			on: sinon.stub()
		},
		field = amountField.createCustomAmountField( element )
	;

	t.ok( element.on.withArgs( 'focus focusout' ).calledOnce );
	t.end();
} );
