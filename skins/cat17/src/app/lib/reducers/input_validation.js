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

function inputValidation( validationState, action ) {
  var newValidationState = objectAssign( {}, validationState ),
      bankDataIsValid;

	switch ( action.type ) {
		case 'INITIALIZE_VALIDATION':
			_.each( validationState, function ( value, key ) {
				if ( _.has( action.payload.initialValues, key ) ) {
					newValidationState[ key ] = {
						dataEntered: true,
						isValid: !_.has( action.payload.violatedFields, key )
					}
				} else if ( _.has( action.payload.violatedFields, key ) ) {
					newValidationState[ key ] = {
						dataEntered: true,
						isValid: false
					}
				}
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
      } else {
        newValidationState.amount = { dataEntered: true, isValid: !action.payload.messages.amount };
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

		// todo Clean way to transport validator groups (those that belong to a respective group [e.g. address]) from
		// concrete validators (e.g. donation_input_validation) to this generic validation mechanism.
		var addressFieldValidatorNames = [
			'addressType',
			'salutation', 'title', 'firstName', 'lastName',
			'companyName',
			'street', 'postcode', 'city', 'country',
			'email'
		];

		_.forEach( addressFieldValidatorNames, function ( name ) {
			if ( !_.has( newValidationState, name ) ) {
				// this would mean the list of validators in (donation|membership)_input_validation was changed w/o update here
				return;
			}

			if ( newValidationState[ name ].dataEntered === true ) {
				newValidationState[ name ] = {
					dataEntered: true,
					isValid: newValidationState[ name ].isValid !== false && !( _.has( action.payload.messages, name ) )
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
