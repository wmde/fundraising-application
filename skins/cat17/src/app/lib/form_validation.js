'use strict';

var jQuery = require( 'jquery' ),
	objectAssign = require( 'object-assign' ),
	Promise = require( 'promise' ),
	_ = require( 'underscore' ),

	ValidationStates = {
		OK: 'OK',
		ERR: 'ERR',
		INCOMPLETE: 'INCOMPLETE',
		NOT_APPLICABLE: 'NOT_APPLICABLE'
	},

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
	 * @param jQueryDeferredObject
	 * @returns {Promise}
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
		sendFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( this.formValuesHaveEmptyRequiredFields( formValues ) ) {
				return { status: ValidationStates.INCOMPLETE };
			}
			postData = {
				amount: formValues.amount.replace( '.', ',' ),
				paymentType: formValues.paymentType
			};
			return jQueryDeferredToPromise( this.sendFunction( this.validationUrl, postData, null, 'json' ) );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues ) {
			// WARNING: As we don't have localized money values at the moment,
			// this method will behave incorrectly for amounts between 0 and 1
			var amountAsFloat = parseFloat( formValues.amount );
			return amountAsFloat === 0 || isNaN( amountAsFloat ) || !formValues.paymentType;
		}
	},

	FeeValidator = {
		validationUrl: '',
		sendFunction: null,
		validate: function ( formValues ) {
			var postData;
			if ( this.formValuesHaveEmptyRequiredFields( formValues ) ) {
				return { status: ValidationStates.INCOMPLETE };
			}
			postData = {
				amount: formValues.amount,
				paymentIntervalInMonths: formValues.paymentIntervalInMonths,
				addressType: formValues.addressType
			};
			return jQueryDeferredToPromise( this.sendFunction( this.validationUrl, postData, null, 'json' ) );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues ) {
			// WARNING: As we don't have localized money values at the moment,
			// this method will behave incorrectly for amounts between 0 and 1
			var amountAsFloat = parseFloat( formValues.amount );
			return amountAsFloat === 0 || isNaN( amountAsFloat ) || !formValues.addressType || !formValues.paymentIntervalInMonths;
		}
	},

	BankDataValidator = {
		validationUrlForSepa: '',
		validationUrlForNonSepa: '',
		sendFunction: null,
		validate: function ( formValues ) {
			if ( formValues.paymentType && formValues.paymentType !== 'BEZ' ) {
				return {
					status: ValidationStates.NOT_APPLICABLE
				};
			}

			if ( formValues.debitType === 'sepa' ) {
				return this.validateSepa( formValues );
			}

			return this.validateNonSepa( formValues );
		},
		/**
		 * @private
		 */
		validateSepa: function ( formValues ) {
			if ( formValues.iban === '' ) {
				return { status: ValidationStates.INCOMPLETE };
			}

			return this.getValidationResultFromApi(
				this.validationUrlForSepa,
				{
					iban: formValues.iban
				}
			);
		},
		/**
		 * @private
		 */
		validateNonSepa: function ( formValues ) {
			if ( formValues.accountNumber === '' || formValues.bankCode === '' ) {
				return { status: ValidationStates.INCOMPLETE };
			}

			return this.getValidationResultFromApi(
				this.validationUrlForNonSepa,
				{
					accountNumber: formValues.accountNumber,
					bankCode: formValues.bankCode
				}
			);
		},
		/**
		 * @private
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

	SepaConfirmationValidator = {
		validate: function ( formValues ) {
			return formValues.confirmSepa && formValues.confirmShortTerm !== false;
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
	 * @param {Function} sendFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, sendFunction ) {
		return objectAssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			sendFunction: sendFunction || jQuery.post
		} );
	},

	createFeeValidator = function ( validationUrl, sendFunction ) {
		return objectAssign( Object.create( FeeValidator ), {
			validationUrl: validationUrl,
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
	createSepaConfirmationValidator: function () {
		return Object.create( SepaConfirmationValidator );
	},
	DefaultRequiredFieldsForAddressType: DefaultRequiredFieldsForAddressType,
	ValidationStates: ValidationStates
};
