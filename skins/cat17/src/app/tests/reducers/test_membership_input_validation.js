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
			dateOfBirth: { dataEntered: true, isValid: true }
		},
		expectedState = {
			dateOfBirth: { dataEntered: false, isValid: null }
		};

	deepFreeze( stateBefore );
	t.deepEqual( membershipInputValidation( stateBefore, newChangeContentAction( 'dateOfBirth', '' ) ), expectedState );
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

test( 'Maintaining sustaining membership type, company name validity remains unchanged', function ( t ) {
	t.deepEqual(
		membershipInputValidation(
			{
				companyName: { dataEntered: true, isValid: true }
			},
			newChangeContentAction( 'membershipType', 'sustaining' )
		),
		{
			companyName: { dataEntered: true, isValid: true }
		}
	);
	t.end();
} );

test( 'Switching to non-sustaining membership type, company name validity is reset', function ( t ) {
	t.deepEqual(
		membershipInputValidation(
			{
				companyName: { dataEntered: true, isValid: true }
			},
			newChangeContentAction( 'membershipType', 'somethingelse' )
		),
		{
			companyName: { dataEntered: false, isValid: null }
		}
	);
	t.end();
} );
