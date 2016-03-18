'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	initialState = {
		amount: 0,
		isCustomAmount: false,
		paymentType: 'BEZ',
		paymentPeriodInMonths: 0 // 0, 1, 3, 6 or 12, 0 = non-recurring payment
	};

/**
 * Return object keys that are not defined in initial state
 *
 * @param {Object} state
 * @returns {Array}
 */
function getInvalidKeys( state ) {
	return _.keys( _.omit( state, _.keys( initialState ) ) );
}

function stateContainsUnknownKeys( state ) {
	return !_.isEmpty( getInvalidKeys( state ) );
}

module.exports = function formContent( state, action ) {
	var newAmount, newState;
	if ( typeof state === 'undefined' ) {
		state = initialState;
	}
	switch ( action.type ) {
		case 'SELECT_AMOUNT':
			newAmount = action.payload.amount === null ? state.amount : action.payload.amount;
			return objectAssign( {}, state, {
				amount: newAmount,
				isCustomAmount: false
			} );
		case 'INPUT_AMOUNT':
			return objectAssign( {}, state, {
				amount: action.payload.amount,
				isCustomAmount: true
			} );
		case 'CHANGE_CONTENT':
			if ( !_.has( state, action.payload.contentName ) ) {
				throw new Error( 'Unsupported from content name: ' + action.payload.contentName );
			}
			newState = _.clone( state );
			newState[ action.payload.contentName ] = action.payload.value;
			return newState;
		case 'INITIALIZE_CONTENT':
			if ( stateContainsUnknownKeys( action.payload ) ) {
				throw new Error(
					'Initial state contains unknown keys: ' + getInvalidKeys( action.payload ).join( ', ' )
				);
			}
			return objectAssign( {}, state, action.payload );
		default:
			return state;
	}
};
