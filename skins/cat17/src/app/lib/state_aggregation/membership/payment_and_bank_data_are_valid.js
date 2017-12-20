'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult ),
		respectiveValidators = _.pick( state.membershipInputValidation, [ 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = state.membershipFormContent.paymentType !== null || _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if (
		state.membershipFormContent.paymentType !== null &&
		(
			state.membershipFormContent.paymentType !== 'BEZ' || state.validity.bankData === true
		)
	) {
		result.isValid = true;
	}
	else if (
		state.membershipFormContent.paymentType === null ||
		(
			state.membershipFormContent.paymentType !== null &&
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
