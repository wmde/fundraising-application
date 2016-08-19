'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	/**
	 * View Handler for enabling and disabling elements if the update value exceeds a certain threshold
	 * @class
	 */
	FeeOptionSwitcher = {
		isDisabled: null, // Null to always trigger show/hide on first call to update
		minimumFee: {},
		elements: [],

		update: function ( state ) {
			var self = this;
			_.each( this.elements, function ( feeOption ) {
				var shouldBeDisabled = self.minimumFee[ state.addressType ] > 12 / state.paymentIntervalInMonths * parseFloat( feeOption.val() );

				if ( shouldBeDisabled ) {
					feeOption.prop( 'checked', false );
					feeOption.prop( 'disabled', true );
				} else {
					feeOption.prop( 'disabled', false );
				}
			} );
		}
	};

module.exports = {
	createFeeOptionSwitcher: function ( elements, minimumFee ) {
		return objectAssign( Object.create( FeeOptionSwitcher ),
			{
				elements: elements,
				minimumFee: minimumFee
			} );
	},

	FeeOptionSwitcher: FeeOptionSwitcher
};
