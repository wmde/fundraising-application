'use strict';

var objectAssign = require( 'object-assign' ),
	getCSSValueSelector = function ( value ) {
		var escapedValue = value.replace( /[^-_a-zA-Z0-9]/g, '\\$&' );
		return '[value=' + escapedValue + ']';
	},
	/**
	 * Unselect radio buttons when using custom amount,
	 * clear custom amount field and select radio button, when form values indicate that amount is not custom.
	 */
	ClearAmount = {
		amountSelection: null,
		amountInput: null,
		previousValues: null,
		update: function ( formValues ) {
			if ( this.previousValues === formValues ) {
				return;
			}
			if ( formValues.isCustomAmount ) {
				this.amountSelection.prop( 'checked', false );
			} else {
				this.amountInput.val( '' );
				if ( formValues.amount ) {
					this.amountSelection.filter( getCSSValueSelector( formValues.amount ) ).prop( 'checked', true );
				}
			}
			this.previousValues = formValues;
		}
	},
	createHandler = function ( $amountSelection, $amountInput ) {
		return objectAssign( Object.create( ClearAmount ), {
			amountSelection: $amountSelection,
			amountInput: $amountInput
		} );
	};

module.exports = {
	createHandler: createHandler
};
