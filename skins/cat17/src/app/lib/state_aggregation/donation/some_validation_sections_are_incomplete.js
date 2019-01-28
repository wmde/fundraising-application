'use strict';

var Validity = require( '../../validation/validation_states' ).Validity;

module.exports = function ( state ) {
	return (
		state.validity.paymentData === Validity.INCOMPLETE ||
		state.donationInputValidation.paymentType.isValid === Validity.INCOMPLETE ||
		state.donationInputValidation.email.isValid === Validity.INCOMPLETE ||
		state.validity.address === Validity.INCOMPLETE ||
		(
			( state.donationFormContent.paymentType === 'BEZ' && state.validity.bankData === Validity.INCOMPLETE )
		)
	);
};
