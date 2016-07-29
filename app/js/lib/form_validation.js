'use strict';

var jQuery = require( 'jquery' ),
	objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	isEmptyString = function ( value ) {
		return value === '';
	},

	DefaultRequiredFieldsForAddressType = {
		person: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
		firma: [ 'companyName', 'street', 'postcode', 'city', 'email' ],
		anonym: []
	},

	AddressValidator = {
		validationUrl: '',
		sendFunction: null,
		requiredFields: {},
		validate: function ( formValues ) {
			var requiredFields = this.getRequiredFieldsForAddressType( formValues.addressType );
			if ( this.formValuesHaveEmptyRequiredFields( formValues, requiredFields ) ) {
				return { status: 'INCOMPLETE' };
			}
			// Don't send anything to server if there are no fields to validate
			if ( requiredFields.length === 0 ) {
				return { status: 'OK' };
			}
			return this.sendFunction( this.validationUrl, formValues, null, 'json' );
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
			var postData = {
				email: formValues.email
			};
			return this.sendFunction( this.validationUrl, postData, null, 'json' );
		}
	},

	AmountValidator = {
		validationUrl: '',
		sendFunction: null,
		validate: function ( formValues ) {
			var postData = {
				amount: formValues.amount,
				paymentType: formValues.paymentType
			};
			return this.sendFunction( this.validationUrl, postData, null, 'json' );
		}
	},

	FeeValidator = {
		validationUrl: '',
		sendFunction: null,
		validate: function ( formValues ) {
			var postData = {
				amount: formValues.amount,
				paymentIntervalInMonths: formValues.paymentIntervalInMonths,
				addressType: formValues.addressType
			};
			return this.sendFunction( this.validationUrl, postData, null, 'json' );
		}
	},

	BankDataValidator = {
		validationUrlForSepa: '',
		validationUrlForNonSepa: '',
		sendFunction: null,
		validate: function ( formValues ) {
			if ( formValues.paymentType && formValues.paymentType !== 'BEZ' ) {
				return {
					status: 'OK'
				};
			}

			if ( formValues.debitType === 'sepa' ) {
				return this.validateSepa( formValues );
			}

			return this.validateNonSepa( formValues );
		},
		validateSepa: function ( formValues ) {
			if ( formValues.iban === '' ) {
				return { status: 'INCOMPLETE' };
			}

			return this.getValidationResultFromApi(
				this.validationUrlForSepa,
				{
					iban: formValues.iban
				}
			);
		},
		validateNonSepa: function ( formValues ) {
			if ( formValues.accountNumber === '' || formValues.bankCode === '' ) {
				return { status: 'INCOMPLETE' };
			}

			return this.getValidationResultFromApi(
				this.validationUrlForNonSepa,
				{
					accountNumber: formValues.accountNumber,
					bankCode: formValues.bankCode
				}
			);
		},
		getValidationResultFromApi: function ( apiUrl, urlArguments ) {
			return this.sendFunction(
				apiUrl,
				urlArguments,
				null,
				'json'
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
	DefaultRequiredFieldsForAddressType: DefaultRequiredFieldsForAddressType
};
