'use strict';

var jQueryElementStub = require( '../../jQueryElementStub' );

/**
 * Create a "node" comparable to the "container" property of the Base class
 */
module.exports = function () {
	var node = jQueryElementStub();
	node.find.withArgs( '.opened' ).returns( jQueryElementStub() );
	return node;
};
