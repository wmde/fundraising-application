'use strict';

module.exports = function ( state ) {
	return (
		state.membershipFormContent.membershipType &&
		state.validity.paymentData === true &&
		state.validity.address === true &&
		(
			( state.membershipFormContent.paymentType === 'BEZ' && state.validity.bankData === true ) ||
			( state.membershipFormContent.paymentType !== 'BEZ' && state.validity.bankData !== false )
		)
	);
};
