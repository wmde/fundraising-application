'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	extractor = require( '../lib/form_data_extractor' )
;

test( 'Map can be built from select options', function ( t ) {
	var createOption = function () {
			return {
				attr: sinon.stub(),
				text: sinon.stub()
			};
		},
		optionOne = createOption(),
		optionTwo = createOption(),
		options = {
			get: sinon.stub()
		},
		container = {
			find: sinon.stub()
		}
	;

	options.get.returns( [ optionOne, optionTwo ] );
	container.find.withArgs( 'option' ).returns( options );

	optionOne.attr.withArgs( 'value' ).returns( 'onekey' );
	optionOne.text.returns( 'hello' );
	optionTwo.attr.withArgs( 'value' ).returns( 'anotherkey' );
	optionTwo.text.returns( 'world' );

	global.jQuery = sinon.stub();
	global.jQuery.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.deepEqual(
		extractor.mapFromSelectOptions( container ),
		{ onekey: 'hello', anotherkey: 'world' }
	);

	delete global.jQuery;

	t.end();
} );

test( 'Map can be built from radio button labels', function ( t ) {
	var createLabel = function ( labelText ) {
			return {
				text: sinon.stub().returns( labelText )
			};
		},
		createElement = function ( labelText ) {
			return {
				attr: sinon.stub(),
				next: sinon.stub().withArgs( 'label' ).returns( createLabel( labelText ) )
			};
		},
		radioOne = createElement( 'alpha' ),
		radioTwo = createElement( 'beta' ),
		radios = {
			get: sinon.stub()
		},
		container = {
			find: sinon.stub()
		}
	;

	radios.get.returns( [ radioOne, radioTwo ] );
	container.find.withArgs( 'input[type="radio"]' ).returns( radios );

	radioOne.attr.withArgs( 'value' ).returns( 'a' );
	radioTwo.attr.withArgs( 'value' ).returns( 'b' );

	global.jQuery = sinon.stub();
	global.jQuery.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.deepEqual(
		extractor.mapFromRadioLabels( container ),
		{ a: 'alpha', b: 'beta' }
	);

	delete global.jQuery;

	t.end();
} );

test( 'Map can be built from radio button label\'s data attributes', function ( t ) {
	var createLabel = function ( labelText ) {
			return {
				data: sinon.stub().withArgs( 'short-text' ).returns( labelText )
			};
		},
		createElement = function ( labelText ) {
			return {
				attr: sinon.stub(),
				next: sinon.stub().withArgs( 'label' ).returns( createLabel( labelText ) )
			};
		},
		radioOne = createElement( 'alpha' ),
		radioTwo = createElement( 'beta' ),
		radios = {
			get: sinon.stub()
		},
		container = {
			find: sinon.stub()
		}
	;

	radios.get.returns( [ radioOne, radioTwo ] );
	container.find.withArgs( 'input[type="radio"]' ).returns( radios );

	radioOne.attr.withArgs( 'value' ).returns( 'a' );
	radioTwo.attr.withArgs( 'value' ).returns( 'b' );

	global.jQuery = sinon.stub();
	global.jQuery.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.deepEqual(
		extractor.mapFromRadioLabelsShort( container ),
		{ a: 'alpha', b: 'beta' }
	);

	delete global.jQuery;

	t.end();
} );

test( 'Map can be built from radio button info texts', function ( t ) {
	var createLabel = function ( infoText ) {
			return {
				data: sinon.stub().withArgs( 'info-text' ).returns( infoText )
			};
		},
		createElement = function ( infoText ) {
			return {
				attr: sinon.stub(),
				parents: sinon.stub().withArgs( '.wrap-field' ).returns( createLabel( infoText ) )
			};
		},
		radioOne = createElement( 'my longer text' ),
		radioTwo = createElement( 'to someone' ),
		radioThree = createElement( 'who reads it' ),
		radios = {
			get: sinon.stub()
		},
		container = {
			find: sinon.stub()
		}
	;

	radios.get.returns( [ radioOne, radioTwo, radioThree ] );
	container.find.withArgs( '.wrap-input input[type="radio"]' ).returns( radios );

	radioOne.attr.withArgs( 'value' ).returns( 'uno' );
	radioTwo.attr.withArgs( 'value' ).returns( 'dos' );
	radioThree.attr.withArgs( 'value' ).returns( 'tres' );

	global.jQuery = sinon.stub();
	global.jQuery.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	t.deepEqual(
		extractor.mapFromRadioInfoTexts( container ),
		{ uno: 'my longer text', dos: 'to someone', tres: 'who reads it' }
	);

	delete global.jQuery;

	t.end();
} );
