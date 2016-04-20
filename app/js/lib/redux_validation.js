'use strict';

/**
 *
 * @module redux_validation
 */

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),

	/**
	 * The dispatcher checks the form content for fields given in the `fields` property.
	 * If they have changed (compared to their equivalent in the `previousFieldValues` property),
	 * the `validationFunction` is called with an object with the selected fields.
	 * If the validation result is not null, it is sent to the store via the `actionCreationFunction`.
	 *
	 * @class ValidationDispatcher
	 */
	ValidationDispatcher = {
		validationFunction: null,
		actionCreationFunction: null,
		fields: null,
		previousFieldValues: {},

		/**
		 *
		 * @param {Object} formValues
		 * @param {Store} store
		 * @returns {*} Action object or null
		 */
		dispatchIfChanged: function ( formValues, store ) {
			var selectedValues = _.pick( formValues, this.fields ),
				validationResult;

			if ( _.isEqual( this.previousFieldValues, selectedValues ) ) {
				return;
			}

			this.previousFieldValues = selectedValues;
			validationResult = this.validationFunction( selectedValues );
			if ( validationResult === null ) {
				return;
			}
			return store.dispatch( this.actionCreationFunction( validationResult ) );
		}
	},

	ValidationDispatcherCollection = {
		dispatchers: [],
		store: null,
		onUpdate: function () {
			var formContent = this.store.getState().formContent,
				i;
			for ( i = 0; i < this.dispatchers.length; i++ ) {
				this.dispatchers[ i ].dispatchIfChanged( formContent, this.store );
			}
		}
	},

	/**
	 *
	 * @param {Function|Object} validator Function or object that has a 'validate' method.
	 * 			The method will be bound to the object. The validator will get an object with store values as parameter.
	 * @param {Function} actionCreationFunction Action to dispatch with the validation result.
	 * @param {Array} fieldNames Names of the state values from formContent that will be validated
	 * @param {Object} initialValues Initial form state. Only the keys and values from fieldNames will be used
	 * @return {ValidationDispatcher}
	 */
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
	},

	/**
	 *
	 * @param {Object} store Redux store
	 * @param {ValidationDispatcher[]} dispatchers
	 */
	createValidationDispatcherCollection = function ( store, dispatchers ) {
		var collection = objectAssign( Object.create( ValidationDispatcherCollection ), {
				store: store,
				dispatchers: dispatchers
			} );
		store.subscribe( collection.onUpdate.bind( collection ) );
		return collection;
	};

module.exports = {
	createValidationDispatcher: createValidationDispatcher,
	createValidationDispatcherCollection: createValidationDispatcherCollection
};
