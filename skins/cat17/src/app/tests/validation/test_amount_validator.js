import { test } from 'tape-catch';
import sinon from 'sinon';
import Promise from 'promise';
import ValidationStates from '../../lib/validation/validation_states';
import AmountValidator from '../../lib/validation/amount_validator';

test( 'Amount validation sends values to server', function ( t ) {
	const positiveResult = { status: ValidationStates.OK };
	const postFunctionSpy = sinon.stub().returns( Promise.resolve( positiveResult ) );
	const amountValidator = new AmountValidator(
		'http://spenden.wikimedia.org/validate-donation-amount',
		{ postData: postFunctionSpy }
	);

	return amountValidator.validate( { amount: 23, otherStuff: 'foo' } )
		.then( function ( validationResult ) {
			const callParameters = postFunctionSpy.getCall( 0 ).args;
			t.ok( postFunctionSpy.calledOnce, 'data is sent once' );
			t.equal( callParameters[ 0 ], 'http://spenden.wikimedia.org/validate-donation-amount', 'validation calls configured URL' );
			t.deepEqual( callParameters[ 1 ], { amount: 23 }, 'validation sends only necessary data' );
			t.deepEqual( validationResult, positiveResult, 'validation function returns promised result' );
			t.end();
		} );
} );

test( 'Amount validation converts transport errors to invalid result', function ( t ) {
	const negativeResult = { status: ValidationStates.ERR, messages: { transportError: 'Internal Server Error' } };
	const failingTransport = sinon.stub().returns( Promise.reject( 'Internal Server Error' ) );
	const amountValidator = new AmountValidator(
		'http://spenden.wikimedia.org/validate-donation-amount',
		{ postData: failingTransport }
	);

	return amountValidator.validate( { amount: 23, otherStuff: 'foo' } )
		.then( function ( validationResult ) {
			t.ok( failingTransport.calledOnce, 'data is sent once' );
			t.deepEqual( validationResult, negativeResult, 'validation function returns error result' );
			t.end();
		} );
} );

test( 'Amount validation sends nothing to server if any of the necessary values are not set', function ( t ) {
	const incompleteResult = { status: ValidationStates.INCOMPLETE };
	const postFunctionSpy = sinon.spy();
	const amountValidator = new AmountValidator(
		'http://spenden.wikimedia.org/validate-donation-amount',
		{ postData: postFunctionSpy }
	);

	return amountValidator.validate( { amount: 0, otherStuff: 'foo' } )
		.then( function ( result ) {
			t.notOk( postFunctionSpy.called, 'no data is sent ' );
			t.deepEquals( incompleteResult, result, 'validation function returns incomplete result' );
			t.end();
		} );
} );
