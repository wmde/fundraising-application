'use strict';

module.exports = function ( state ) {
	return (
		(
			( state.membershipFormContent.addressType === 'person' && state.membershipFormContent.membershipType !== null ) ||
			( state.membershipFormContent.addressType === 'firma' && state.membershipFormContent.membershipType === 'sustaining' )
		) &&
		state.validity.paymentData === true &&
		state.validity.address === true &&
		(
			( state.membershipFormContent.paymentType === 'BEZ' && state.validity.bankData === true ) ||
			( state.membershipFormContent.paymentType !== 'BEZ' )
		)
	);
};
