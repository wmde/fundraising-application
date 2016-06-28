'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' );

/**
 * Return object keys that are not defined in initial state
 *
 * @param {Object} state
 * @param {Object} initialState
 * @returns {Array}
 */
function getInvalidKeys( state, initialState ) {
	return _.keys( _.omit( state, _.keys( initialState ) ) );
}

function clearFieldsIfAddressTypeChanges( newState, payload ) {
	if ( payload.contentName !== 'addressType'  ) {
		return;
	}
	switch ( payload.value ) {
		case 'person':
			newState.company = '';
			break;
		case 'firma':
			newState.title = '';
			newState.firstName = '';
			newState.lastName = '';
			break;
		case 'anonym':
			newState.title = '';
			newState.company = '';
			newState.firstName = '';
			newState.lastName = '';
			newState.street = '';
			newState.postcode = '';
			newState.city = '';
			newState.email = '';
			break;
	}
}

function forcePersonalDataForDirectDebit( state ) {
	if ( state.paymentType === 'BEZ' && state.addressType === 'anonym' ) {
		return objectAssign( {}, state, { addressType: 'person' } );
	} else {
		return state;
	}
}

function forceAddressTypeForActiveMembership( state ) {
	if ( state.membershipType === 'active' ) {
		return objectAssign( {}, state, { addressType: 'person' } );
	} else {
		return state;
	}
}

function trimValue( value ) {
	return value.replace( /^\s+|\s+$/gm, '' );
}

module.exports = {
	stateContainsUnknownKeys: function ( state, initialState ) {
		return !_.isEmpty( getInvalidKeys( state, initialState ) );
	},
	getInvalidKeys: getInvalidKeys,
	formContent: function ( state, action ) {
		var newAmount, newState;
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
					throw new Error( 'Unsupported form content name: ' + action.payload.contentName );
				}
				newState = _.clone( state );
				clearFieldsIfAddressTypeChanges( newState, action.payload );
				newState[ action.payload.contentName ] = trimValue( action.payload.value );
				newState = forcePersonalDataForDirectDebit( newState );
				newState = forceAddressTypeForActiveMembership( newState );
				return newState;
			case 'FINISH_BANK_DATA_VALIDATION':
				if ( action.payload.status !== 'OK' ) {
					return state;
				}
				return objectAssign( {}, state, {
					iban: action.payload.iban || '',
					bic: action.payload.bic || '',
					accountNumber: action.payload.account || '',
					bankCode: action.payload.bankCode || '',
					bankName: action.payload.bankName || ''
				} );
			default:
				return state;
		}
	}
};
