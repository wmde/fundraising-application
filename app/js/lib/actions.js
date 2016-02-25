'use strict';

module.exports = {
	newAddPageAction: function ( pageName ) {
		return {
			type: 'ADD_PAGE',
			payload: { name: pageName }
		};
	},

	newNextPageAction: function () {
		return {
			type: 'NEXT_PAGE'
		};
	},

	newStoreValidationResultAction: function ( isValid ) {
		return {
			type: 'VALIDATION_RESULT',
			payload: { isValid: isValid }
		};
	}
};
