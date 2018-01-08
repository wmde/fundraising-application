'use strict';

var objectAssign = require( 'object-assign' ),
	Base = require( '../base' )
;

module.exports = objectAssign( Object.create( Base ), {
	countryNames: null,
	update: function ( addressType, salutation, title, firstName, lastName, companyName, street, postcode, city, country, email, aggregateValidity ) {
		var wrapperTag = '<span>',
			newLongText
		;

		this.setSectionStatusFromValidity( aggregateValidity );

		if ( aggregateValidity.dataEntered ) {
			this.setIcon( this.getValueIcon( addressType ) );
		} else {
			this.setIcon( undefined );
		}

		if ( this.text ) {
			this.setText(
				!aggregateValidity.dataEntered ?
					this.text.data( Base.DOM_SELECTORS.data.emptyText ) :
					this.getValueText( addressType )
			);
		}

		if ( !this.longText ) {
			return;
		}

		newLongText = jQuery( wrapperTag );
		if ( addressType === 'person' || addressType === 'firma' ) {
			if ( addressType === 'person' && firstName !== '' && lastName !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( salutation + ' ' + title + ' ' + firstName + ' ' + lastName ), '<br>' );
			} else if ( addressType === 'firma' && companyName !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( companyName ), '<br>' );
			}

			if ( street !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( street ), '<br>' );
			}
			if ( postcode !== '' && city !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( postcode + ' ' + city ), '<br>' );
			}
			if ( country !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( this.countryNames[ country ] ), '<br>' );
			}
			if ( email !== '' ) {
				newLongText.append( jQuery( wrapperTag ).text( email ), '<br>' );
			}
		}

		this.longText.html( newLongText );
		// we worked around setLongText so have to clean up manually
		this.setLongTextIndication( true );
	}
} );
