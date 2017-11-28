'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	DELIMITER_KEYCODE_MAP = {
		',': 188,
		'.': 190
	},
	Handler = {
		input: null,
		delimiter: '.',
		handle: function ( event ) {
			var $element = this.input,
				// @todo Revise keyCode
				// @see https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent
				// @see https://stackoverflow.com/a/41656511
				keyCode = event.keyCode || event.which,
				keysAllowed = [
					// numbers
					48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
					// editing and navigation
					0, 8, 9, 13, 37, 39, 46,
					// Delimiters (regular & numpad)
					DELIMITER_KEYCODE_MAP[ this.delimiter ], 110
				];

			// @todo Shouldn't this test for `|| event.ctrlKey !== false` instead?
			if( !_.contains( keysAllowed, keyCode ) && event.ctrlKey === false ) {
				event.preventDefault();
			}

			if( this.keyCodeIsDelimiter( keyCode ) && this.inputHasDelimiter() ) {
				event.preventDefault();
			}
		},
		keyCodeIsDelimiter: function ( keyCode ) {
			return DELIMITER_KEYCODE_MAP[ this.delimiter] === keyCode  ||
				keyCode === 110; // numpad
		},
		inputHasDelimiter: function () {
			return this.input.val().indexOf( this.delimiter ) > -1;
		}
	}
;

module.exports = {
	Handler: Handler,
	createNumericInputHandler: function ( input, delimiter ) {
		var handler = objectAssign( Object.create( Handler ), {
			input: input,
			delimiter: delimiter
		} );

		input.on( 'keypress', handler.handle.bind( handler ) );
		return handler;
	}
};
