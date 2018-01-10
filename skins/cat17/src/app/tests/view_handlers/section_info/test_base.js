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
