'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for enabling and disabling elements if the update value exceeds a certain threshold
	 * @class
	 */
	FeeOptionSwitcher = {
		isDisabled: null, // Null to always trigger show/hide on first call to update
		thresholdValue: 0,

		update: function ( value ) {
			if ( parseInt( value ) > this.thresholdValue ) {
				if ( this.isDisabled === true ) {
					return;
				}
				this.element.prop( 'checked', false );
				this.element.prop( 'disabled', true );
				this.isDisabled = true;
			} else {
				if ( this.isDisabled === false ) {
					return;
				}
				this.element.prop( 'disabled', false );
				this.isDisabled = false;
			}
		}
	};

module.exports = {
	/**
	 * @param {jQuery} element
	 * @param {int} thresholdValue
	 * @return {FeeOptionSwitcher}
	 */
	createFeeOptionSwitcher: function ( element, thresholdValue ) {
		return objectAssign( Object.create( FeeOptionSwitcher ),
			{
				element: element,
				thresholdValue: thresholdValue
			} );
	},

	FeeOptionSwitcher: FeeOptionSwitcher
};
