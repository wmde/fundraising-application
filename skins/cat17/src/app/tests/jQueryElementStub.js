'use strict';

var sinon = require( 'sinon' );

// your typical jQuery extended DOM node
module.exports = function () {
	return {
		find: sinon.stub(),
		text: sinon.stub(),
		html: sinon.stub(),
		addClass: sinon.stub(),
		removeClass: sinon.stub(),
		toggleClass: sinon.stub(),
		data: sinon.stub(),
		prepend: sinon.stub(),
		append: sinon.stub()
	};
};
