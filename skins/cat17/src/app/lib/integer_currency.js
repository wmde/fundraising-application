function getFirstTwoDigitsOfNumberString( num ) {
	switch ( num.length ) {
		case 1:
			return num + '0';
		case 2:
			return num;
		default:
			return num.substr( 0, 2 );
	}
}

function stringToNumber( s ) {
	if ( s.match( /[^-0-9]/ ) ) {
		return Number.NaN;
	}
	return parseInt( s, 10 );
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
		allowDecimals: true,
		parse: function ( value ) {
			var strParts = value.split( this.decimalDelimiter ),
				parts;

			if ( strParts.length < 2 || this.allowDecimals === false ) {
				strParts[ 1 ] = '00';
			} else {
				strParts[ 1 ] = getFirstTwoDigitsOfNumberString( strParts[ 1 ] );
			}

			parts = strParts.map( stringToNumber );

			if ( isNaN( parts[ 0 ] ) || isNaN( parts[ 1 ] ) || parts.length > 2 ) {
				throw new Error( 'Invalid number' );
			}

			return parts[ 0 ] * 100 + parts[ 1 ];
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
				return objectAssign( Object.create( CurrencyFormatter ), {
					decimalDelimiter: ','
				} );
			case 'en':
				return Object.create( CurrencyFormatter );
			default:
				throw new Error( 'Unsupported locale: ' + locale );
		}
	},
	createCurrencyParser: function ( locale, allowDecimals ) {
		switch ( locale ) {
			case 'de':
				return objectAssign( Object.create( CurrencyParser ), {
					decimalDelimiter: ',',
					allowDecimals: typeof allowDecimals === 'boolean' ? allowDecimals : true
				} );
			case 'en':
				return objectAssign( Object.create( CurrencyParser ), {
					allowDecimals: typeof allowDecimals === 'boolean' ? allowDecimals : true
				} );
			default:
				throw new Error( 'Unsupported locale: ' + locale );
		}
	}
};
