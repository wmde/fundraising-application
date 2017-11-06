'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for displaying a notice when PayPal is used with recurrent payments
	 * @class
	 */
	RecurrentPaypalNotice = {
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
	createRecurrentPaypalNoticeHandler: function ( animator ) {
		return objectAssign( Object.create( RecurrentPaypalNotice ), {
			animator: animator
		} );
	}
};
