'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	switcher = require( '../../lib/view_handler/element_class_switcher' )
;

test( 'Given correct value, element gets class applied', function ( t ) {
	var element = {
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		handler = switcher.createElementClassSwitcher( element, '4711', 'jochen' )
	;

	handler.update( '4711' );

	t.ok( element.addClass.withArgs( 'jochen' ).calledOnce );
	t.end();
} );

test( 'Given wrong value, element gets class removed again', function ( t ) {
	var element = {
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		handler = switcher.createElementClassSwitcher( element, '4711', 'heureka' )
	;

	handler.update( 'somethingsomehting' );

	t.ok( element.removeClass.withArgs( 'heureka' ).calledOnce );
	t.end();
} );
