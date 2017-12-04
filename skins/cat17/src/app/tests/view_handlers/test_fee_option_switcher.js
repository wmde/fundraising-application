'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	feeOptionSwitcher = require( '../../lib/view_handler/fee_option_switcher' ),
	createFeeOptionSwitcher = feeOptionSwitcher.createFeeOptionSwitcher;

function createParentElement() {
	return {
		addClass: sinon.stub(),
		removeClass: sinon.stub()
	}
}

function createTestElement( value, parentElement ) {
	return {
		val: sinon.stub().returns( value ),
		prop: sinon.spy(),
		parent: sinon.stub().returns( parentElement )
	};
}

function getMinimumFees() {
	return {
		person: 24,
		firma: 100
	};
}

test( 'When value is below threshold, element gets enabled', function ( t ) {
	var parentElement = createParentElement(),
		element = createTestElement( 5, parentElement ),
		state = { paymentIntervalInMonths: 1, addressType: 'person' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.ok( parentElement.removeClass.withArgs( 'disabled' ).calledOnce  );
	t.end();
} );

test( 'When value is above threshold, element gets disabled', function ( t ) {
	var parentElement = createParentElement(),
		element = createTestElement( 5, parentElement ),
		state = { paymentIntervalInMonths: 12, addressType: 'person' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( parentElement.addClass.withArgs( 'disabled' ).calledOnce  );
	t.end();
} );

test( 'When value equals threshold, element gets enabled', function ( t ) {
	var parentElement = createParentElement(),
		element = createTestElement( 50, parentElement ),
		state = { paymentIntervalInMonths: 6, addressType: 'firma' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.ok( parentElement.removeClass.withArgs( 'disabled' ).calledOnce  );
	t.end();
} );
