'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	WarningBox = {
		issueBox: null,
		validationFunction: null,
		update: function ( fieldValue ) {
			if ( this.validationFunction( fieldValue ) ) {
				this.issueBox.show();
			}
			else {
				this.issueBox.hide();
			}
		}
	},

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
