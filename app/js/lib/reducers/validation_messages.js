'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' );

function addStateIfStatusIsNotOk( state, validationResult, additionalState ) {
	if ( validationResult === 'OK' ) {
		return _.omit( state, Object.keys( additionalState ) );
	} else {
		return objectAssign( {}, state, additionalState );
	}
}

function validationMessages( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = {};
	}
	switch ( action.type ) {
		case 'FINISH_AMOUNT_VALIDATION':
			return addStateIfStatusIsNotOk( state, action.payload.status, { amount: action.payload.message } );
		case 'FINISH_ADDRESS_VALIDATION':
			return addStateIfStatusIsNotOk( state, action.payload.status, { address: action.payload.message } );
		default:
			return state;
	}
}

module.exports = validationMessages;
