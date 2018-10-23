var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' ),
	Validity = require( '../../validation/validation_states' ).Validity;

module.exports = function ( state ) {
	var result = validationResult.newUndefinedResult(),
		fieldSets = {
			person: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
			firma: [ 'companyName', 'street', 'postcode', 'city', 'email' ]
		},
		respectiveValidators,
		validity
	;

	if ( state.membershipFormContent.addressType === 'person' || state.membershipFormContent.addressType === 'firma' ) {
		respectiveValidators = _.pick( state.membershipInputValidation, fieldSets[ state.membershipFormContent.addressType ] );

		result.dataEntered = _.contains( _.pluck( respectiveValidators, 'dataEntered' ), true );

		validity = _.pluck( respectiveValidators, 'isValid' );
		if ( _.contains( validity, Validity.INVALID ) ) {
			result.isValid = Validity.INVALID;
		} else if ( _.contains( validity, Validity.INCOMPLETE ) ) {
			result.isValid = Validity.INCOMPLETE;
		} else {
			result.isValid = Validity.VALID;
		}
	}

	return result;
};
