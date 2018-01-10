'use strict';

var objectAssign = require( 'object-assign' ),
	Base = require( '../base' )
;

module.exports = objectAssign( Object.create( Base ), {
	countryNames: null,
	update: function ( addressType, salutation, title, firstName, lastName, companyName, street, postcode, city, country, email, aggregateValidity ) {
		this.setSectionStatusFromValidity( aggregateValidity );

		this.setIcon( this.getValueIcon( addressType, aggregateValidity ) );
		this.setText( this.getValueText( addressType, aggregateValidity ) );
		this.setLongText(
			this.getValueLongText( addressType, salutation, title, firstName, lastName, companyName, street, postcode, city, country, email ),
			{ updateMethod: 'html' }
		);
	},
	getValueText: function ( addressType, aggregateValidity ) {
		if ( !aggregateValidity.dataEntered ) {
			return this.getFallbackText();
		}

		return Base.getValueText.call( this, addressType );
	},
	getValueIcon: function ( addressType, aggregateValidity ) {
		if ( !aggregateValidity.dataEntered ) {
			return this.getFallbackIcon();
		}

		return Base.getValueIcon.call( this, addressType );
	},
	getValueLongText: function ( addressType, salutation, title, firstName, lastName, companyName, street, postcode, city, country, email ) {
		var wrapperTag = '<span>',
			longText = ''
		;

		if ( addressType !== 'person' && addressType !== 'firma' ) {
			return longText;
		}

		longText = jQuery( wrapperTag );

		if ( addressType === 'person' && firstName !== '' && lastName !== '' ) {
			longText.append( jQuery( wrapperTag ).text( salutation + ' ' + title + ' ' + firstName + ' ' + lastName ), '<br>' );
		} else if ( addressType === 'firma' && companyName !== '' ) {
			longText.append( jQuery( wrapperTag ).text( companyName ), '<br>' );
		}

		if ( street !== '' ) {
			longText.append( jQuery( wrapperTag ).text( street ), '<br>' );
		}
		if ( postcode !== '' && city !== '' ) {
			longText.append( jQuery( wrapperTag ).text( postcode + ' ' + city ), '<br>' );
		}
		if ( country !== '' ) {
			longText.append( jQuery( wrapperTag ).text( this.countryNames[ country ] ), '<br>' );
		}
		if ( email !== '' ) {
			longText.append( jQuery( wrapperTag ).text( email ), '<br>' );
		}

		return longText;
	}
} );
