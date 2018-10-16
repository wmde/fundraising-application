'use strict';

var objectAssign = require( 'object-assign' ),
	Promise = require( 'promise' ),
	_ = require( 'underscore' ),
	ValidationStates = require( './validation_states' ).ValidationStates,
	JQueryTransport = require( './jquery_transport' ).default,

	isEmptyString = function ( value ) {
		return value === '';
	},

	DefaultRequiredFieldsForAddressType = {
		person: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
		firma: [ 'companyName', 'street', 'postcode', 'city', 'email' ],
		anonym: []
	},

	/**
	 * This function avoids an endless loop on failure.
	 * The jQuery fail function returns the xhrObject, which is itself a Promise,
	 * which will then be called by the Redux promise_middleware again and again.
	 *
	 * When IE support is finally ditched and we use jQuery >= 3.0, this wrapper method must be removed.
	 *
	 * @param {jQueryDeferredObject} jQueryDeferredObject
	 * @return {Promise}
	 */
	jQueryDeferredToPromise = function ( jQueryDeferredObject ) {
		return new Promise( function ( resolve, reject ) {
			jQueryDeferredObject.then( resolve, function ( xhrObject, statusCode, statusMessage ) {
				reject( statusMessage );
			} );
		} );
	},

	AddressValidator = {
		validationUrl: '',
		sendFunction: null,
		requiredFields: {},
		validate: function ( formValues ) {
			var requiredFields = this.getRequiredFieldsForAddressType( formValues.addressType );
			if ( this.formValuesHaveEmptyRequiredFields( formValues, requiredFields ) ) {
				return { status: ValidationStates.INCOMPLETE };
			}
			// Don't send anything to server if there are no fields to validate
			if ( requiredFields.length === 0 ) {
				return { status: ValidationStates.OK };
			}

			return jQueryDeferredToPromise( this.sendFunction( this.validationUrl, formValues, null, 'json' ) );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues, requiredFields ) {
			var objectWithOnlyTheRequiredFields = _.pick( formValues, requiredFields );
			return _.find( objectWithOnlyTheRequiredFields, isEmptyString ) !== undefined;
		},
		getRequiredFieldsForAddressType: function ( addressType ) {
			if ( !_.has( this.requiredFields, addressType ) ) {
				throw new Error( 'Invalid address type: ' + addressType );
			}
			return this.requiredFields[ addressType ];
		}
	},

	EmailAddressValidator = {
		validationUrl: '',
		sendFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( !formValues.email ) {
				return { status: ValidationStates.INCOMPLETE };
			}
			postData = {
				email: formValues.email
			};
			return jQueryDeferredToPromise( this.sendFunction( this.validationUrl, postData, null, 'json' ) );
		}
	},

	AmountValidator = {
		validationUrl: '',
		transport: null,
		/**
		 * @param {object} formValues
		 * @return {Promise}
		 */
		validate: function ( formValues ) {

			if ( this.formValuesHaveEmptyRequiredFields( formValues ) ) {
				return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
			}
			return this.transport.postData(
				this.validationUrl,
				{
					amount: formValues.amount
				}
			).catch( function ( reason ) {
				return Promise.resolve( { status: ValidationStates.ERR, messages: { transportError: reason } } );
			});
		},
		/**
		 * @param {object} formValues
		 * @return {boolean}
		 * @private
		 */
		formValuesHaveEmptyRequiredFields: function ( formValues ) {
			return formValues.amount === 0;
		}
	},

	FeeValidator = {
		validationUrl: '',
		feeFormatter: null,
		sendFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( this.formValuesHaveEmptyRequiredFields( formValues ) ) {
				return { status: ValidationStates.INCOMPLETE };
			}
			postData = {
				amount: this.feeFormatter.format( formValues.amount ),
				paymentIntervalInMonths: formValues.paymentIntervalInMonths,
				addressType: formValues.addressType
			};
			return jQueryDeferredToPromise( this.sendFunction( this.validationUrl, postData, null, 'json' ) );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues ) {
			return formValues.amount === 0 || !formValues.addressType || !formValues.paymentIntervalInMonths;
		}
	},

	BankDataValidator = {
		validationUrlForSepa: '',
		validationUrlForNonSepa: '',
		sendFunction: null,
		/**
		 * @param {string} iban
		 * @return {Promise}
		 */
		validateIban: function ( iban ) {
			return this.getValidationResultFromApi(
				this.validationUrlForSepa,
				{
					iban
				}
			);
		},
		/**
		 * @param {string} accountNumber
		 * @param {string} bankCode
		 * @return {Promise}
		 */
		validateClassicAccountNumber: function ( accountNumber, bankCode ) {
			return this.getValidationResultFromApi(
				this.validationUrlForNonSepa,
				{
					accountNumber,
					bankCode
				}
			);
		},
		/**
		 * @private
		 * @param {string} apiUrl
		 * @param {Object} urlArguments
		 * @return {Promise}
		 */
		getValidationResultFromApi: function ( apiUrl, urlArguments ) {
			return jQueryDeferredToPromise(
				this.sendFunction(
					apiUrl,
					urlArguments,
					null,
					'json'
				)
			);
		}
	},

	createAddressValidator = function ( validationUrl, requiredFields, sendFunction ) {
		return objectAssign( Object.create( AddressValidator ), {
			validationUrl: validationUrl,
			requiredFields: requiredFields,
			sendFunction: sendFunction || jQuery.post
		} );
	},

	createEmailAddressValidator = function ( validationUrl, sendFunction ) {
		return objectAssign( Object.create( EmailAddressValidator ), {
			validationUrl: validationUrl,
			sendFunction: sendFunction || jQuery.post
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Transport} transport
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, transport ) {
		return objectAssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			transport: transport || new JQueryTransport()
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Object} feeFormatter Formatter object that supports the .format method
	 * @param {Function} sendFunction jQuery.post function or equivalent
	 * @return {*}
	 */
	createFeeValidator = function ( validationUrl, feeFormatter, sendFunction ) {
		return objectAssign( Object.create( FeeValidator ), {
			validationUrl: validationUrl,
			feeFormatter: feeFormatter,
			sendFunction: sendFunction || jQuery.post
		} );
	},

	createBankDataValidator = function ( validationUrlForSepa, validationUrlForNonSepa, sendFunction ) {
		return objectAssign( Object.create( BankDataValidator ), {
			validationUrlForSepa: validationUrlForSepa,
			validationUrlForNonSepa: validationUrlForNonSepa,
			sendFunction: sendFunction || jQuery.get
		} );
	}
;

module.exports = {
	createAmountValidator: createAmountValidator,
	createFeeValidator: createFeeValidator,
	createAddressValidator: createAddressValidator,
	createEmailAddressValidator: createEmailAddressValidator,
	createBankDataValidator: createBankDataValidator,
	DefaultRequiredFieldsForAddressType: DefaultRequiredFieldsForAddressType,
	ValidationStates: ValidationStates
};
