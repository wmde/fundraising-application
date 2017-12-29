'use strict';

module.exports = function ( state ) {
	return {
		isValid: state.membershipFormContent.membershipType !== null,
		dataEntered: state.membershipFormContent.membershipType !== null
	};
};
