
function getfirstTwoDigitsOfNumber( num ) {
	if ( num < 100 ) {
		return num;
	}
	while ( num > 99 ) {
		num /= 10;
	}
	return Math.floor( num );

}

var objectAssign = require( 'object-assign' ),
	CurrencyFormatter = {
		decimalDelimiter: '.',
		/**
		 * @param {Number} value
		 * @return {string}
		 */
		format: function ( value ) {
			var decimals = value % 100;
			if ( decimals < 10 ) {
				decimals = '0' + decimals;
			}
			return Math.floor( value / 100 ) + this.decimalDelimiter + decimals;
		}
	},
	CurrencyParser = {
		decimalDelimiter: '.',
		parse: function ( value ) {
			var parts = value.split( this.decimalDelimiter ).map(
				function ( p ) {
					if ( p.match( /[^-0-9]/ ) ) {
						return Number.NaN;
					}
					return parseInt( p, 10 );
				}
			);
			if ( parts.length < 2 ) {
				parts[1] = 0;
			}

			parts[1] = getfirstTwoDigitsOfNumber( parts[1] );

			if ( isNaN( parts[0] ) || isNaN( parts[1] ) || parts.length > 2 ) {
				throw new Error( 'Invalid number' );
			}

			return parts[0] * 100 + parts[1];
		},
		getDecimalDelimiter: function () {
			return this.decimalDelimiter;
		}
	}
;


module.exports = {
	createCurrencyFormatter: function ( locale ) {
		switch ( locale ) {
			case 'de':
				return objectAssign( Object.create( CurrencyFormatter ), { decimalDelimiter: ',' } );
			case 'en':
				return Object.create( CurrencyFormatter );
			default:
				throw new Error( 'Unsupported locale: ' + locale );
		}
	},
	createCurrencyParser: function ( locale ) {
		switch ( locale ) {
			case 'de':
				return objectAssign( Object.create( CurrencyParser ), { decimalDelimiter: ',' } );
			case 'en':
				return Object.create( CurrencyParser );
			default:
				throw new Error( 'Unsupported locale: ' + locale );
		}
	}
};
