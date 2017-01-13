'use strict';

/**
 *
 * @module redux_validation
 */

var objectAssign = require( 'object-assign' ),
	ValidationDispatcher = require( './base' ),
	Actions = require( '../actions' ),
	_ = require( 'underscore' ),

	/**
	 *
	 * @param {EmailAddressValidator} validator
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
	createEmailValidationDispatcher = function ( validator,  initialValues ) {
		var fieldNames = [ 'email' ];

		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator.validate.bind( validator ),
			actionCreationFunction: Actions.newFinishEmailAddressValidationAction,
			finishActionCreationFunction: Actions.newFinishEmailAddressValidationAction,
			beginActionCreationFunction: Actions.newBeginEmailAddressValidationAction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = createEmailValidationDispatcher;

