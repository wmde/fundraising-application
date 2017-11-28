'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	feeOptionSwitcher = require( '../../lib/view_handler/fee_option_switcher' ),
	createFeeOptionSwitcher = feeOptionSwitcher.createFeeOptionSwitcher;

function createTestElement( value ) {
	return {
		val: function () { return value; },
		prop: sinon.spy()
	};
}

function getMinimumFees() {
	return {
		person: 24,
		firma: 100
	};
}

test( 'When value is below threshold, element gets enabled', function ( t ) {
	var element = createTestElement( 5 ),
		state = { paymentIntervalInMonths: 1, addressType: 'person' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( !element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );

test( 'When value is above threshold, element gets disabled', function ( t ) {
	var element = createTestElement( 5 ),
		state = { paymentIntervalInMonths: 12, addressType: 'person' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( !element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );

test( 'When value equals threshold, element gets enabled', function ( t ) {
	var element = createTestElement( 50 ),
		state = { paymentIntervalInMonths: 6, addressType: 'firma' },
		handler = createFeeOptionSwitcher( [ element ], getMinimumFees() );
	handler.update( state );

	t.ok( !element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );
