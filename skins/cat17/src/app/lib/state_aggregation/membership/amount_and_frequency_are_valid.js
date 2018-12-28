'use strict';

var Validity = require( '../../validation/validation_states' ).Validity;

/**
 * @param {Object} state
 * @return {boolean}
 */
function getDataEntered( state ) {
	return state.membershipFormContent.paymentIntervalInMonths > 0 || state.membershipFormContent.amount !== 0;
}

/**
 * @param {Object} state
 * @return {?boolean}
 */
function getValidity( state ) {
	if ( state.membershipFormContent.paymentIntervalInMonths > 0 &&
		state.membershipFormContent.amount !== 0 &&
		state.membershipInputValidation.amount.isValid === Validity.VALID ) {
		return Validity.VALID;
	} else if ( state.membershipInputValidation.amount.isValid === Validity.INCOMPLETE ) {
		return Validity.INCOMPLETE;
	} else {
		return Validity.INVALID;
	}
}

module.exports = function ( state ) {
	return {
		dataEntered: getDataEntered( state ),
		isValid: getValidity( state )
	};
};
