'use strict';

var test = require( 'tape' ),
	createCurrencyFormatter = require( '../lib/simple_currency_formatter' ).createCurrencyFormatter,
	expectedFormattedAmountInGermanLocale = '23,00' + String.fromCharCode( 160 ) + '€',
	expectedFormattedAmountInEnglishLocale = '23.00' + String.fromCharCode( 160 ) + '€'
	;

test( 'German locale', function ( t ) {

	var locale = 'de';

	t.test( 'Given amount with decimal comma, it is rendered as-is', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23,00' ), expectedFormattedAmountInGermanLocale, 'Amount is preserved' );
		t.end();
	} );

	t.test( 'Given amount with missing decimal places, they are added', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23,0' ), expectedFormattedAmountInGermanLocale, 'Amount is formatted' );
		t.end();
	} );

	t.test( 'Given integer amount, decimal places are added', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23' ), expectedFormattedAmountInGermanLocale, 'Amount is formatted' );
		t.end();
	} );

	t.test( 'Large amounts have no separators', function ( t ) {
		var formatter = createCurrencyFormatter( locale ),
			formattedAmount = formatter.format( '230000' );
		t.equals( formattedAmount, '230000,00' + String.fromCharCode( 160 ) + '€', 'Amount is formatted' );
		t.end();
	} );

} );

test( 'English locale', function ( t ) {

	var locale = 'en';

	t.test( 'Given amount with decimal comma, it is rendered as-is', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23,00' ), expectedFormattedAmountInEnglishLocale, 'Amount is preserved' );
		t.end();
	} );

	t.test( 'Given amount with missing decimal places, they are added', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23,0' ), expectedFormattedAmountInEnglishLocale, 'Amount is formatted' );
		t.end();
	} );

	t.test( 'Given integer amount, decimal places are added', function ( t ) {
		var formatter = createCurrencyFormatter( locale );
		t.equals( formatter.format( '23' ), expectedFormattedAmountInEnglishLocale, 'Amount is formatted' );
		t.end();
	} );

	t.test( 'Large amounts have no separators', function ( t ) {
		var formatter = createCurrencyFormatter( locale ),
			formattedAmount = formatter.format( '230000' );
		t.equals( formattedAmount, '230000.00' + String.fromCharCode( 160 ) + '€', 'Amount is formatted' );
		t.end();
	} );

} );

test( 'unsupported locales throw exception', function ( t ) {
	t.throws( function () {
		createCurrencyFormatter( 'ru' );
	} );
	t.end();
} );

