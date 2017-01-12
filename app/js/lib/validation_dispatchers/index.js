
// TODO Remove this function when a dispatcher factory function exists for all validators
var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	ValidationDispatcher = require( './base' ),
	createValidationDispatcher = function ( validator, actionCreationFunction, fieldNames, initialValues ) {
		if ( typeof validator === 'object' ) {
			validator = validator.validate.bind( validator );
		}
		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator,
			actionCreationFunction: actionCreationFunction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = {
	createAmountValidationDispatcher: require( './amount' ),
	createValidationDispatcher: createValidationDispatcher
};
