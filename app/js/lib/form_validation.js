'use strict';

var jQuery = require( 'jquery' ),
	objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),

	AddressValidator = {
		validationUrl: '',
		sendFunction: null,

		requiredFieldsForAll: [ 'street', 'postcode', 'city', 'email' ],
		requiredFieldsForPerson: [ 'firstName', 'lastName', 'salutation' ],
		requiredFieldsForCompany: [ 'company' ],
		validate: function ( formValues ) {
			var requiredFields;
			switch ( formValues.addressType ) {
				case 'person':
					requiredFields = this.requiredFieldsForAll.concat( this.requiredFieldsForPerson );
					break;
				case 'firma':
					requiredFields = this.requiredFieldsForAll.concat( this.requiredFieldsForCompany );
					break;
				default:
					return { status: 'OK' };
			}

			// Return error status if not all required fields have been filled
			if ( this.formValuesHaveEmptyRequiredFields( formValues, requiredFields ) ) {
				return { status: 'ERR' };
			}

			return this.sendFunction( this.validationUrl, formValues, null, 'json' );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues, requiredFields ) {
			var objectWithOnlyTheRequiredFields = _.pick( formValues, requiredFields ),
				isEmptyString = function ( value ) { return value === ''; };
			return _.find( objectWithOnlyTheRequiredFields, isEmptyString ) !== undefined;
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
			var data, validationUrl;
			if ( formValues.paymentType && formValues.paymentType !== 'BEZ' ) {
				return {
					status: 'OK'
				};
			}
			if ( formValues.debitType === 'sepa' ) {
				data = {
					iban: formValues.iban
				};
				validationUrl = this.validationUrlForSepa;
			} else {
				data = {
					accountNumber: formValues.accountNumber,
					bankCode: formValues.bankCode
				};
				validationUrl = this.validationUrlForNonSepa;
			}
			return this.sendFunction( validationUrl, data, null, 'json' );
		}
	},

	SepaConfirmationValidator = {
		validate: function ( formValues ) {
			return formValues.confirmSepa && formValues.confirmShortTerm;
		}
	},

	createAddressValidator = function ( validationUrl, sendFunction ) {
		return objectAssign( Object.create( AddressValidator ), {
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
	createBankDataValidator: createBankDataValidator,
	createSepaConfirmationValidator: function () {
		return Object.create( SepaConfirmationValidator );
	}
};
