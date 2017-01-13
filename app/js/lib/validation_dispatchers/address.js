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
	 * @param {AddressValidator} validator
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
	createAddressValidationDispatcher = function ( validator,  initialValues ) {
		var fieldNames = [
			'addressType',
			'salutation',
			'title',
			'firstName',
			'lastName',
			'companyName',
			'street',
			'postcode',
			'city',
			'country',
			'email'
		];

		if ( typeof validator === 'object' ) {
			validator = validator.validate.bind( validator );
		}
		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator,
			actionCreationFunction: Actions.newFinishAddressValidationAction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = createAddressValidationDispatcher;

