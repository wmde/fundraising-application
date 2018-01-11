'use strict';

var objectAssign = require( 'object-assign' ),
	Base = require( '../base' )
;

module.exports = objectAssign( Object.create( Base ), {
	update: function ( paymentType, iban, bic, aggregateValidity ) {
		this.setSectionStatusFromValidity( aggregateValidity );

		this.setIcon( this.getValueIcon( paymentType ) );
		this.setText( this.getValueText( paymentType, aggregateValidity ) );
		this.setLongText(
			this.getValueLongText( paymentType, iban, bic ),
			{ updateMethod: 'html' }
		);
	},
	getValueText: function ( paymentType, aggregateValidity ) {
		if ( !aggregateValidity.dataEntered ) {
			return this.getFallbackText();
		}

		return Base.getValueText.call( this, paymentType );
	},
	getValueLongText: function ( paymentType, iban, bic ) {
		var wrapperTag = '<div>',
			longText = ''
		;

		if ( paymentType !== 'BEZ' ) {
			return longText;
		}

		longText = jQuery( wrapperTag );

		if ( iban !== '' && bic !== '' ) {
			longText.append(
				jQuery( '<dl>' )
					.addClass( Base.DOM_SELECTORS.classes.summaryBankInfo )
					.append(
						jQuery( '<dt>' ).text( 'IBAN' ),
						jQuery( '<dd>' ).text( iban ),
						jQuery( '<dt>' ).text( 'BIC' ),
						jQuery( '<dd>' ).text( bic )
					)
			);
		}

		longText.append(
			jQuery( wrapperTag ).text( Base.getValueLongText.call( this, paymentType ) )
		);

		return longText;
	}
} );
