'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	shyButton = require( '../../lib/view_handler/shy_submit_button' )
;

test( 'Given all positive values, element gets class removed', function ( t ) {
	var element = {
			toggleClass: sinon.stub()
		},
		handler = shyButton.createShySubmitButtonHandler( element )
	;

	handler.update( {
		a: true,
		b: true
	} );

	t.ok( element.toggleClass.withArgs( 'btn-unactive', false ).calledOnce );
	t.end();
} );

test( 'Given a faulty value, element gets class added', function ( t ) {
	var element = {
			toggleClass: sinon.stub()
		},
		handler = shyButton.createShySubmitButtonHandler( element )
	;

	handler.update( {
		a: true,
		b: false
	} );

	t.ok( element.toggleClass.withArgs( 'btn-unactive', true ).calledOnce );
	t.end();
} );

test( 'Given an undetermined value among positive ones, element gets class removed', function ( t ) {
	var element = {
			toggleClass: sinon.stub()
		},
		handler = shyButton.createShySubmitButtonHandler( element )
	;

	handler.update( {
		a: true,
		f: null,
		b: true
	} );

	t.ok( element.toggleClass.withArgs( 'btn-unactive', false ).calledOnce );
	t.end();
} );
