var _ = require( 'underscore' ),
	validationResult = require( './../validation_result' )
;

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
		if ( _.contains( validity, false ) ) {
			result.isValid = false;
		} else if ( _.contains( validity, null ) ) {
			result.isValid = null;
		} else {
			result.isValid = true;
		}
	}

	return result;
};
