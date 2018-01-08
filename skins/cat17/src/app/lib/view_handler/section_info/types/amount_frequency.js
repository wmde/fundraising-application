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

		if ( this.text ) {
			this.setText(
				amount === 0 ?
					this.text.data( Base.DOM_SELECTORS.data.emptyText ) :
					this.currencyFormatter.format( amount ) + ' €'
			);
		}

		this.setLongText( this.getValueLongText( paymentInterval ) );
	}
} );
