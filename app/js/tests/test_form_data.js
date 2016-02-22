'use strict';

var test = require( 'tape' ),
	jsdom = require( 'jsdom' ),
	formData = require( '../lib/form_data' );

test( 'RadioValueAccessor filters out unchecked elements and returns value', function ( t ) {
	var testHTML = '<div>' +
		'<input type="radio" name="test" value="23" />' +
		'<input type="radio" name="test" value="42" checked="checked" />' +
		'</div>';
	jsdom.env( {
		html: testHTML,
		done: function ( errs, window ) {

			var $ = require( 'jquery' )( window ),
				radioValues = formData.createRadioValueAccessor( $( 'input[name=test]' ) );
			t.equal( radioValues.getValue(), '42' );
			t.end();
		}
	} );
} );

test( 'TextValueAccessor returns value', function ( t ) {
	var testHTML = '<div>' +
		'<input type="text" name="test" id="inputWithoutValue" />' +
		'<input type="text" name="test" value="42" id="inputWithValue" />' +
		'</div>';
	jsdom.env( {
		html: testHTML,
		done: function ( errs, window ) {

			var $ = require( 'jquery' )( window ),
				emptyValue = formData.createTextValueAccessor( $( '#inputWithoutValue' ) ),
				filledValue = formData.createTextValueAccessor( $( '#inputWithValue' ) );
			t.equal( emptyValue.getValue(), '' );
			t.equal( filledValue.getValue(), '42' );
			t.end();
		}
	} );
} );

test( 'MultipleValueAccessor returns first truthy value', function ( t ) {
	var getValStub = function ( returnValue ) {
		return { getValue: function () { return returnValue; } };
	};

	t.equal(
		formData.createMultipleValueAccessor( getValStub( null ), getValStub( 42 ) ).getValue(),
		42
	);

	t.equal(
		formData.createMultipleValueAccessor( getValStub( 23 ), getValStub( undefined ) ).getValue(),
		23
	);

	t.equal(
		formData.createMultipleValueAccessor( getValStub( 23 ), getValStub( 42 ) ).getValue(),
		23
	);

	t.end();
} );

