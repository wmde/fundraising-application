'use strict';

var objectAssign = require( 'object-assign' ),
	defaultState = { count: 0 }

module.exports = function userInteractionCount( state, action ) {
	if ( typeof state === 'undefined' ) {
		state = defaultState;
	}

	if ( [ 'CHANGE_CONTENT', 'SELECT_AMOUNT', 'INPUT_AMOUNT', 'USER_INTERACTION' ].indexOf( action.type ) > -1 ) {
		return objectAssign( {}, state, { count: state.count + 1 } );
	}

	return state;
};