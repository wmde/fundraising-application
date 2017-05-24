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
	 * If the validation result is not null, it is sent to the store via the `finishActionCreationFunction`.
	 *
	 * @class ValidationDispatcher
	 */
	ValidationDispatcher = {
		validationFunction: null,
		finishActionCreationFunction: null,
		beginActionCreationFunction: null,
		fields: null,
		fieldValuesIfNotSet: null,
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

			if ( !this.fieldsRequiredForDispatchAreSet( selectedValues ) ) {
				return;
			}

			this.previousFieldValues = selectedValues;

			if ( this.beginActionCreationFunction ) {
				store.dispatch( this.beginActionCreationFunction( selectedValues ) );
			}
			validationResult = this.validationFunction( selectedValues );
			return store.dispatch( this.finishActionCreationFunction( validationResult ) );
		},

		fieldsRequiredForDispatchAreSet: function ( formValues ) {
			var allFieldsSet = true;

			if ( _.isObject( formValues ) && this.fieldValuesIfNotSet !== null ) {
				_.each( this.fieldValuesIfNotSet, function ( initalValue, fieldName ) {
					if ( initalValue === formValues[ fieldName ] ) {
						allFieldsSet = false;
					}
				} );
			}

			return allFieldsSet;
		}
	};

module.exports = ValidationDispatcher;
