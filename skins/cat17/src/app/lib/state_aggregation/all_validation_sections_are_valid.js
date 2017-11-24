var _ = require( 'underscore' )

module.exports = function ( state ) {
	'use strict';
	return !_.contains( state.validity, false )
};