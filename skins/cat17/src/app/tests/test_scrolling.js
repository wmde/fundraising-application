'use strict';

var test = require( 'tape-catch' ),
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
			};
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

	t.equal( scrolling.calculateElementOffset( element, headerElements ), 1920 );

	delete global.$;

	t.end();
} );

test( 'calculateElementOffset can add the padding of the element to the offset', function ( t ) {
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

	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.PADDDING } ), 2001 );
	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.ELEMENT } ), 2000 );

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

	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.PADDDING } ), 2000 );

	delete global.$;

	t.end();
} );

test( 'calculateElementOffset can subtract the margin of the element from the offset', function ( t ) {
	var element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	headerElements.get.returns( [] );

	element.css.withArgs( 'margin-top' ).returns( '1px' );
	element.offset.returns( { top: 2000 } );

	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.MARGIN } ), 1999 );
	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.ELEMENT } ), 2000 );

	delete global.$;

	t.end();
} );

test( 'calculateElementOffset ignores element margin not given in pixels', function ( t ) {
	var element = {
			css: sinon.stub(),
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub()
		}
	;

	headerElements.get.returns( [] );

	element.css.withArgs( 'margin-top' ).returns( '1em' );
	element.offset.returns( { top: 2000 } );

	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.equal( scrolling.calculateElementOffset( element, headerElements, { elementStart: scrolling.ElementStart.MARGIN } ), 2000 );

	delete global.$;

	t.end();
} );

test( 'findElementWithLowestOffset returns no elements if no elements are given', function ( t ) {
	t.equal( scrolling.findElementWithLowestOffset( [] ), null );

	t.end();
} );

test( 'findElementWithLowestOffset returns no elements if jquery objects conation no DOM nodes ', function ( t ) {

	var firstElement = {
			length: 0
		},
		secondElement = {
			length: 0
		};

	t.equal( scrolling.findElementWithLowestOffset( [ firstElement, secondElement ] ), null );

	t.end();
} );

test( 'findElementWithLowestOffset first non empty element with an offset ', function ( t ) {

	var firstElement = {
			length: 0
		},
		secondElement = {
			length: 1,
			offset: sinon.stub()
		};

	secondElement.offset.returns( { top: 400 } );

	t.equal( scrolling.findElementWithLowestOffset( [ firstElement, secondElement ] ), secondElement );

	t.end();
} );

test( 'findElementWithLowestOffset will return the element with the lowest offset ', function ( t ) {

	var firstElement = {
			length: 1,
			offset: sinon.stub()
		},
		secondElement = {
			length: 1,
			offset: sinon.stub()
		};

	firstElement.offset.returns( { top: 400 } );
	secondElement.offset.returns( { top: 200 } );

	t.equal( scrolling.findElementWithLowestOffset( [ firstElement, secondElement ] ), secondElement );
	t.equal( scrolling.findElementWithLowestOffset( [ secondElement, firstElement ] ), secondElement );

	t.end();
} );

test( 'AnimatedScroller only allows one scrolling at a time', function ( t ) {
	var element = {
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub(),
			addClass: sinon.stub()
		},
		scroller = scrolling.createAnimatedScroller( headerElements ),
		body = {
			stop: sinon.stub().returnsThis(),
			animate: sinon.stub()
		}
	;

	element.offset.returns( { top: 400 } );

	global.$ = sinon.stub();
	global.$.withArgs( 'html, body' ).returns( body );

	scroller.scrollTo( element );

	t.ok( body.stop.withArgs( true ).calledOnce );

	delete global.$;

	t.end();
} );

test( 'AnimatedScroller brings us to the right spot on the page', function ( t ) {
	var element = {
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub(),
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		scroller = scrolling.createAnimatedScroller( headerElements ),
		body = {
			stop: sinon.stub().returnsThis(),
			animate: sinon.stub()
		}
	;

	element.offset.returns( { top: 400 } );

	global.$ = sinon.stub();
	global.$.withArgs( 'html, body' ).returns( body );

	scroller.scrollTo( element );

	t.ok( body.animate.calledOnce );
	t.deepEquals( body.animate.args[ 0 ][ 0 ], { scrollTop: 400 } );

	delete global.$;

	t.end();
} );

test( 'AnimatedScroller scrolls in hard-coded time', function ( t ) {
	var element = {
			offset: sinon.stub()
		},
		headerElements = {
			get: sinon.stub(),
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		scroller = scrolling.createAnimatedScroller( headerElements ),
		body = {
			stop: sinon.stub().returnsThis(),
			animate: sinon.stub()
		}
	;

	element.offset.returns( { top: 400 } );

	global.$ = sinon.stub();
	global.$.withArgs( 'html, body' ).returns( body );

	scroller.scrollTo( element );

	t.ok( body.animate.calledOnce );
	t.equals( body.animate.args[ 0 ][ 1 ], 1000 );

	delete global.$;

	t.end();
} );

test( 'AnimatedScroller treats element once scroll position reached', function ( t ) {
	var element = {
			offset: sinon.stub(),
			focus: sinon.stub(),
			is: sinon.stub(),
			attr: sinon.stub()
		},
		headerElements = {
			get: sinon.stub(),
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		scroller = scrolling.createAnimatedScroller( headerElements ),
		body = {
			stop: sinon.stub().returnsThis(),
			animate: sinon.stub()
		}
	;

	element.offset.returns( { top: 400 } );
	element.is.withArgs( ':focus' ).returns( false );

	global.$ = sinon.stub();
	global.$.withArgs( 'html, body' ).returns( body );

	scroller.scrollTo( element );

	t.ok( body.animate.calledOnce );
	t.equals( typeof body.animate.args[ 0 ][ 2 ], 'function' );

	body.animate.args[ 0 ][ 2 ]();

	t.ok( element.attr.withArgs( 'tabindex', '-1' ).calledOnce );
	t.ok( element.focus.calledTwice );

	delete global.$;

	t.end();
} );

test( 'AnimatedScroller ensures header elements can anticipate scrolling', function ( t ) {
	var element = {
			offset: sinon.stub(),
			focus: sinon.stub(),
			is: sinon.stub(),
			attr: sinon.stub()
		},
		headerElements = {
			get: sinon.stub(),
			addClass: sinon.stub(),
			removeClass: sinon.stub()
		},
		scroller = scrolling.createAnimatedScroller( headerElements ),
		body = {
			stop: sinon.stub().returnsThis(),
			animate: sinon.stub()
		}
	;

	element.offset.returns( { top: 400 } );

	global.$ = sinon.stub();
	global.$.withArgs( 'html, body' ).returns( body );

	scroller.scrollTo( element );

	t.ok( headerElements.addClass.withArgs( 'scrolling' ).calledOnce );
	t.ok( body.animate.calledOnce );
	t.equals( typeof body.animate.args[ 0 ][ 2 ], 'function' );

	body.animate.args[ 0 ][ 2 ]();

	t.ok( headerElements.removeClass.withArgs( 'scrolling' ).calledOnce );

	delete global.$;

	t.end();
} );
