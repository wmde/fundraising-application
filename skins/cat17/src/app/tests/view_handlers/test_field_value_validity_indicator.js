'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	fieldValueValidityIndicator = require( '../../lib/view_handler/field_value_validity_indicator' ),
	createValidityIndicator = fieldValueValidityIndicator.createFieldValueValidityIndicator;

function ElementStub( sibling ) {
	this.addClassSpy = sinon.spy();
	this.removeClassSpy = sinon.spy();
	this.nextElement = sibling;
}

ElementStub.prototype = {
	addClass: function ( classToAdd ) {
		this.addClassSpy( classToAdd );
		return this;
	},

	removeClass: function ( classToRemove ) {
		this.removeClassSpy( classToRemove );
		return this;
	},

	next: function () {
		return this.nextElement;
	}
};

test( 'When validation state has initial status, nothing is indicated', function ( t ) {
	var indicatorElement = new ElementStub(),
		inputElement = new ElementStub( indicatorElement ),
		validationState = { dataEntered: false, isValid: null },
		handler = createValidityIndicator( inputElement );

	handler.update( validationState );

	t.ok( inputElement.addClassSpy.notCalled, 'validity indication keeps initial status' );
	t.ok( inputElement.removeClassSpy.withArgs( 'valid invalid' ), 'validity indication is set to neutral' );
	t.ok( indicatorElement.addClassSpy.withArgs( 'icon-placeholder' ), 'indicator is set to neutral placeholder' );
	t.ok( indicatorElement.removeClassSpy.withArgs( 'icon-ok icon-bug' ), 'icons are hidden' );
	t.end();
} );

test( 'When validation state has valid status, it is indicated', function ( t ) {
	var indicatorElement = new ElementStub(),
		inputElement = new ElementStub( indicatorElement ),
		validationState = { dataEntered: true, isValid: true },
		handler = createValidityIndicator( inputElement );

	handler.update( validationState );

	t.ok( inputElement.addClassSpy.withArgs( 'valid' ).calledOnce, 'input field set to valid' );
	t.ok( inputElement.removeClassSpy.withArgs( 'invalid' ).calledOnce, 'input field set to not invalid' );
	t.ok( indicatorElement.addClassSpy.withArgs( 'icon-ok' ).calledOnce, 'indicator set to valid' );
	t.ok( indicatorElement.removeClassSpy.withArgs( 'icon-bug icon-placeholder' ).calledOnce, 'indicator set to not invalid' );
	t.end();
} );

test( 'When validation state has invalid status, it is indicated', function ( t ) {
	var indicatorElement = new ElementStub(),
		inputElement = new ElementStub( indicatorElement ),
		validationState = { dataEntered: true, isValid: false },
		handler = createValidityIndicator( inputElement );

	handler.update( validationState );

	t.ok( inputElement.addClassSpy.withArgs( 'invalid' ).calledOnce, 'input field set to invalid' );
	t.ok( inputElement.removeClassSpy.withArgs( 'valid' ).calledOnce, 'input field set to not valid' );
	t.ok( indicatorElement.addClassSpy.withArgs( 'icon-bug' ).calledOnce, 'indicator set to invalid' );
	t.ok( indicatorElement.removeClassSpy.withArgs( 'icon-ok icon-placeholder' ).calledOnce, 'indicator set to not valid' );
	t.end();
} );

