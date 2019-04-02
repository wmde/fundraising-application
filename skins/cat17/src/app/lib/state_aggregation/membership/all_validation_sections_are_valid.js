'use strict';

const Validity = require( '../../validation/validation_states' ).Validity;

module.exports = function ( state ) {
	return (
		(
			( state.membershipFormContent.addressType === 'person' &&
				( state.membershipFormContent.membershipType === 'active' || state.membershipFormContent.membershipType === 'sustaining' )
			) ||
			( state.membershipFormContent.addressType === 'firma' && state.membershipFormContent.membershipType === 'sustaining' )
		) &&
		state.validity.paymentData === Validity.VALID &&
		state.membershipInputValidation.email.isValid === Validity.VALID &&
		state.validity.address === Validity.VALID &&
		(
			( state.membershipFormContent.paymentType === 'BEZ' && state.validity.bankData === Validity.VALID ) ||
			( state.membershipFormContent.paymentType !== 'BEZ' )
		)
	);
};
