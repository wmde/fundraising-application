'use strict';

var test = require( 'tape-catch' ),
	deepFreeze = require( 'deep-freeze' ),
	membershipInputValidation = require( '../../lib/reducers/membership_input_validation' );

function newChangeContentAction( field, newValue ) {
	return {
		type: 'CHANGE_CONTENT',
		payload: {
			contentName: field,
			value: newValue
		}
	};
}

test( 'If an optional field content changes to empty, the validity status is reset', function ( t ) {
	var stateBefore = {
			phoneNumber: { dataEntered: true, isValid: true }
		},
		expectedState = {
			phoneNumber: { dataEntered: false, isValid: null }
		};

	deepFreeze( stateBefore );
	t.deepEqual( membershipInputValidation( stateBefore, newChangeContentAction( 'phoneNumber', '' ) ), expectedState );
	t.end();
} );

test( 'If a non-optional field is emptied, its validity status remains unchanged', function ( t ) {
	var stateBefore = {
			street: { dataEntered: true, isValid: true }
		},
		expectedState = {
			street: { dataEntered: true, isValid: true }
		};

	deepFreeze( stateBefore );
	t.deepEqual( membershipInputValidation( stateBefore, newChangeContentAction( 'street', '' ) ), expectedState );
	t.end();
} );
