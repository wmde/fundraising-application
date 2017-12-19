'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for displaying a field value validity indicator
	 * @class
	 */
	FieldValueValidityIndicator = {
		element: {},

		update: function ( validationState ) {
			if ( validationState.isValid === true ) {
				this.element.addClass( 'valid' ).removeClass( 'invalid' );
				this.element.parent().addClass( 'valid' ).removeClass( 'invalid' );
			} else if( validationState.isValid === false ) {
				this.element.addClass( 'invalid' ).removeClass( 'valid' );
				this.element.parent().addClass( 'invalid' ).removeClass( 'valid' );
			} else if( validationState.isValid === null ) {
				this.element.removeClass( 'valid invalid' );
				this.element.parent().removeClass( 'invalid valid' );
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
