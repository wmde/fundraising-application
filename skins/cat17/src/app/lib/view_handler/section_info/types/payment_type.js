'use strict';

var objectAssign = require( 'object-assign' ),
	Base = require( '../base' )
;

module.exports = objectAssign( Object.create( Base ), {
	update: function ( paymentType, iban, bic, aggregateValidity ) {
		this.setSectionStatusFromValidity( aggregateValidity );

		this.setIcon( this.getValueIcon( paymentType ) );

		if ( this.text ) {
			this.setText(
				!aggregateValidity.dataEntered ?
					this.text.data( Base.DOM_SELECTORS.data.emptyText ) :
					this.getValueText( paymentType )
			);
		}

		if ( paymentType !== 'BEZ' ) {
			this.setLongText( '' );
			return;
		}

		this.setLongText( this.getValueLongText( paymentType ) );

		if ( this.longText && iban && bic ) {
			this.longText.prepend( // intentionally html. Escaping performed through .text() calls on user-input vars
				jQuery( '<dl>' ).addClass( Base.DOM_SELECTORS.classes.summaryBankInfo ).append(
					jQuery( '<dt>' ).text( 'IBAN' ),
					jQuery( '<dd>' ).text( iban ),
					jQuery( '<dt>' ).text( 'BIC' ),
					jQuery( '<dd>' ).text( bic )
				)
			);
		}
	}
} );
