'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../jQueryElementStub' ),
	createContainerElement = require( './createContainerElement' ),
	Base = require( '../../../lib/view_handler/section_info/base' )
;

test( 'Fallback text is used when value does not correspond to text map', function ( t ) {
	var container = createContainerElement(),
		text = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			text: text,

			valueTextMap: { BEZ: 'Lastschrift', PPL: 'Paypal' }
		} );

	text.data.withArgs( 'empty-text' ).returns( 'Bitcoin' );

	handler.update( 'BTC' );

	t.ok( text.data.withArgs( 'empty-text' ).calledOnce, 'Fetches default text' );
	t.ok( text.text.withArgs( 'Bitcoin' ).calledOnce, 'Payment type is set' );

	t.end();
} );

test( 'No data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( Base ), {
			container: container
		} );

	handler.setSectionStatusFromValidity( { dataEntered: false, isValid: null } );

	t.ok( container.removeClass.withArgs( 'completed disabled invalid' ).calledOnce );
	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );

test( 'Valid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( Base ), {
			container: container
		} );

	handler.setSectionStatusFromValidity( { dataEntered: true, isValid: true } );

	t.ok( container.removeClass.withArgs( 'completed disabled invalid' ).calledOnce );
	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );

	t.end();
} );

test( 'Invalid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( Base ), {
			container: container
		} );

	handler.setSectionStatusFromValidity( { dataEntered: true, isValid: false } );

	t.ok( container.removeClass.withArgs( 'completed disabled invalid' ).calledOnce );
	t.ok( container.addClass.withArgs( 'invalid' ).calledOnce );

	t.end();
} );

/**
 * This unintuitive state seems to be a possible outcome of
 * lib/state_aggregation/membership/membership_type_is_valid.js
 */
test( 'Incomplete membership_type validity correctly reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( Base ), {
			container: container
		} );

	handler.setSectionStatusFromValidity( { dataEntered: false, isValid: false } );

	t.ok( container.removeClass.withArgs( 'completed disabled invalid' ).calledOnce );
	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );

/**
 * This unintuitive state seems to be a possible outcome of
 * lib/state_aggregation/donation/amount_and_frequency_are_valid.js
 */
test( 'Incomplete amount_and_frequency validity correctly reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( Base ), {
			container: container
		} );

	handler.setSectionStatusFromValidity( { dataEntered: true, isValid: null } );

	t.ok( container.removeClass.withArgs( 'completed disabled invalid' ).calledOnce );
	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );

test( 'Icon is correctly determined from value', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	t.equals( handler.getValueIcon( 1 ), 'icon-1' );

	t.end();
} );

test( 'Icon is null if can not be determined from value and no error display', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	t.equals( handler.getValueIcon( 4 ), null );

	t.end();
} );

test( 'Icon is error if can not be determined from value and error display set', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	icon.data.withArgs( 'display-error' ).returns( true );

	t.equals( handler.getValueIcon( 5 ), 'icon-error' );

	t.end();
} );

test( 'Icon class set according to value', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	handler.setIcon( 'icon-1' );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-1' ).calledOnce );

	t.end();
} );

test( 'Icon error set correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	handler.setIcon( 'icon-error' );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-error' ).calledOnce );

	t.end();
} );

test( 'Icon class reset if no class name passed', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			icon: icon,

			valueIconMap: { 0: 'icon-0', 1: 'icon-1' }
		} );

	handler.setIcon( null );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.notCalled );

	t.end();
} );
