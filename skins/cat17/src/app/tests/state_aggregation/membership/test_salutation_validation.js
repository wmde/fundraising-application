'use strict';

var test = require( 'tape-catch' ),
	salutationIsValid = require( '../../../lib/state_aggregation/membership/salutation_is_valid' )
;

test( 'Salutation is undetermined for non-private address', function ( t ) {
	var state = {
			membershipFormContent: {
				addressType: 'firma',
				salutation: ''
			}
		},
		expectedValidation = {
			isValid: null,
			dataEntered: false
		};
	t.deepEqual( salutationIsValid( state ), expectedValidation );
	t.end();
} );

test( 'Salutation is undetermined when no names are given for private address', function ( t ) {
	var state = {
			membershipFormContent: {
				addressType: 'person',
				firstName: '',
				lastName: '',
				salutation: ''
			}
		},
		expectedValidation = {
			isValid: null,
			dataEntered: false
		};
	t.deepEqual( salutationIsValid( state ), expectedValidation );
	t.end();
} );

test( 'Salutation is invalid when names are given for private address and salutation is empty', function ( t ) {
	var state = {
			membershipFormContent: {
				addressType: 'person',
				firstName: 'Kylo',
				lastName: 'Ren',
				salutation: ''
			}
		},
		expectedValidation = {
			isValid: false,
			dataEntered: true
		};
	t.deepEqual( salutationIsValid( state ), expectedValidation );
	t.end();
} );

test( 'Salutation is valid when salutation is given', function ( t ) {
	var state = {
			membershipFormContent: {
				salutation: 'Herr'
			}
		},
		expectedValidation = {
			isValid: true,
			dataEntered: true
		};
	t.deepEqual( salutationIsValid( state ), expectedValidation );
	t.end();
} );
