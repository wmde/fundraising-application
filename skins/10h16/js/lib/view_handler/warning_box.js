'use strict';

var objectAssign = require( 'object-assign' ),

	WarningBox = {
		issueBox: null,
		validationFunction: null,
		update: function ( fieldValue ) {
			if ( this.validationFunction( fieldValue ) ) {
				this.issueBox.show();
			} else {
				this.issueBox.hide();
			}
		}
	},

	/**
	 * @param {jQuery} issueBoxElement Element that should be shown/hidden
	 * @param {function} shouldShowWarningFunction Function that returns true/false in reaction the the value that gets passed in
	 * @returns {WarningBox}
	 */
	createHandler = function ( issueBoxElement, shouldShowWarningFunction ) {
		return objectAssign(
			Object.create( WarningBox ),
			{
				issueBox: issueBoxElement,
				validationFunction: shouldShowWarningFunction
			}
		);
	};

module.exports = {
	createHandler: createHandler
};
