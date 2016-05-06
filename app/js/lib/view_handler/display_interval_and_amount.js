'use strict';

var objectAssign = require( 'object-assign' ),
	IntervalAndAmountDisplayHandler = {
		intervalElement: null,
		amountElement: null,
		intervalTranslations: null,
		numberFormatter: null,
		update: function ( formContent ) {
			this.intervalElement.text( this.formatPaymentInterval( formContent.paymentIntervalInMonths ) );
			this.amountElement.text( this.numberFormatter.format( formContent.amount ) );
		},
		formatPaymentInterval: function ( paymentIntervalInMonths ) {
			return this.intervalTranslations[ paymentIntervalInMonths ];
		}
	};

module.exports = {
	createPaymentIntervalAndAmountDisplayHandler: function ( intervalElement, amountElement, intervalTranslations, numberFormatter ) {
		return objectAssign( Object.create( IntervalAndAmountDisplayHandler ), {
			intervalElement: intervalElement,
			amountElement: amountElement,
			intervalTranslations: intervalTranslations,
			numberFormatter: numberFormatter
		} );
	}
};
