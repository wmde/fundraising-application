'use strict';

var sinon = require( 'sinon' ),
	objectAssign = require( 'object-assign' )
;

/**
 * jQuery is used as HTML generator - for this test we let it become a super-charged string object with access to
 * - the original construction parameter (HTML) via .toString()
 * - the methods called on the wanna-be node via the properties
 */
module.exports = function ( arg0 ) {
	return objectAssign( arg0, {
		addClass: sinon.stub().returnsThis(),
		text: sinon.stub().returnsThis(),
		append: sinon.stub().returnsThis()
	} );
};
