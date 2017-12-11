'use strict';

var test = require( 'tape' ),
	IntegerCurrency = require( '../lib/integer_currency' )
;

test( 'German locale - formatting an integer', function ( t ) {
	var formatter = IntegerCurrency.createCurrencyFormatter( 'de' );
	var inputsAndExpectedOutputs = [
		[ 0, '0,00' ],
		[ 1, '0,01' ],
		[ 99, '0,99' ],
		[ 100, '1,00' ],
		[ 1000, '10,00' ],
		[ 1337, '13,37' ]
	];
	inputsAndExpectedOutputs.map( function ( io ) {
		t.equal( formatter.format( io[0] ), io[1] );
	});
	t.end();
} );

test( 'German locale - parsing valid strings', function ( t ) {
	var parser = IntegerCurrency.createCurrencyParser( 'de' );
	var inputsAndExpectedOutputs = [
		[ '0,00', 0 ],
		[ '0,01', 1 ],
		[ '0,99', 99 ],
		[ '1,00', 100 ],
		[ '10,00', 1000 ],
		[ '13,37', 1337 ],

		// long decimal values should be truncated
		[ '13,3373', 1333 ],
		[ '1,989', 198 ],
		[ '1,991', 199 ],
		[ '1,999', 199 ],
		[ '17,995', 1799 ],

		// Values with less than 2 decimal points should be valid
		[ '12', 1200 ],
		[ '12,9', 1290 ]
	];
	inputsAndExpectedOutputs.map( function ( io ) {
		t.equal( parser.parse( io[0] ), io[1] );
	});
	t.end();
} );

test( 'German locale - parsing invalid strings', function ( t ) {
	var parser = IntegerCurrency.createCurrencyParser( 'de' );
	var inputs = [
		'',
		',01',
		'A,1',
		'1,*',
		'CAFFE',
		'1.2',
		'1,2,3'
	];
	inputs.map( function ( invalidInput ) {
		t.throws( function() { parser.parse( invalidInput ); },
			'"' + invalidInput + '" should throw exception' );
	});
	t.end();
} );

test( 'English locale - formatting an integer ', function ( t ) {
	var formatter = IntegerCurrency.createCurrencyFormatter( 'en' );
	var inputsAndExpectedOutputs = [
		[ 0, '0.00' ],
		[ 1, '0.01' ],
		[ 99, '0.99' ],
		[ 100, '1.00' ],
		[ 1000, '10.00' ],
		[ 1337, '13.37' ]
	];
	inputsAndExpectedOutputs.map( function ( io ) {
		t.equal( formatter.format( io[0] ), io[1] );
	});
	t.end();
} );

test( 'English locale - parsing valid strings', function ( t ) {
	var parser = IntegerCurrency.createCurrencyParser( 'en' );
	var inputsAndExpectedOutputs = [
		[ '0.00', 0 ],
		[ '0.01', 1 ],
		[ '0.99', 99 ],
		[ '1.00', 100 ],
		[ '10.00', 1000 ],
		[ '13.37', 1337 ],

		// long decimal values should be truncated
		[ '13.3373', 1333 ],
		[ '1.989', 198 ],
		[ '1.991', 199 ],
		[ '1.999', 199 ],
		[ '17.995', 1799 ],

		// Values with less than 2 decimal points should be valid
		[ '12', 1200 ],
		[ '12.9', 1290 ]
	];
	inputsAndExpectedOutputs.map( function ( io ) {
		t.equal( parser.parse( io[0] ), io[1] );
	});
	t.end();
} );

test( 'English locale - parsing invalid strings', function ( t ) {
	var parser = IntegerCurrency.createCurrencyParser( 'en' );
	var inputs = [
		'',
		'.01',
		'A.1',
		'1.*',
		'CAFFE',
		'1,2',
		'1.2.3'
	];
	inputs.map( function ( invalidInput ) {
		t.throws( function() { parser.parse( invalidInput ); },
			'"' + invalidInput + '" should throw exception' );
	});
	t.end();
} );

test( 'Other locales than German and english throw and error', function ( t ) {
	t.throws( function () { IntegerCurrency.createCurrencyFormatter( 'fr' ); }, 'Unsupported locale' );
	t.throws( function () { IntegerCurrency.createCurrencyParser( 'fr' ); }, 'Unsupported locale' );
	t.end();
} );