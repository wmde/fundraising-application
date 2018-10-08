'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	fieldValueValidityIndicator = require( '../../lib/view_handler/field_value_validity_indicator' ),
	createValidityIndicator = fieldValueValidityIndicator.createFieldValueValidityIndicator;

function ElementStub() {
	this.addClassSpy = sinon.spy();
	this.removeClassSpy = sinon.spy();
}

ElementStub.prototype = {
	addClass: function ( classToAdd ) {
		this.addClassSpy( classToAdd );
		return this;
	},

	removeClass: function ( classToRemove ) {
		this.removeClassSpy( classToRemove );
		return this;
	}
};

test( 'When validation state has initial status, nothing is indicated', function ( t ) {
	var inputContainer = new ElementStub(),
		validationState = { dataEntered: false, isValid: null },
		handler = createValidityIndicator( inputContainer );

	handler.update( validationState );

	t.ok( inputContainer.addClassSpy.notCalled, 'validity indication keeps initial status' );
	t.ok( inputContainer.removeClassSpy.withArgs( 'valid invalid' ), 'validity indication is set to neutral' );
	t.end();
} );

test( 'When validation state has valid status, it is indicated', function ( t ) {
	var inputContainer = new ElementStub(),
		validationState = { dataEntered: true, isValid: true },
		handler = createValidityIndicator( inputContainer );

	handler.update( validationState );

	t.ok( inputContainer.addClassSpy.withArgs( 'valid' ).calledOnce, 'input field set to valid' );
	t.ok( inputContainer.removeClassSpy.withArgs( 'invalid' ).calledOnce, 'input field set to not invalid' );
	t.end();
} );

test( 'When validation state has invalid status, it is indicated', function ( t ) {
	var inputContainer = new ElementStub(),
		validationState = { dataEntered: true, isValid: false },
		handler = createValidityIndicator( inputContainer );

	handler.update( validationState );

	t.ok( inputContainer.addClassSpy.withArgs( 'invalid' ).calledOnce, 'input field set to invalid' );
	t.ok( inputContainer.removeClassSpy.withArgs( 'valid' ).calledOnce, 'input field set to not valid' );
	t.end();
} );
