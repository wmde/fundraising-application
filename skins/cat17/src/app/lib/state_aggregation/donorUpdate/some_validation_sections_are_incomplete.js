'use strict';

var Validity = require( '../../validation/validation_states' ).Validity;

module.exports = function ( state ) {
	return state.validity.address === Validity.INCOMPLETE;
};
