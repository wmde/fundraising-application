'use strict';

var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

module.exports = function ( state ) {
	var result = _.clone( validationResult ),
		respectiveValidators = _.pick( state.donationInputValidation, [ 'paymentType', 'iban', 'bic', 'accountNumber', 'bankCode' ] )
	;

	result.dataEntered = _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

	if ( respectiveValidators.paymentType.isValid && state.donationFormContent.paymentType !== 'BEZ' ) {
		result.isValid = true;
	} else if ( _.contains( _.pluck( respectiveValidators, 'isValid' ), false ) || state.validity.bankData === false ) {
		result.isValid = false;
	} else if ( state.donationInputValidation.paymentType.isValid === null ) {
		result.isValid = null;
	} else {
		result.isValid = true;
	}

	return result;
};
