'use strict';

module.exports = {
	newAddPageAction: function ( pageName ) {
		return {
			type: 'ADD_PAGE',
			payload: { name: pageName }
		};
	},

	newNextPageAction: function () {
		return {
			type: 'NEXT_PAGE'
		};
	},

	newPreviousPageAction: function () {
		return {
			type: 'PREVIOUS_PAGE'
		};
	},

	/**
	 *
	 * @param {Number} amount Amount as Integer
	 * @return {{type: string, payload: {amount: *}}}
	 */
	newSelectAmountAction: function ( amount ) {
		return {
			type: 'SELECT_AMOUNT',
			payload: { amount: amount }
		};
	},

	/**
	 *
	 * @param {Number} amount Amount as Integer
	 * @return {{type: string, payload: {amount: *}}}
	 */
	newInputAmountAction: function ( amount ) {
		return {
			type: 'INPUT_AMOUNT',
			payload: { amount: amount }
		};
	},

	/**
	 * Set initial form content
	 *
	 * @param {{Object}} initialContent
	 * @returns {{type: string, payload: *}}
	 */
	newInitializeContentAction: function ( initialContent ) {
		return {
			type: 'INITIALIZE_CONTENT',
			payload: initialContent
		};
	},

	newChangeContentAction: function ( contentName, newValue ) {
		return {
			type: 'CHANGE_CONTENT',
			payload: {
				contentName: contentName,
				value: newValue
			}
		};
	},

	/**
	 * Set initial form content
	 *
	 * @param {{Object}} violatedFields
	 * @param {{Object}} initialValidationResult
	 * @returns {{type: string, payload: *}}
	 */
	newInitializeValidationStateAction: function ( violatedFields, initialValidationResult ) {
		return {
			type: 'INITIALIZE_VALIDATION',
			payload: {
				violatedFields: violatedFields,
				initialValidationResult: initialValidationResult
			}
		};
	},

	newValidateInputAction: function ( contentName, newValue, pattern ) {
		return {
			type: 'VALIDATE_INPUT',
			payload: {
				contentName: contentName,
				value: newValue,
				pattern: pattern
			}
		};
	},

	newMarkEmptyFieldsInvalidAction: function ( requiredFields, neutralFields ) {
		return {
			type: 'MARK_EMPTY_FIELD_INVALID',
			payload: {
				requiredFields: requiredFields,
				neutralFields: neutralFields
			}
		};
	},

	/**
	 *
	 * @param {Object|Promise} validationResult
	 * @return {{type: string, payload: *}}
	 */
	newFinishAddressValidationAction: function ( validationResult ) {
		return {
			type: 'FINISH_ADDRESS_VALIDATION',
			payload: validationResult
		};
	},

	/**
	 *
	 * @param {Object|Promise} validationResult
	 * @return {{type: string, payload: *}}
	 */
	newFinishEmailAddressValidationAction: function ( validationResult ) {
		return {
			type: 'FINISH_EMAIL_ADDRESS_VALIDATION',
			payload: validationResult
		};
	},

	/**
	 *
	 * @param {Object|Promise} validationResult
	 * @return {{type: string, payload: *}}
	 */
	newFinishPaymentDataValidationAction: function ( validationResult ) {
		return {
			type: 'FINISH_PAYMENT_DATA_VALIDATION',
			payload: validationResult
		};
	},

	/**
	 *
	 * @param {Object|Promise} validationResult
	 * @return {{type: string, payload: *}}
	 */
	newFinishBankDataValidationAction: function ( validationResult ) {
		return {
			type: 'FINISH_BANK_DATA_VALIDATION',
			payload: validationResult
		};
	},

	/**
	 *
	 * @param {Object} formData
	 * @return {{type: string, payload: *}}
	 */
	newBeginAddressValidationAction: function ( formData ) {
		return {
			type: 'BEGIN_ADDRESS_VALIDATION',
			payload: formData
		};
	},

	/**
	 *
	 * @param {Object} formData
	 * @return {{type: string, payload: *}}
	 */
	newBeginEmailAddressValidationAction: function ( formData ) {
		return {
			type: 'BEGIN_EMAIL_ADDRESS_VALIDATION',
			payload: formData
		};
	},

	/**
	 *
	 * @param {Object} formData
	 * @return {{type: string, payload: *}}
	 */
	newBeginPaymentDataValidationAction: function ( formData ) {
		return {
			type: 'BEGIN_PAYMENT_DATA_VALIDATION',
			payload: formData
		};
	},

	/**
	 *
	 * @param {Object} formData
	 * @return {{type: string, payload: *}}
	 */
	newBeginBankDataValidationAction: function ( formData ) {
		return {
			type: 'BEGIN_BANK_DATA_VALIDATION',
			payload: formData
		};
	},

	/**
	 *
	 * @return {{type: string}}
	 */
	newUserInteractedAction: function () {
		return {
			type: 'USER_INTERACTION'
		}
	}

};
