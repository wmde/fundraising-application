'use strict';

var objectAssign = require( 'object-assign' ),
	ValidationWrapper = {
		validationFunction: null,
		actionCreationFunction: null,
		validate: function ( formValues ) {
			var validationResult = this.validationFunction( formValues );
			if ( typeof validationResult === 'undefined' ) {
				return;
			}
			return this.actionCreationFunction( validationResult );
		}
	},

	ValidationMapper = {
		store: null,
		validationFunctions: [],
		onUpdate: function ( state ) {
			var formContent = state.formContent,
				i, validationAction;
			for ( i = 0; i < this.validationFunctions.length; i++ ) {
				validationAction = this.validationFunctions[ i ]( formContent );
				if ( validationAction && validationAction.type ) {
					this.store.dispatch( validationAction );
				}
			}
		}
	},

	/**
	 *
	 * @param {Function|Object} validator Function or object that has a 'validate' method.
	 * 			The method will be bound to the object. If the function returns undefined,
	 * 			that means 'no need for validating' and no validation action will be generated.
	 * @param {Function} actionCreationFunction
	 * @return {ValidationWrapper}
	 */
	createValidationWrapper = function ( validator, actionCreationFunction ) {
		if ( typeof validator === 'object' ) {
			validator = validator.validate.bind( validator );
		}
		return objectAssign( Object.create( ValidationWrapper ), {
			validationFunction: validator,
			actionCreationFunction: actionCreationFunction
		} );
	},

	/**
	 *
	 * @param {*} store Redux store
	 * @param {Array} validators (objects that have a `validate` method that returns an action object)
	 */
	createValidationMapper = function ( store, validators ) {
		var validationFunctions = validators.map( function ( validator ) {
				return function ( state ) {
					return validator.validate.call( validator, state );
				};
			} ),
			mapper = objectAssign( Object.create( ValidationMapper ), {
				store: store,
				validationFunctions: validationFunctions
			} );
		store.subscribe( mapper.onUpdate.bind( mapper ) );
		return mapper;
	};

module.exports = {
	createValidationWrapper: createValidationWrapper,
	createValidationMapper: createValidationMapper
};
