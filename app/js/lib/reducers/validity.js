'use strict';

var objectAssign = require( 'object-assign' ),

	defaultState = {
		amount: null
		// TODO: Add different fields like address and email
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
	if ( typeof state !== 'object' ) {
		state = defaultState;
	}
	switch ( action.type ) {
		case 'FINISH_AMOUNT_VALIDATION':
			return objectAssign( {}, state, { amount: convertExternalResult( action ) } );
		default:
			return state;
	}
}

module.exports = validity;
