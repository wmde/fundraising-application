'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult ),
		respectiveValidators = _.pick( state.membershipInputValidation, [ 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = state.membershipFormContent.paymentType !== '' || _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if ( _.contains( _.pluck( respectiveValidators, 'isValid' ), false ) || state.validity.bankData === false ) {
		result.isValid = false;
	} else if ( state.membershipFormContent.paymentType === '' ) {
		result.isValid = null;
	} else {
		result.isValid = true;
	}

	return result;
};
