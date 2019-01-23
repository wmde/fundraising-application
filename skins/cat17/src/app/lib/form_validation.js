'use strict';

var objectAssign = require( 'object-assign' ),
	Promise = require( 'promise' ),
	_ = require( 'underscore' ),
	ValidationStates = require( './validation/validation_states' ).ValidationStates,
	JQueryTransport = require( './jquery_transport' ).default,
	AmountValidator = require( './validation/amount_validator' ).default,
	BankDataValidator = require( './validation/bank_data_validator' ).default,

	isEmptyString = function ( value ) {
		return value === '';
	},

	DefaultRequiredFieldsForAddressType = {
		person: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city' ],
		firma: [ 'companyName', 'street', 'postcode', 'city' ],
		anonym: []
	},
	AddressValidator = {
		validationUrl: '',
		transport: null,
		requiredFields: {},
		validate: function ( formValues ) {
			var requiredFields = this.getRequiredFieldsForAddressType( formValues.addressType );
			if ( this.formValuesHaveEmptyRequiredFields( formValues, requiredFields ) ) {
				return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
			}
			// Don't send anything to server if there are no fields to validate
			if ( requiredFields.length === 0 ) {
				return Promise.resolve( { status: ValidationStates.OK } );
			}

			return this.transport.postData( this.validationUrl, formValues );
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
		transport: null,
		validate: function ( formValues ) {
			if ( !formValues.email ) {
				return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
			}
			return this.transport.postData( this.validationUrl, { email: formValues.email } );
		}
	},

	FeeValidator = {
		validationUrl: '',
		feeFormatter: null,
		transport: null,
		validate: function ( formValues ) {
			var postData;
			if ( this.formValuesHaveEmptyRequiredFields( formValues ) ) {
				return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
			}
			postData = {
				amount: this.feeFormatter.format( formValues.amount ),
				paymentIntervalInMonths: formValues.paymentIntervalInMonths,
				addressType: formValues.addressType
			};
			return this.transport.postData( this.validationUrl, postData );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues ) {
			return formValues.amount === 0 || !formValues.addressType || !formValues.paymentIntervalInMonths;
		}
	},

	createAddressValidator = function ( validationUrl, requiredFields, transport ) {
		return objectAssign( Object.create( AddressValidator ), {
			validationUrl: validationUrl,
			requiredFields: requiredFields,
			transport: transport || new JQueryTransport()
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Transport} transport
	 * @return {EmailAddressValidator}
	 */
	createEmailAddressValidator = function ( validationUrl, transport ) {
		return objectAssign( Object.create( EmailAddressValidator ), {
			validationUrl: validationUrl,
			transport: transport || new JQueryTransport()
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Transport} transport
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, transport ) {
		return new AmountValidator(
			validationUrl,
			transport || new JQueryTransport()
		);
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Object} feeFormatter Formatter object that supports the .format method
	 * @param {Object} transport
	 * @return {FeeValidator}
	 */
	createFeeValidator = function ( validationUrl, feeFormatter, transport ) {
		return objectAssign( Object.create( FeeValidator ), {
			validationUrl: validationUrl,
			feeFormatter: feeFormatter,
			transport: transport || new JQueryTransport()
		} );
	},

	/**
	 *
	 * @param {string} validationUrlForSepaBankData
	 * @param {string} validationUrlForClassicBankData
	 * @param {Transport} transport
	 * @return {BankDataValidator}
	 */
	createBankDataValidator = function ( validationUrlForSepaBankData, validationUrlForClassicBankData, transport ) {
		return new BankDataValidator( validationUrlForSepaBankData, validationUrlForClassicBankData, transport || new JQueryTransport() );
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
