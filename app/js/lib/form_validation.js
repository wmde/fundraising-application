'use strict';

var jQuery = require( 'jquery' ),
	objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),

	AddressValidator = {
		validationUrl: '',
		postFunction: null,

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

			// Skip validation if not all required fields have been filled
			if ( this.formValuesHaveEmptyRequiredFields( formValues, requiredFields ) ) {
				return null;
			}

			return this.postFunction( this.validationUrl, formValues, null, 'json' );
		},
		formValuesHaveEmptyRequiredFields: function ( formValues, requiredFields ) {
			var objectWithOnlyTheRequiredFields = _.pick( formValues, requiredFields ),
				isEmptyString = function ( value ) { return value === ''; };
			return _.find( objectWithOnlyTheRequiredFields, isEmptyString ) !== undefined;
		}
	},

	AmountValidator = {
		validationUrl: '',
		postFunction: null,
		validate: function ( formValues ) {
			var postData = {
				amount: formValues.amount,
				paymentType: formValues.paymentType
			};
			return this.postFunction( this.validationUrl, postData, null, 'json' );
		}
	},

	createAddressValidator = function ( validationUrl, postFunction ) {
		return objectAssign( Object.create( AddressValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	},

	/**
	 *
	 * @param {string} validationUrl
	 * @param {Function} postFunction jQuery.post function or equivalent
	 * @return {AmountValidator}
	 */
	createAmountValidator = function ( validationUrl, postFunction ) {
		return objectAssign( Object.create( AmountValidator ), {
			validationUrl: validationUrl,
			postFunction: postFunction || jQuery.post
		} );
	};

module.exports = {
	createAmountValidator: createAmountValidator,
	createAddressValidator: createAddressValidator
};
