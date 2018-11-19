'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' );

/**
 * Return object keys that are not defined in initial state
 *
 * @param {Object} state
 * @param {Object} initialState
 * @return {Array}
 */
function getInvalidKeys( state, initialState ) {
	return _.keys( _.omit( state, _.keys( initialState ) ) );
}

function trimValue( value ) {
	return value.replace( /^\s+|\s+$/gm, '' );
}

function forcePersonalDataForDirectDebit( state ) {
	if ( state.paymentType === 'BEZ' && state.addressType === 'anonym' ) {
		return objectAssign( {}, state, { addressType: 'person' } );
	}
	return state;
}

function forceAddressTypeForActiveMembership( state ) {
	if ( state.membershipType === 'active' ) {
		return objectAssign( {}, state, { addressType: 'person' } );
	}
	return state;
}

function forceOneTimePaymentForSofort( state ) {
	if ( state.paymentType === 'SUB' && state.paymentIntervalInMonths > 0 ) {
		return objectAssign( {}, state, { paymentIntervalInMonths: 0 } );
	}
	return state;
}

module.exports = {
	stateContainsUnknownKeys: function ( state, initialState ) {
		return !_.isEmpty( getInvalidKeys( state, initialState ) );
	},
	getInvalidKeys: getInvalidKeys,
	formContent: function ( state, action ) {
		var newAmount,
			newState;
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

				if ( _.isString( action.payload.value ) ) {
					newState[ action.payload.contentName ] = trimValue( action.payload.value );
				} else {
					newState[ action.payload.contentName ] = action.payload.value;
				}

				newState = forcePersonalDataForDirectDebit( newState );
				newState = forceAddressTypeForActiveMembership( newState );
				newState = forceOneTimePaymentForSofort( newState );
				return newState;
			case 'FINISH_BANK_DATA_VALIDATION':
				if ( action.payload.status !== 'OK' ) {
					if ( /^(DE).*$/.test( state.iban ) ) {
						return objectAssign( {}, state, {
							bic: '',
							bankName: ''
						} );
					}
					return state;
				}
				return objectAssign( {}, state, {
					iban: action.payload.iban || '',
					bic: action.payload.bic || state.bic || '',
					bankName: action.payload.bankName || ''
				} );
			default:
				return state;
		}
	}
};
