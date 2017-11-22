'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	Handler = {
		input: null,
		handle: function ( event ) {
			var $element = this.input,
				// @todo Revise keyCode
				// @see https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent
				// @see https://stackoverflow.com/a/41656511
				keyCode = event.keyCode || event.which,
				keysAllowed = [44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8, 9, 13];

			// @todo Shouldn't this test for `|| event.ctrlKey !== false` instead?
			if( !_.contains( keysAllowed, keyCode ) && event.ctrlKey === false ) {
				event.preventDefault();
			}

			// @todo Inject delimiter (depending on locale)
			if( ( keyCode === 44 || keyCode === 46 ) && $element.val().indexOf( '.' ) > 0 ) {
				event.preventDefault();
			}

			if( keyCode === 44 ) {
				// @todo There must be a more elegant way
				setTimeout(
					function () {
						$element.val(
							$element.val().replace( ',', '.' )
						);
					}, 10
				);
			}
		}
	}
;

module.exports = {
	Handler: Handler,
	attach: function ( input ) {
		var handler = objectAssign( Object.create( Handler ), {
			input: input
		} );

		input.on( 'keypress', handler.handle.bind( handler ) );
	}
};
