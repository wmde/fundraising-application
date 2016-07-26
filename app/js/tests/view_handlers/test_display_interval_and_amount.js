'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createPaymentIntervalAndAmountDisplayHandler = require( '../../lib/view_handler/display_interval_and_amount' ).createPaymentIntervalAndAmountDisplayHandler,
	createElement = function () {
		return {
			text: sinon.spy()
		};
	},
	paymentIntervalTranslations = {
		0: 'einmalig',
		1: 'monatlich',
		3: 'quartalsweise',
		6: 'halbjährlich',
		12: 'jährlich'
	},
	paymentTypeTranslations = {
		BEZ: 'Lastschrift',
		UEB: 'Überweisung',
		MCP: 'Kreditkarte',
		PPL: 'PayPal'
	},
	formattedAmount = '23,00 EUR',
	currencyFormatter = {
		format: sinon.stub().returns( formattedAmount )
	}
	;

test( 'The amount is passed to the curreny formatter', function ( t ) {
	var amountElement = createElement(),
		intervalElement = createElement(),
		paymentTypeElement = createElement(),
		handler = createPaymentIntervalAndAmountDisplayHandler( intervalElement, amountElement, paymentTypeElement,
			paymentIntervalTranslations, paymentTypeTranslations, currencyFormatter );
	handler.update( {
		amount: '23,00',
		paymentIntervalInMonths: '0'
	} );
	t.ok( currencyFormatter.format.calledOnce, 'format is called' );
	t.equals( currencyFormatter.format.firstCall.args[ 0 ], '23,00', 'Amount is passed to formatter' );
	t.end();
} );

test( 'Formatted amount is set in amount element', function ( t ) {
	var amountElement = createElement(),
		intervalElement = createElement(),
		paymentTypeElement = createElement(),
		handler = createPaymentIntervalAndAmountDisplayHandler( intervalElement, amountElement, paymentTypeElement,
			paymentIntervalTranslations, paymentTypeTranslations, currencyFormatter );
	handler.update( {
		amount: '23,0',
		paymentIntervalInMonths: '0',
		paymentType: 'BEZ'
	} );
	t.ok( amountElement.text.calledOnce, 'Amount is set' );
	t.equals( amountElement.text.firstCall.args[ 0 ], formattedAmount, 'amount is set' );
	t.end();
} );

test( 'Formatted Interval is set in Interval element', function ( t ) {
	var amountElement = createElement(),
		intervalElement = createElement(),
		paymentTypeElement = createElement(),
		handler = createPaymentIntervalAndAmountDisplayHandler( intervalElement, amountElement, paymentTypeElement,
			paymentIntervalTranslations, paymentTypeTranslations, currencyFormatter );
	handler.update( {
		amount: '23,0',
		paymentIntervalInMonths: '0',
		paymentType: 'BEZ'
	} );
	t.ok( intervalElement.text.calledOnce, 'Interval is set' );
	t.equals( intervalElement.text.firstCall.args[ 0 ], 'einmalig', 'amount is set' );

	handler.update( {
		amount: '23,0',
		paymentIntervalInMonths: '3',
		paymentType: 'BEZ'
	} );
	t.ok( intervalElement.text.calledTwice, 'Interval is set' );
	t.equals( intervalElement.text.secondCall.args[ 0 ], 'quartalsweise', 'amount is set' );

	t.end();
} );

test( 'Formatted payment type is set in respective element', function ( t ) {
	var amountElement = createElement(),
		intervalElement = createElement(),
		paymentTypeElement = createElement(),
		handler = createPaymentIntervalAndAmountDisplayHandler( intervalElement, amountElement, paymentTypeElement,
			paymentIntervalTranslations, paymentTypeTranslations, currencyFormatter );
	handler.update( {
		amount: '23,0',
		paymentIntervalInMonths: '0',
		paymentType: 'BEZ'
	} );
	t.ok( paymentTypeElement.text.calledOnce, 'Payment type is set' );
	t.equals( paymentTypeElement.text.firstCall.args[ 0 ], 'Lastschrift', 'payment type is translated' );

	t.end();
} );
