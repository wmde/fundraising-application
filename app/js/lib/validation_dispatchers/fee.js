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
	 * @param {FeeValidator} validator
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
	createFeeValidationDispatcher = function ( validator,  initialValues ) {
		var fieldNames = [ 'amount', 'paymentIntervalInMonths', 'addressType' ];

		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator.validate.bind( validator ),
			actionCreationFunction: Actions.newFinishPaymentDataValidationAction,
			finishActionCreationFunction: Actions.newFinishPaymentDataValidationAction,
			beginActionCreationFunction: Actions.newBeginPaymentDataValidationAction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = createFeeValidationDispatcher;

