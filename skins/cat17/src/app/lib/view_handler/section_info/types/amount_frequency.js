'use strict';

var objectAssign = require( 'object-assign' ),
	Base = require( '../base' )
;

module.exports = objectAssign( Object.create( Base ), {
	// todo Inject actual currency formatter (that knows how to format it depending on locale and incl currency symbol)
	currencyFormatter: null,
	update: function ( amount, paymentInterval, aggregateValidity ) {
		this.setSectionStatusFromValidity( aggregateValidity );

		this.setIcon( this.getValueIcon( paymentInterval ) );
		this.setText( this.getValueText( amount ) );
		this.setLongText( this.getValueLongText( paymentInterval ) );
	},
	getValueText: function ( amount ) {
		if ( amount === 0 ) {
			return this.getFallbackText();
		}

		return this.currencyFormatter.format( amount ) + ' €';
	}
} );
