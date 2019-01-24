'use strict';

var Validity = require( '../../validation/validation_states' ).Validity;

module.exports = function ( state ) {
	return (
		state.validity.paymentData === Validity.VALID &&
		state.donationInputValidation.paymentType.isValid === Validity.VALID &&
		state.validity.address === Validity.VALID &&
		(
			state.donationFormContent.addressType === 'anonym' ||
			state.donationInputValidation.email.isValid === Validity.VALID
		) &&
		(
			( state.donationFormContent.paymentType === 'BEZ' && state.validity.bankData === Validity.VALID ) ||
			( state.donationFormContent.paymentType !== 'BEZ' )
		)
	);
};
