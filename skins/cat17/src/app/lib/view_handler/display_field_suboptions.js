'use strict';

/**
 * A - very opinionated - observer that, given a state value and a fieldset, ensures that the current state is
 * correctly reflected in the fieldset view (e.g. showing sub forms and information regarding a selected value).
 */
var objectAssign = require( 'object-assign' ),
	SuboptionDisplayHandler = {
		fieldset: null,
		update: function ( value ) {
			this.fieldset.find( '.wrap-field' ).removeClass( 'selected notselected' );
			this.fieldset.find( '.info-text' ).removeClass( 'opened' );

			// fieldsets may contain fields of more than one name (cp. intervalType vs periode), avoid false matches (hidden)
			var field = this.fieldset.find( '.wrap-input [value="' + value + '"]:not(.hidden)' );
			var wrapper = field.parents( '.wrap-field' );
			var infoText = wrapper.find( '.info-text' );

			wrapper.addClass( 'selected' );
			infoText.addClass( 'opened' );
			this.fieldset.css( 'min-height', infoText.prop( 'scrollHeight' ) + 'px' );
		}
	};

module.exports = {
	createSuboptionDisplayHandler: function ( fieldset ) {
		return objectAssign( Object.create( SuboptionDisplayHandler ), {
			fieldset: fieldset
		} );
	}
};
