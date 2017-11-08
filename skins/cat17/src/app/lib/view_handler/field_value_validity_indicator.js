'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for displaying a field value validity indicator
	 * @class
	 */
	FieldValueValidityIndicator = {
		element: {},

		update: function ( validationState ) {
			// @fixme look why the 2nd condition was added and fix it differently.
			// @fixme Element values should come from the store, not from the elements themselves
			if ( validationState.isValid === true && this.element.val() !== "" ) {
				this.element.addClass( 'valid' ).removeClass( 'invalid' )
					.next( 'span' ).addClass( 'icon-ok' ).removeClass( 'icon-bug icon-placeholder' );
        this.element.parent().addClass('valid').removeClass('invalid');
			} else if ( validationState.isValid === false ) {
				this.element.addClass( 'invalid' ).removeClass( 'valid' )
					.next( 'span' ).addClass( 'icon-bug' ).removeClass( 'icon-ok icon-placeholder' );
        this.element.parent().addClass('invalid').removeClass('valid');
			} else if ( validationState.isValid === null ) {
				this.element.removeClass( 'valid invalid' )
					.next( 'span' ).addClass( 'icon-placeholder' ).removeClass( 'icon-ok icon-bug' );
        this.element.parent().removeClass('invalid valid');
			}
		}
	};

module.exports = {
	/**
	 * @param {jQuery} element
	 * @return {FieldValueValidityIndicator}
	 */
	createFieldValueValidityIndicator: function ( element ) {
		return objectAssign( Object.create( FieldValueValidityIndicator ), { element: element } );
	},

	FieldValueValidityIndicator: FieldValueValidityIndicator
};
