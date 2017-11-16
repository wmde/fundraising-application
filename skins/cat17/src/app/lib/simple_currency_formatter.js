'use strict';

/**
 * This number formatter class is a primitive subset of the intl library ( https://www.npmjs.com/package/intl ),
 * which is not available in all browsers and node versions.
 */
var parseGermanFloat = function ( amountStr ) {
		return parseFloat( String( amountStr ).replace( ',', '.' ) );
	},

	GermanCurrencyFormatter = {
		format: function ( amountStr ) {
			var amount = this.parse( amountStr );
      // @fixme This is probably a workaround for amounts < 1. Check what this code does & how it interacts with AmountValidator.formValuesHaveEmptyRequiredFields
      if (amount % 1 != 0) {
        return String( (Math.round(amount * 100)/100).toFixed(2) ).replace( '.', '.' ) + String.fromCharCode( 160 ) + '€';
      }
			return String( Math.round(amount * 100) / 100 ).replace( '.', '.' ) + String.fromCharCode( 160 ) + '€';
		},
		parse: parseGermanFloat
	},

	EnglishCurrencyFormatter = {
		format: function ( amountStr ) {
			var amount = this.parse( amountStr );
			return '€' + String( amount.toFixed( 0 ) );
		},
		parse: parseGermanFloat // just to be sure.
	};

module.exports = {
	createCurrencyFormatter: function ( locale ) {
		switch ( locale ) {
			case 'de':
				return Object.create( GermanCurrencyFormatter );
			case 'en':
				return Object.create( EnglishCurrencyFormatter );
			default:
				throw new Error( 'Unsupported locale: ' + locale );
		}
	}
};
