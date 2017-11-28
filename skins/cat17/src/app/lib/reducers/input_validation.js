'use strict';

var _ = require( 'underscore' ),
  objectAssign = require( 'object-assign' ),
  ValidationStates = require( '../form_validation' ).ValidationStates;

function inputIsValid( value, pattern ) {
  if ( pattern === null ) {
    return value !== '';
  }
  return new RegExp( pattern ).test( value );
}

/**
 * @param {boolean} formWasFilledBefore
 * @return {Function}
 */
function newDataEnteredState( formWasFilledBefore ) {
	if ( formWasFilledBefore ) {
		return function () {
			return true;
		};
	} else {
		return function ( state ) {
			return state;
		};
	}
}

function inputValidation( validationState, action ) {
  var newValidationState = objectAssign( {}, validationState ),
      bankDataIsValid, dataEnteredTransformer;

	switch ( action.type ) {
		case 'INITIALIZE_VALIDATION':
			// We have no indicator if the form was freshly loaded with default values, called with payment data from
			// a banner or reloaded with validation errors. So we try to determine the state of "dataEntered" by
			// looking at the validation info from the server. In case of coming from the banner,
			// initialValidationResult is filled, in case of validation errors, violatedFields is filled.
			// In all other cases, we can't decide and just pass the initial state from the reducer, but that should be ok
			dataEnteredTransformer = newDataEnteredState(
				!_.isEmpty( action.payload.violatedFields ) ||
				!_.isEmpty( action.payload.initialValidationResult )
			);
			_.each( validationState, function ( value, key ) {
				newValidationState[ key ] = {
					dataEntered: dataEnteredTransformer( validationState[ key ].dataEntered ),
					isValid: _.has( action.payload.violatedFields, key ) ? false : validationState[ key ].isValid
				};
			} );
			return newValidationState;
    case 'VALIDATE_INPUT':
      if ( validationState[ action.payload.contentName ].dataEntered === false && action.payload.value === '' ) {
        return validationState;
      }

      newValidationState[ action.payload.contentName ] = {
        dataEntered: true,
        isValid: inputIsValid( action.payload.value, action.payload.pattern )
      };
      return newValidationState;
    case 'MARK_EMPTY_FIELD_INVALID':
      _.each( action.payload.requiredFields, function ( key ) {
        if ( newValidationState[ key ].isValid === null ) {
          newValidationState[ key ].isValid = false;
        }
      } );
      _.each( action.payload.neutralFields, function ( key ) {
        newValidationState[ key ].isValid = null;
      } );
      return newValidationState;
    case 'FINISH_PAYMENT_DATA_VALIDATION':
      if ( action.payload.status === ValidationStates.INCOMPLETE ) {
        return newValidationState;
      } else if ( action.payload.status === ValidationStates.OK ) {
        newValidationState.amount =  { dataEntered: true, isValid: true };
        newValidationState.paymentType = { dataEntered: true, isValid: true };
      } else {
        newValidationState.amount = { dataEntered: true, isValid: !action.payload.messages.amount };
        newValidationState.paymentType = { dataEntered: true, isValid: !action.payload.messages.paymentType };
      }
      return newValidationState;
    case 'FINISH_BANK_DATA_VALIDATION':
      if ( action.payload.status === ValidationStates.INCOMPLETE || action.payload.status === ValidationStates.NOT_APPLICABLE ) {
        return newValidationState;
      }
      bankDataIsValid = action.payload.status !== ValidationStates.ERR;
      newValidationState.iban = { dataEntered: true, isValid: bankDataIsValid };
      if ( action.payload.bic || !bankDataIsValid ) {
        newValidationState.bic = { dataEntered: true, isValid: bankDataIsValid };
      }
      if ( action.payload.account || !bankDataIsValid ) {
        newValidationState.accountNumber = { dataEntered: true, isValid: bankDataIsValid };
      }
      if ( action.payload.bankCode || !bankDataIsValid ) {
        newValidationState.bankCode = { dataEntered: true, isValid: bankDataIsValid };
      }
      return newValidationState;
    case 'FINISH_EMAIL_ADDRESS_VALIDATION':
      if ( action.payload.status === ValidationStates.INCOMPLETE ) {
        return newValidationState;
      }
      newValidationState.email = { dataEntered: true, isValid: action.payload.status !== ValidationStates.ERR };
      return newValidationState;
    case 'FINISH_ADDRESS_VALIDATION':
      if ( action.payload.status === ValidationStates.INCOMPLETE ) {
        return newValidationState;
      }
      _.forEach( newValidationState, function ( value, key ) {
        if ( newValidationState[ key ].dataEntered === true ) {
          newValidationState[ key ] = {
            dataEntered: true,
            isValid: newValidationState[ key ].isValid !== false && !( _.has( action.payload.messages, key ) )
          };
        }
      } );
      return newValidationState;
    default:
      return validationState;
  }
}

module.exports = {
  inputValidation: inputValidation
};
