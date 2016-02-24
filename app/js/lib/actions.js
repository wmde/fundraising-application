'use strict';

function newAddPageAction( pageName ) {
	return {
		type: 'ADD_PAGE',
		payload: { name: pageName }
	};
}

function newNextPageAction() {
	return {
		type: 'NEXT_PAGE'
	};
}

function newStoreValidationResultAction( isValid ) {
	return {
		type: 'VALIDATION_RESULT',
		payload: { isValid: isValid }
	};
}

module.exports = {
	newAddPageAction: newAddPageAction,
	newNextPageAction: newNextPageAction,
	newStoreValidationResultAction: newStoreValidationResultAction
};
