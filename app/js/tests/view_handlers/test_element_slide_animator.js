'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createElementSlideHandler = require( '../../lib/view_handler/element_slide_animator' ).createHandler
	;

test( 'When state matches value for showing, element slides in', function ( t ) {
	var element = {
			slideDown: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideHandler( element, showValue );

	handler.update( showValue );

	t.ok( element.slideDown.calledOnce, 'slideDown was called once' );
	t.end();
} );

test( 'Given a matching regular expression as value for showing, element slides in', function ( t ) {
	var element = {
			slideDown: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		handler = createElementSlideHandler( element, /^.711$/ );

	handler.update( '4711' );

	t.ok( element.slideDown.calledOnce, 'slideDown was called once' );
	t.end();
} );

test( 'When state matches value for showing and element is already showing, element does not change', function ( t ) {
	var element = {
			slideDown: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideHandler( element, showValue );

	handler.update( showValue );
	handler.update( showValue );

	t.ok( element.slideDown.calledOnce, 'slideDown was only called once' );
	t.end();
} );

test( 'When state does not match value for showing, element slides up', function ( t ) {
	var element = {
			slideUp: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideHandler( element, showValue );

	handler.update( '23' );

	t.ok( element.slideUp.calledOnce, 'slideUp was called once' );
	t.end();
} );

test( 'When state matches value for showing and element is already showing, element does not change', function ( t ) {
	var element = {
			slideUp: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideHandler( element, showValue );

	handler.update( '23' );
	handler.update( '23' );

	t.ok( element.slideUp.calledOnce, 'slideUp was only called once' );
	t.end();
} );
