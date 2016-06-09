'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	countrySpecificValidation = require( '../../lib/reducers/country_specifics' );

test( 'CHANGE_CONTENT updates all defined element attributes', function ( t ) {
	var stateBefore = {},
		expectedState = {
			'post-code': {
				'data-pattern': '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 4020',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Linz'
			},
			email: {
				placeholder: 'z. B. name@domain.at'
			}
		};

	deepFreeze( stateBefore );
	t.deepEqual( countrySpecificValidation( stateBefore, {
		type: 'CHANGE_CONTENT',
		payload: {
			contentName: 'country',
			value: 'AT'
		}
	} ), expectedState );
	t.end();
} );
