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
	 * @param {BankDataValidator} validator
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
	createBankDataValidationDispatcher = function ( validator, initialValues ) {
		var fieldNames = [ 'iban', 'bic', 'accountNumber', 'bankCode', 'debitType', 'paymentType' ];

		return objectAssign( Object.create( ValidationDispatcher ), {
			validationFunction: validator.validate.bind( validator ),
			finishActionCreationFunction: Actions.newFinishBankDataValidationAction,
			beginActionCreationFunction: Actions.newBeginBankDataValidationAction,
			fields: fieldNames,
			previousFieldValues: _.pick( initialValues || {}, fieldNames )
		} );
	};

module.exports = createBankDataValidationDispatcher;

