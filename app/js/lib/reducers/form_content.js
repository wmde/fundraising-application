'use strict';

var objectAssign = require( 'object-assign' ),
	initialState = {
		amount: 0,
		isCustomAmount: false,
		paymentType: 'BEZ'
	};

module.exports = function formContent( state, action ) {
	var newAmount;
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
		case 'SELECT_PAYMENT_TYPE':
			return objectAssign( {}, state, {
				paymentType: action.payload.paymentType
			} );
		default:
			return state;
	}
};
