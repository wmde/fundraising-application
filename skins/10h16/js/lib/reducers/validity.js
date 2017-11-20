'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	defaultState = {
		paymentData: null,
		address: null,
		bankData: null,
		sepaConfirmation: null
	};

/**
 * Check if action has error flag set (happens when the AJAX request fails) and if payload.status is 'OK'.
 *
 * This should be used for server validation results that have a 'status' field.
 *
 * @param {Object} action Redux standard action
 * @return {boolean}
 */
function convertExternalResult( action ) {
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
	var newState = objectAssign( {}, state );
	if ( typeof state !== 'object' ) {
		state = defaultState;
	}
	switch ( action.type ) {
		case 'INITIALIZE_VALIDATION':
			if ( !_.isEmpty( action.payload.initialValidationResult ) ) {
				newState = _.extendOwn( state, action.payload.initialValidationResult );
			}
			return newState;
		case 'FINISH_PAYMENT_DATA_VALIDATION':
			return objectAssign( {}, state, { paymentData: convertExternalResult( action ) } );
		case 'FINISH_ADDRESS_VALIDATION':
			return objectAssign( {}, state, { address: convertExternalResult( action ) } );
		case 'FINISH_BANK_DATA_VALIDATION':
			return objectAssign( {}, state, {
				bankData: convertExternalResult( action )
			} );
		case 'FINISH_SEPA_CONFIRMATION_VALIDATION':
			return objectAssign( {}, state, { sepaConfirmation: action.payload } );
		default:
			return state;
	}
}

module.exports = validity;
