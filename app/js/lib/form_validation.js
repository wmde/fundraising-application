'use strict';

var jQuery = require( 'jquery' ),
	actions = require( './actions' ),
	objectAsssign = require( 'object-assign' ),

	AmountValidator = {
		previousAmount: null,
		previousPaymentType: null,
		validationUrl: '',
		postFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( formValues.amount === this.previousAmount && formValues.paymentType === this.previousPaymentType ) {
				return;
			}
			postData = {
				amount: formValues.amount,
				paymentType: formValues.paymentType
			};
			return actions.newValidateAmountAction( this.postFunction( this.validationUrl, postData, null, 'json' ) );
		},
		storePreviousValues: function ( formValues ) {
			this.previousAmount = formValues.amount;
			this.previousPaymentType = formValues.paymentType;
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
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, postFunction ) {
		return objectAsssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
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
			mapper = objectAsssign( Object.create( ValidationMapper ), {
				store: store,
				validationFunctions: validationFunctions
			} );
		store.subscribe( mapper.onUpdate.bind( mapper ) );
		return mapper;

	};

module.exports = {
	createAmountValidator: createAmountValidator,
	createValidationMapper: createValidationMapper
};
