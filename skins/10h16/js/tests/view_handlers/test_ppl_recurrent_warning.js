'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	pplRecurrentWarning = require( '../../lib/view_handler/recurrent_paypal_notice' ),
	createPPLRecurrentWarningHandler = pplRecurrentWarning.createRecurrentPaypalNoticeHandler
	;

function createAnimator() {
	return {
		showElement: sinon.spy(),
		hideElement: sinon.spy()
	};
}

test( 'When interval is recurring, warning is shown', function ( t ) {
	var animator = createAnimator(),
		handler = createPPLRecurrentWarningHandler( animator );

	handler.update( {
		paymentType: 'PPL',
		paymentIntervalInMonths: 12
	} );

	t.ok( animator.showElement.calledOnce, 'Element is shown' );
	t.notOk( animator.hideElement.called, 'Element is not hidden' );
	t.end();
} );

test( 'When interval is not recurring, warning is hidden', function ( t ) {
	var animator = createAnimator(),
		handler = createPPLRecurrentWarningHandler( animator );

	handler.update( {
		paymentType: 'PPL',
		paymentIntervalInMonths: 0
	} );

	t.ok( animator.hideElement.calledOnce, 'Element is hidden' );
	t.notOk( animator.showElement.called, 'Element is not shown' );
	t.end();
} );

test( 'When payment type is paypal and interval is recurring, warning is hidden', function ( t ) {
	var animator = createAnimator(),
		handler = createPPLRecurrentWarningHandler( animator );

	handler.update( {
		paymentType: 'BEZ',
		paymentIntervalInMonths: 12
	} );

	t.ok( animator.hideElement.calledOnce, 'Element is hidden' );
	t.notOk( animator.showElement.called, 'Element is not shown' );
	t.end();
} );

