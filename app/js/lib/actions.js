'use strict';

function addPage( pageName ) {
	return {
		type: 'ADD_PAGE',
		payload: { name: pageName }
	};
}

function storeValidationResult( isValid ) {
	return {
		type: 'VALIDATION_RESULT',
		payload: { isValid: isValid }
	};
}

function nextPage() {
	return {
		type: 'NEXT_PAGE'
	};
}

module.exports = {
	addPage: addPage,
	nextPage: nextPage,
	storeValidationResult: storeValidationResult
};
