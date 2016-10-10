'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for showing and hiding elements if the update value matches a regular expression
	 * @class
	 */
	PPLRecurrentWarning = {
		isHidden: null,
		animator: null,

		update: function ( formContent ) {

			if ( formContent.paymentType === 'PPL' && formContent.paymentIntervalInMonths > 0 ) {
				if ( this.isHidden === false ) {
					return;
				}
				this.animator.showElement();
				this.isHidden = false;
			} else {
				if ( this.isHidden === true ) {
					return;
				}
				this.animator.hideElement();
				this.isHidden = true;
			}
		}
	};

module.exports = {
	createPPLRecurrentWarningHandler: function ( animator ) {
		return objectAssign( Object.create( PPLRecurrentWarning ), {
			animator: animator
		} );
	}
};
