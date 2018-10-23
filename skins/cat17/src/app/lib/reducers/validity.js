'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	{ ValidationStates, Validity } = require( '../validation/validation_states' ),
	defaultState = {
		paymentData: null,
		address: null,
		bankData: null
	};

/**
 * Check if action has error flag set (happens when the AJAX request fails) and if payload.status is 'OK'.
 *
 * This should be used for server validation results that have a 'status' field.
 *
 * @param {Object} action Redux standard action
 * @return {Validity.VALID|Validity.INVALID|Validity.INCOMPLETE}
 */
function convertExternalResult( action ) {
	if ( action.payload.status === ValidationStates.INCOMPLETE ) {
		return Validity.INCOMPLETE;
	}

	return ( !action.error && action.payload.status === ValidationStates.OK ) ? Validity.VALID : Validity.INVALID;
}

/**
 * Handle FINISH_XXX_VALIDATION actions and store the validity
 *
 * @param {Object} state Validity settings for
 * @param {Object} action Redux standard action
 * @return {Object}
 */
function validity( state, action ) {
	var newState = {};

	if ( typeof state !== 'object' ) {
		state = defaultState;
	}

	switch ( action.type ) {
		case 'INITIALIZE_VALIDATION':
			if ( !_.isEmpty( action.payload.initialValidationResult ) ) {
				newState = _.extendOwn( state, action.payload.initialValidationResult );
			}
			break;
		case 'FINISH_PAYMENT_DATA_VALIDATION':
			newState = { paymentData: convertExternalResult( action ) };
			break;
		case 'FINISH_ADDRESS_VALIDATION':
			newState = { address: convertExternalResult( action ) };
			break;
		case 'FINISH_BANK_DATA_VALIDATION':
			newState = { bankData: convertExternalResult( action ) };
			break;
	}

	return objectAssign( defaultState, state, newState );
}

module.exports = validity;
