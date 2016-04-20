'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createElementCustomVisibilityHandler = require( '../../lib/view_handler/element_visibility_switcher' ).createElementCustomVisibilityHandler
	;

function createTestAnimator() {
	return {
		showElement: sinon.spy(),
		hideElement: sinon.spy()
	};
}

test( 'When state matches value for showing, element is shown', function ( t ) {
	var animator = createTestAnimator(),
		showValue = '4711',
		handler = createElementCustomVisibilityHandler( sinon.stub(), showValue, animator );

	handler.update( '4711' );

	t.ok( animator.showElement.calledOnce, 'showElement was called once' );
	t.end();
} );

test( 'Given a matching regular expression as value for showing, element is shown', function ( t ) {
	var animator = createTestAnimator(),
		showValue = /^.711$/,
		handler = createElementCustomVisibilityHandler( sinon.stub(), showValue, animator );

	handler.update( '4711' );

	t.ok( animator.showElement.calledOnce, 'showElement was called once' );
	t.end();
} );

test( 'When state matches value for showing and element is already showing, element does not change', function ( t ) {
	var animator = createTestAnimator(),
		showValue = '4711',
		handler = createElementCustomVisibilityHandler( sinon.stub(), showValue, animator );

	handler.update( '4711' );
	handler.update( '4711' );

	t.ok( animator.showElement.calledOnce, 'showElement was called once' );
	t.end();
} );

test( 'When state does not match value for showing, element is hidden', function ( t ) {
	var animator = createTestAnimator(),
		showValue = '4711',
		handler = createElementCustomVisibilityHandler( sinon.stub(), showValue, animator );

	handler.update( '47' );

	t.ok( animator.hideElement.calledOnce, 'hideElement was called once' );
	t.end();
} );

test( 'When state matches value for hiding and element is already hidden, element does not change', function ( t ) {
	var animator = createTestAnimator(),
		showValue = '4711',
		handler = createElementCustomVisibilityHandler( sinon.stub(), showValue, animator );

	handler.update( '47' );
	handler.update( '47' );

	t.ok( animator.hideElement.calledOnce, 'hideElement was called once' );
	t.end();
} );
