'use strict';

var _ = require( 'underscore' ),
  inputValidationLib = require( './input_validation' ),
  defaultFields = {
    dataEntered: false,
    isValid: null
  },
  initialState = {
    amount: _.clone( defaultFields ),
    paymentType: _.clone( defaultFields ),
    salutation: _.clone( defaultFields ),
    firstName: _.clone( defaultFields ),
    lastName: _.clone( defaultFields ),
    companyName: _.clone( defaultFields ),
    street: _.clone( defaultFields ),
    postcode: _.clone( defaultFields ),
    city: _.clone( defaultFields ),
    email: _.clone( defaultFields ),
    iban: _.clone( defaultFields ),
    bic: _.clone( defaultFields ),
    accountNumber: _.clone( defaultFields ),
    bankCode: _.clone( defaultFields )
  },

  setValidityOnSalutationChange = function ( state, action ) {
    if ( action.type !== 'CHANGE_CONTENT' ||
      action.payload.contentName !== 'salutation' ) {
      return state;
    }
    return _.extend( {}, state, {
      salutation: { dataEntered: true, isValid: true }
    } );
  },

	setValidityOfPaymentType = function ( state, action ) {
		if ( action.type === 'CHANGE_CONTENT' && action.payload.contentName === 'paymentType' ) {
			return _.extend( {}, state, {
				paymentType: {
					dataEntered: action.payload.value !== '',
					isValid: action.payload.value !== ''
				}
			} );
		}
		if ( action.type === 'INITIALIZE_CONTENT' && typeof action.payload.paymentType !== 'undefined' ) {
			return _.extend( {}, state, {
				paymentType: {
					dataEntered: action.payload.paymentType !== '',
					isValid: action.payload.paymentType !== ''
				}
			} );
		}
		return state;
	}
;

module.exports = function donationInputValidation( state, action ) {
  if ( typeof state === 'undefined' ) {
    state = initialState;
  }

  state = setValidityOnSalutationChange( state, action );
	state = setValidityOfPaymentType( state, action );

  return inputValidationLib.inputValidation( state, action );
};
