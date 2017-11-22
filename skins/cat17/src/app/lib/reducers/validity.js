'use strict';

/**
 * @todo Consider constants for validity instead of boolean|null - motivate strict checking, improve readability
 */

var objectAssign = require( 'object-assign' ),

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
 * @return {boolean|null}
 */
function convertExternalResult( action ) {
	if ( action.payload.status === 'NOT_APPLICABLE' ) {
		return null;
	}

	return !action.error && action.payload.status === 'OK';
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
