'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	areIntlLocalesSupported = require('intl-locales-supported'),
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
	locale = 'de',
	expectedFormattedAmount = '23,00' + String.fromCharCode( 160 ) + '€'
	;

if (global.Intl) {
	// Determine if the built-in `Intl` has the locale data we need. 
	if ( !areIntlLocalesSupported( [ locale ] ) ) {
		// `Intl` exists, but it doesn't have the data we need, so load the 
		// polyfill and patch the constructors we need with the polyfill's. 
		var IntlPolyfill    = require( 'intl' );
		Intl.NumberFormat   = IntlPolyfill.NumberFormat;
		Intl.DateTimeFormat = IntlPolyfill.DateTimeFormat;
	}
} else {
	// No `Intl`, so use and load the polyfill. 
	global.Intl = require( 'intl' );
}

test( 'Given amount with decimal comma, it is rendered as-is', function ( t ) {
	var amountElement = createElement(),
		periodElement = createElement(),
		handler = createPaymentPeriodAndAmountDisplayHandler( periodElement, amountElement, paymentPeriodTranslations, locale );
	handler.update( {
		amount: '23,00',
		period: '0'
	} );
	t.ok( amountElement.text.calledOnce, 'Amount is set' );
	t.equals( amountElement.text.firstCall.args[ 0 ], expectedFormattedAmount, 'Amount is preserved' );
	t.end();
} );

test( 'Given amount with missing decimal places, they are added', function ( t ) {
	var amountElement = createElement(),
		periodElement = createElement(),
		handler = createPaymentPeriodAndAmountDisplayHandler( periodElement, amountElement, paymentPeriodTranslations );
	handler.update( {
		amount: '23,0',
		period: '0'
	} );
	t.ok( amountElement.text.calledOnce, 'Amount is set' );
	t.equals( amountElement.text.firstCall.args[ 0 ], expectedFormattedAmount, 'decimal places are added' );
	t.end();
} );
