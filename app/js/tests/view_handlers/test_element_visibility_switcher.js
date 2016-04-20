'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	visibilitySwitching = require( '../../lib/view_handler/element_visibility_switcher' ),
	createElementCustomVisibilityHandler = visibilitySwitching.createElementCustomVisibilityHandler,
	createElementSlideAnimationHandler = visibilitySwitching.createElementSlideAnimationHandler,
	createElementVisibilityHandler = visibilitySwitching.createElementVisibilityHandler
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

test( 'Given a slide animation, When element is shown, element slides down', function ( t ) {
	var element = {
			slideDown: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideAnimationHandler( element, showValue );

	handler.update( showValue );

	t.ok( element.slideDown.calledOnce, 'slideDown was called once' );
	t.end();
} );

test( 'When state does not match value for showing, element slides up', function ( t ) {
	var element = {
			slideUp: sinon.stub().returnsThis(),
			animate: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementSlideAnimationHandler( element, showValue );

	handler.update( '23' );

	t.ok( element.slideUp.calledOnce, 'slideUp was called once' );
	t.end();
} );

test( 'Given a slide animation, When element is shown, element is shown', function ( t ) {
	var element = {
			show: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementVisibilityHandler( element, showValue );

	handler.update( showValue );

	t.ok( element.show.calledOnce, 'show was called once' );
	t.end();
} );

test( 'When state does not match value for showing, element is hidden', function ( t ) {
	var element = {
			hide: sinon.stub().returnsThis()
		},
		showValue = '4711',
		handler = createElementVisibilityHandler( element, showValue );

	handler.update( '23' );

	t.ok( element.hide.calledOnce, 'hide was called once' );
	t.end();
} );
