'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	feePerMonth = function ( intervalInMonths, fee ) {
		return ( 12 / intervalInMonths ) * fee;
	},

	/**
	 * View Handler for enabling and disabling elements if the update value exceeds a certain threshold
	 * @class
	 */
	FeeOptionSwitcher = {
		isDisabled: null, // Null to always trigger show/hide on first call to update
		minimumFee: {},
		elements: [],

		update: function ( state ) {
			_.each( this.elements, function ( feeOption ) {
				// TODO no need to parseFloat when fee is converted to integer
				var shouldBeDisabled = this.minimumFee[ state.addressType ] > feePerMonth( state.paymentIntervalInMonths, parseFloat( feeOption.val() ) ) ;

				if ( shouldBeDisabled ) {
					feeOption.prop( 'checked', false );
					feeOption.prop( 'disabled', true );
				} else {
					feeOption.prop( 'disabled', false );
				}
			}, this );
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
