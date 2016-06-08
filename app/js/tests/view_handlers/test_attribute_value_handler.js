'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	attributeValueHandler = require( '../../lib/view_handler/attribute_value_handler' ),
	createAttributeValueHandler = attributeValueHandler.createAttributeValueHandler,
	createElement = function () {
		return {
			attr: sinon.spy()
		};
	};

test( 'Attribute values are changed', function ( t ) {
	var postCodeElement = createElement(),
		cityElement = createElement(),
		emailElement = createElement(),
		handler = createAttributeValueHandler( postCodeElement, cityElement, emailElement );

	handler.update(
		{
			'post-code': {
				'data-pattern': 'whatever'
			},
			city: {
				placeholder: 'something else'
			},
			email: {
				title: 'a title'
			}
		}
	);

	t.ok( postCodeElement.attr.withArgs( 'data-pattern', 'whatever' ).called, 'regex validation pattern was set' );
	t.ok( cityElement.attr.withArgs( 'placeholder', 'something else' ).called, 'placeholder attribute was set' );
	t.ok( emailElement.attr.withArgs( 'title', 'a title' ).called, 'title attribute was set' );

	t.end();
} );
