'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	addressFields = [
		'salutation',
		'title',
		'firstName',
		'lastName',
		'company',
		'street',
		'postcode',
		'city',
		'country',
		'email'
	];

function addStateIfStatusIsNotOk( state, validationResult, additionalState ) {
	if ( validationResult === 'OK' ) {
		return _.omit( state, Object.keys( additionalState ) );
	} else {
		return objectAssign( {}, state, additionalState );
	}
}

function setAddressState( state, validationResult, additionalState ) {
	var newState = _.omit( state, addressFields );
	if ( validationResult === 'OK' ) {
		return newState;
	} else {
		return objectAssign( {}, newState, additionalState );
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
			return setAddressState( state, action.payload.status, action.payload.messages );
		default:
			return state;
	}
}

module.exports = validationMessages;
