'use strict';

var objectAssign = require( 'object-assign' ),
	PeriodAndAmountDisplayHandler = {
		periodElement: null,
		amountElement: null,
		periodTranslations: null,
		numberFormatter: null,
		update: function ( formContent ) {
			this.periodElement.text( this.formatPaymentPeriod( formContent.paymentPeriodInMonths ) );
			this.amountElement.text( this.formatAmount( formContent.amount ) );
		},
		formatPaymentPeriod: function( paymentPeriodInMonths ) {
			return this.periodTranslations[ paymentPeriodInMonths ];
		},
		formatAmount: function( amount ) {
			var floatAmount = parseFloat( String( amount ).replace( ',', '.') );
			return this.numberFormatter.format( floatAmount );
		}
	};

module.exports = {
	createPaymentPeriodAndAmountDisplayHandler: function ( periodElement, amountElement, periodTranslations, locale ) {
		// TODO load polyfill for IE < 11 and Safari
		return objectAssign( Object.create( PeriodAndAmountDisplayHandler ), {
			periodElement: periodElement,
			amountElement: amountElement,
			periodTranslations: periodTranslations,
			numberFormatter: new Intl.NumberFormat( locale || 'de', { style:'currency', currency:'EUR' } )
		} );
	}
};
