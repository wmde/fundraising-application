'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	validation = require( '../lib/form_validation' );

test( 'Amount validation sends values to server', function ( t ) {
	var positiveResult = { status: 'OK' },
		postFunctionSpy = sinon.stub().returns( positiveResult ),
		amountValidator = validation.createAmountValidator(
			'http://spenden.wikimedia.org/validate-amount',
			postFunctionSpy
		),
		callParameters, validationResult;

	validationResult = amountValidator.validate( { amount: 23, paymentType: 'BEZ', otherStuff: 'foo' } );

	t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
	callParameters = postFunctionSpy.getCall( 0 ).args;
	t.equals( callParameters[ 0 ], 'http://spenden.wikimedia.org/validate-amount', 'validation calls configured URL' );
	t.deepEquals( callParameters[ 1 ], { amount: 23, paymentType: 'BEZ' }, 'validation sends only necessary data' );
	t.equals( callParameters[ 3 ], 'json', 'validation expects JSON data' );
	t.deepEqual( validationResult, {
		type: 'VALIDATE_AMOUNT',
		payload: positiveResult
	}, 'validation function returns action with result' );
	t.end();
} );

test( 'Validation sends values to server only when they changed', function ( t ) {
	var postFunctionSpy = sinon.spy(),
		amountValidator = validation.createAmountValidator(
			'http://spenden.wikimedia.org/validate-amount',
			postFunctionSpy
		),
		formValues = { amount: 23, paymentType: 'BEZ', otherStuff: 'foo' };

	amountValidator.validate( formValues );
	amountValidator.validate( formValues );

	t.ok( postFunctionSpy.calledOnce, 'data is sent only once when input data is the same' );

	formValues.amount = 42;
	amountValidator.validate( formValues );

	t.ok( postFunctionSpy.calledTwice, 'data is sent when input data changes' );

	t.end();
} );

test( 'ValidationMapper connects validator functions to form content', function ( t ) {
	var storeSpy = {
			subscribe: sinon.spy(),
			dispatch: sinon.spy()
		},
		validator = {
			validate: sinon.spy()
		},
		formContent = { amount: 42 },
		mapper = validation.createValidationMapper( storeSpy, [ validator ] );

	t.ok( storeSpy.subscribe.calledOnce, 'mapper subscribes to store updates' );

	mapper.onUpdate( { foo: '123', formContent: formContent } );

	t.ok( validator.validate.calledOnce, 'mapper calls validation on update' );
	t.ok( validator.validate.calledWith( formContent ), 'mapper selects form content for validation on update' );
	t.end();
} );

test( 'ValidationMapper dispatches validation action if it is an action object', function ( t ) {
	var storeSpy = {
			subscribe: sinon.spy(),
			dispatch: sinon.spy()
		},
		validators = [
			{ validate: function () { return null; } },
			{ validate: function () { return { thisIsNotAnAction: true }; } },
			{ validate: function () { return { type: 'VALIDATE_AMOUNT' }; } },
			{ validate: function () { return { type: 'VALIDATE_INPUT' }; } }
		],
		formContent = { amount: 42 },
		mapper = validation.createValidationMapper( storeSpy, validators );

	mapper.onUpdate( { formContent: formContent } );

	t.ok( storeSpy.dispatch.calledTwice, 'mapper dispatches actions' );
	t.ok( storeSpy.dispatch.calledWith( { type: 'VALIDATE_AMOUNT' } ), 'dispatches validation actions returned by validator' );
	t.end();
} );
