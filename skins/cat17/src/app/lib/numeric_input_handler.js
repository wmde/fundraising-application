'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	DELIMITER_KEYCODE_MAP = {
		',': 44,
		'.': 46
	},
	Handler = {
		input: null,
		delimiter: '.',
		handle: function ( event ) {
			var // @todo Revise keyCode when support for .key is more wide spread
				// @see https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent
				// @see https://stackoverflow.com/a/41656511
				keyCode = event.keyCode || event.which,
				keysAllowed = [
					// numbers
					48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
					// editing and navigation
					0, 8, 9, 13, 37, 39,
					// Delimiter
					DELIMITER_KEYCODE_MAP[ this.delimiter ]
				];

			// @todo Shouldn't this test for `|| event.ctrlKey !== false` instead?
			if ( !_.contains( keysAllowed, keyCode ) && event.ctrlKey === false ) {
				event.preventDefault();
			}

			if ( this.keyCodeIsDelimiter( keyCode ) && this.inputHasDelimiter() ) {
				event.preventDefault();
			}
		},
		keyCodeIsDelimiter: function ( keyCode ) {
			return DELIMITER_KEYCODE_MAP[ this.delimiter ] === keyCode;
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
