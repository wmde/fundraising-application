'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	jQueryElementStub = require( '../../jQueryElementStub' ),
	createContainerElement = require( './createContainerElement' ),
	Factory = require( '../../../lib/view_handler/section_info/factory' )
;

test( 'Instance correctly detects and applies sub-elements', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler
	;

	container.find.withArgs( 'i:not(".link")' ).returns( icon );
	container.find.withArgs( '.text' ).returns( text );
	container.find.withArgs( '.info-detail' ).returns( longText );

	handler = Factory.createInstance( {}, container );

	t.deepEquals( handler.container, container );
	t.deepEquals( handler.icon, icon );
	t.deepEquals( handler.text, text );
	t.deepEquals( handler.longText, longText );

	t.ok( container.find.withArgs( 'i:not(".link")' ).calledOnce );
	t.ok( container.find.withArgs( '.text' ).calledOnce );
	t.ok( container.find.withArgs( '.info-detail' ).calledOnce );

	t.end();
} );

test( 'Instance is created with properties applied', function ( t ) {
	var container = createContainerElement(),
		iconMap = { a: 1 },
		textMap = { a: 2 },
		longTextMap = { a: 3 },
		additionalProperties = { alpha: 'gamma' },
		handler = Factory.createInstance( {}, container, iconMap, textMap, longTextMap, additionalProperties )
	;

	t.deepEquals( handler.valueIconMap, iconMap );
	t.deepEquals( handler.valueTextMap, textMap );
	t.deepEquals( handler.valueLongTextMap, longTextMap );

	t.deepEquals( handler.alpha, 'gamma' );

	t.end();
} );

test( 'Proxy forwards calls and arguments', function ( t ) {
	var widgetOneDom = jQueryElementStub(),
		widgetTwoDom = jQueryElementStub(),
		fakeType = {
			update: sinon.stub()
		},
		// IRL a jQuery object that matched multiple DOM nodes
		containers = {
			get: sinon.stub().returns( [ widgetOneDom, widgetTwoDom ] )
		},
		proxy
	;

	global.jQuery = sinon.stub();
	global.jQuery.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	proxy = Factory.createProxy( fakeType, containers, {}, {}, {}, {} );

	proxy.update( 'a', 'b', 'c' );

	t.ok( proxy.widgets instanceof Array );
	t.equals( proxy.widgets.length, 2 );
	t.deepEquals( proxy.widgets[ 0 ].update.firstCall.args, [ 'a', 'b', 'c' ] );
	t.deepEquals( proxy.widgets[ 1 ].update.firstCall.args, [ 'a', 'b', 'c' ] );

	delete global.jQuery;
	t.end();
} );
