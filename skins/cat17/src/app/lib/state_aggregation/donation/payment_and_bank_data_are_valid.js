'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult ),
		respectiveValidators = _.pick( state.donationInputValidation, [ 'paymentType', 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if (
		state.donationInputValidation.paymentType.isValid === true &&
		(
			state.donationFormContent.paymentType !== 'BEZ' || state.validity.bankData === true
		)
	) {
		result.isValid = true;
	}
	else if (
		state.donationInputValidation.paymentType.isValid === null ||
		(
			state.donationInputValidation.paymentType.isValid === true &&
			!_.contains( _.pluck( respectiveValidators, 'isValid' ), false )
		)
	) {
		result.isValid = null;
	}
	else {
		result.isValid = false;
	}

	return result;
};
