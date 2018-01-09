'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../../jQueryElementStub' ),
	createContainerElement = require( '../createContainerElement' ),
	AmountFrequency = require( '../../../../lib/view_handler/section_info/types/amount_frequency' ),
	formattedAmount = '23,00',
	currencyFormatter = {
		format: sinon.stub().returns( formattedAmount )
	}
;

test( 'The amount is passed to the currency formatter', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' },
			valueTextMap: { 0: 'lorem', 1: 'ipsum' },
			valueLongTextMap: { 0: 'lorem lorem', 1: 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 23.00, '0', { dataEntered: true, isValid: true } );

	t.ok( currencyFormatter.format.calledOnce, 'format is called' );
	t.equals( currencyFormatter.format.firstCall.args[ 0 ], 23.00, 'Amount is passed to formatter' );
	t.end();
} );

test( 'Formatted amount is set in amount element', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' },
			valueTextMap: { 0: 'lorem', 1: 'ipsum' },
			valueLongTextMap: { 0: 'lorem lorem', 1: 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 23.00, '0', { dataEntered: true, isValid: true } );

	t.ok( text.text.calledOnce, 'Amount is set' );
	t.equals( text.text.firstCall.args[ 0 ], formattedAmount + ' €', 'amount is set' );

	t.end();
} );

test( 'Icon is set according to value', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' },
			valueTextMap: { 0: 'lorem', 1: 'ipsum' },
			valueLongTextMap: { 0: 'lorem lorem', 1: 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 34.00, '1', { dataEntered: true, isValid: true } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-1' ).calledOnce );

	t.end();
} );

test( 'Icon is set to error if value out of bounds and error desired', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' },

			currencyFormatter: currencyFormatter
		} );

	icon.data.withArgs( 'display-error' ).returns( true );

	handler.update( 101, 'outOfBounds', { dataEntered: true, isValid: false } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-error' ).calledOnce );

	t.end();
} );

test( 'Icon is reset if value out of bounds and error not desired', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' },

			currencyFormatter: currencyFormatter
		} );

	icon.data.withArgs( 'display-error' ).returns( false );

	handler.update( 101, 'outOfBounds', { dataEntered: true, isValid: false } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.notCalled );

	t.end();
} );

test( 'No data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			currencyFormatter: currencyFormatter
		} );

	handler.update( null, null, { dataEntered: false, isValid: null } );

	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );

test( 'Valid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			currencyFormatter: currencyFormatter
		} );

	handler.update( null, null, { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );

	t.end();
} );

test( 'Invalid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			currencyFormatter: currencyFormatter
		} );

	handler.update( null, null, { dataEntered: true, isValid: false } );

	t.ok( container.addClass.withArgs( 'invalid' ).calledOnce );

	t.end();
} );

/**
 * This unintuitive state seems to be a possible outcome of
 * lib/state_aggregation/donation/amount_and_frequency_are_valid.js
 */
test( 'Incomplete validity correctly reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( AmountFrequency ), {
			container: container,

			currencyFormatter: currencyFormatter
		} );

	handler.update( null, null, { dataEntered: true, isValid: null } );

	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );
