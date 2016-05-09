'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	feeOptionSwitcher = require( '../../lib/view_handler/fee_option_switcher' ),
	createFeeOptionSwitcher = feeOptionSwitcher.createFeeOptionSwitcher;

function createTestElement() {
	return {
		prop: sinon.spy()
	};
}

test( 'When value is below threshold, element gets enabled', function ( t ) {
	var element = createTestElement(),
		value = '1',
		handler = createFeeOptionSwitcher( element, 6 );
	handler.update( value );

	t.ok( !element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );

test( 'When value is above threshold, element gets disabled', function ( t ) {
	var element = createTestElement(),
		value = '12',
		handler = createFeeOptionSwitcher( element, 6 );
	handler.update( value );

	t.ok( element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( !element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );

test( 'When value equals threshold, element gets enabled', function ( t ) {
	var element = createTestElement(),
		value = '6',
		handler = createFeeOptionSwitcher( element, 6 );
	handler.update( value );

	t.ok( !element.prop.withArgs( 'disabled', true ).called, 'element was not disabled' );
	t.ok( element.prop.withArgs( 'disabled', false ).called, 'element was enabled' );
	t.end();
} );
