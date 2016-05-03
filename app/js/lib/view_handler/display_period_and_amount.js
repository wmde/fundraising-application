'use strict';

var objectAssign = require( 'object-assign' ),
	PeriodAndAmountDisplayHandler = {
		periodElement: null,
		amountElement: null,
		periodTranslations: null,
		numberFormatter: null,
		update: function ( formContent ) {
			this.periodElement.text( this.formatPaymentPeriod( formContent.paymentPeriodInMonths ) );
			this.amountElement.text( this.numberFormatter.format( formContent.amount ) );
		},
		formatPaymentPeriod: function( paymentPeriodInMonths ) {
			return this.periodTranslations[ paymentPeriodInMonths ];
		}
	};

module.exports = {
	createPaymentPeriodAndAmountDisplayHandler: function ( periodElement, amountElement, periodTranslations, numberFormatter ) {
		return objectAssign( Object.create( PeriodAndAmountDisplayHandler ), {
			periodElement: periodElement,
			amountElement: amountElement,
			periodTranslations: periodTranslations,
			numberFormatter: numberFormatter
		} );
	}
};
