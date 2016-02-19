'use strict';

var test = require( 'tape' ),
	amountCollector = require( '../lib/amount_collector' );

test( 'AmountCollector factory validates input sources', function ( t ) {
	var valStub = function () { return 0; };
	t.throws( function () {
		amountCollector.createAmountCollector( { }, { val: valStub } );
	} );
	t.throws( function () {
		amountCollector.createAmountCollector( { val: valStub }, { } );
	} );
	t.doesNotThrow( function () {
		amountCollector.createAmountCollector( { val: valStub }, { val: valStub } );
	} );
	t.end();
} );

test( 'getAmount returns first truthy value', function ( t ) {
	var getValStub = function ( returnValue ) {
		return function () { return returnValue; };
	};

	t.equal(
		amountCollector.createAmountCollector( { val: getValStub( null ) }, { val: getValStub( 42 ) } ).getAmount(),
		42
	);

	t.equal(
		amountCollector.createAmountCollector( { val: getValStub( 23 ) }, { val: getValStub( undefined ) } ).getAmount(),
		23
	);

	t.equal(
		amountCollector.createAmountCollector( { val: getValStub( 23 ) }, { val: getValStub( 42 ) } ).getAmount(),
		23
	);

	t.end();
} );
