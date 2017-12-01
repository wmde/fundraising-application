'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	scrolling = require( '../lib/scrolling' )
;

test( 'calculateElementOffset returns the offset of the element', function ( t ) {
	var element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	headerElements.get.returns( [] );

	element.css.returns( '' );
	element.offset.returns( { top: 2000 } );

	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements ), 2000 );

	delete global.$;

	t.end();
} );


test( 'calculateElementOffset subtracts the height of visible header elements', function ( t ) {
	var createElement = function () {
			return {
				is: sinon.stub(),
				height: sinon.stub()
			}
		},
		firstHeaderElement = createElement(),
		secondHeaderElement = createElement(),
		thirdHeaderElement = createElement(),
		element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	firstHeaderElement.height.returns( 50 );
	firstHeaderElement.is.withArgs( ':visible' ).returns( true );
	secondHeaderElement.height.returns( 20 );
	secondHeaderElement.is.withArgs( ':visible' ).returns( false );
	thirdHeaderElement.height.returns( 30 );
	thirdHeaderElement.is.withArgs( ':visible' ).returns( true );
	headerElements.get.returns( [ firstHeaderElement, secondHeaderElement, thirdHeaderElement ] );

	element.css.returns( '' );
	element.offset.returns( { top: 2000 } );

	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements ), 1920	);

	delete global.$;

	t.end();
} );

test( 'calculateElementOffset add the padding of the element to the offset', function ( t ) {
	var element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	headerElements.get.returns( [] );

	element.css.withArgs( 'padding-top' ).returns( '1px' );
	element.offset.returns( { top: 2000 } );


	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements ), 2001 );

	delete global.$;

	t.end();
} );

test( 'calculateElementOffset ignores element padding not given in pixels', function ( t ) {
	var element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	headerElements.get.returns( [] );

	element.css.withArgs( 'padding-top' ).returns( '1em' );
	element.offset.returns( { top: 2000 } );


	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements ), 2000 );

	delete global.$;

	t.end();
} );



