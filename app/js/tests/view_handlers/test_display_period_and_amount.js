'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createPaymentPeriodAndAmountDisplayHandler = require( '../../lib/view_handler/display_period_and_amount' ).createPaymentPeriodAndAmountDisplayHandler,
	createElement = function () {
		return {
			text: sinon.spy()
		};
	},
	paymentPeriodTranslations = {
		'0': 'einmalig',
		'1': 'monatlich',
		'3': 'quartalsweise',
		'6': 'halbjährlich',
		'12': 'jährlich'
	},
	formattedAmount = '23,00 EUR',
	currencyFormatter = {
		format: sinon.stub().returns( formattedAmount )
	}
	;

test( 'The amount is passed to the curreny formatter', function ( t ) {
	var amountElement = createElement(),
		periodElement = createElement(),
		handler = createPaymentPeriodAndAmountDisplayHandler( periodElement, amountElement, paymentPeriodTranslations, currencyFormatter );
	handler.update( {
		amount: '23,00',
		period: '0'
	} );
	t.ok( currencyFormatter.format.calledOnce, 'format is called' );
	t.equals( currencyFormatter.format.firstCall.args[ 0 ], '23,00', 'Amount is passed to formatter' );
	t.end();
} );

test( 'Formatted amount is set in amount element', function ( t ) {
	var amountElement = createElement(),
		periodElement = createElement(),
		handler = createPaymentPeriodAndAmountDisplayHandler( periodElement, amountElement, paymentPeriodTranslations, currencyFormatter );
	handler.update( {
		amount: '23,0',
		period: '0'
	} );
	t.ok( amountElement.text.calledOnce, 'Amount is set' );
	t.equals( amountElement.text.firstCall.args[ 0 ], formattedAmount, 'amount is set' );
	t.end();
} );
