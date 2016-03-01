'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	reduxValidation = require( '../lib/redux_validation' );

test( 'ValidationWrapper calls validationFunction and returns validation action', function ( t ) {
	var successResult = { status: 'OK' },
		dummyAction = { type: 'TEST_VALIDATION' },
		testData = { testData: 'just some data which will be ignored' },
		validationFunction = sinon.stub().returns( successResult ),
		actionCreationFunction = sinon.stub().returns( dummyAction ),
		wrapper = reduxValidation.createValidationWrapper( validationFunction, actionCreationFunction ),

		validationResult = wrapper.validate( testData );

	t.ok( validationFunction.calledOnce, 'validation function is called once' );
	t.ok( validationFunction.calledWith( testData ), 'validation function is called with test data' );
	t.ok( actionCreationFunction.calledOnce, 'action is created' );
	t.ok( actionCreationFunction.calledWith( successResult ), 'action is created with validation result' );
	t.deepEqual( validationResult, dummyAction, 'validation wrapper should return action object' );
	t.end();
} );

test( 'ValidationWrapper accepts validator object as validation function', function ( t ) {
	var validatorSpy = {
			validatorDeletegate: sinon.spy(),
			validate: function ( formValues ) {
				return this.validatorDeletegate( formValues );
			}
		},
		testData = { testData: 'just some data which will be ignored' },
		actionCreationFunction = sinon.stub(),
		wrapper = reduxValidation.createValidationWrapper( validatorSpy, actionCreationFunction );

	wrapper.validate( testData );

	t.ok( validatorSpy.validatorDeletegate.calledOnce, 'validate function is called once' );
	t.ok( validatorSpy.validatorDeletegate.calledWith( testData ), 'validation function is called with test data' );
	t.end();
} );

test( 'ValidationWrapper does not create action if validation function returns undefined', function ( t ) {
	var testData = { testData: 'just some data which will be ignored' },
		validationFunction = sinon.stub().returns( undefined ),
		actionCreationFunction = sinon.stub(),
		wrapper = reduxValidation.createValidationWrapper( validationFunction, actionCreationFunction );

	t.equals( wrapper.validate( testData ), undefined, 'returns undefined instead of action object' );
	t.ok( actionCreationFunction.notCalled, 'action creation is avoided' );
	t.end();
} );

test( 'ValidationMapper connects validator functions to form content', function ( t ) {
	var formContent = { amount: 42 },
		storeSpy = {
			subscribe: sinon.spy(),
			dispatch: sinon.spy(),
			getState: sinon.stub().returns( { formContent: formContent, foo: '123' } )
		},
		validator = {
			validate: sinon.spy()
		},

		mapper = reduxValidation.createValidationMapper( storeSpy, [ validator ] );

	t.ok( storeSpy.subscribe.calledOnce, 'mapper subscribes to store updates' );

	mapper.onUpdate();

	t.ok( validator.validate.calledOnce, 'mapper calls validation on update' );
	t.ok( validator.validate.calledWith( formContent ), 'mapper selects form content for validation on update' );
	t.end();
} );

test( 'ValidationMapper dispatches validation action if it is an action object', function ( t ) {
	var formContent = { amount: 42 },
		storeSpy = {
			subscribe: sinon.spy(),
			dispatch: sinon.spy(),
			getState: sinon.stub().returns( { formContent: formContent } )
		},
		validators = [
			{ validate: function () { return null; } },
			{ validate: function () { return { thisIsNotAnAction: true }; } },
			{ validate: function () { return { type: 'VALIDATE_AMOUNT' }; } },
			{ validate: function () { return { type: 'VALIDATE_INPUT' }; } }
		],
		mapper = reduxValidation.createValidationMapper( storeSpy, validators );

	mapper.onUpdate();

	t.ok( storeSpy.dispatch.calledTwice, 'mapper dispatches actions' );
	t.ok( storeSpy.dispatch.calledWith( { type: 'VALIDATE_AMOUNT' } ), 'dispatches validation actions returned by validator' );
	t.end();
} );
