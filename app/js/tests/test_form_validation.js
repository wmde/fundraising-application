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
	t.deepEqual( validationResult, positiveResult, 'validation function returns result' );
	t.end();
} );

