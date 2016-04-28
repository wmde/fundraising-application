'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),
	initialState = {
		amount: 0,
		isCustomAmount: false,
		paymentType: 'BEZ',
		paymentPeriodInMonths: 0, // 0, 1, 3, 6 or 12, 0 = non-recurring payment
		debitType: 'sepa', // sepa and "non-sepa"
		iban: '',
		bic: '',
		accountNumber: '',
		bankCode: '',
		bankName: '',
		addressType: 'person', // person, firma and anonym
		salutation: 'Frau',
		title: '',
		firstName: '',
		lastName: '',
		company: '',
		street: '',
		postcode: '',
		city: '',
		country: 'DE',
		email: ''
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
				throw new Error( 'Unsupported form content name: ' + action.payload.contentName );
			}
			newState = _.clone( state );
			clearFieldsIfAddressTypeChanges( newState, action.payload );
			newState[ action.payload.contentName ] = action.payload.value;
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
