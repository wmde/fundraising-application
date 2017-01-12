'use strict';

/**
 *
 * @module redux_validation
 */

var _ = require( 'underscore' ),

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
			return store.dispatch( this.actionCreationFunction( validationResult ) );
		}
	};

module.exports = ValidationDispatcher;
