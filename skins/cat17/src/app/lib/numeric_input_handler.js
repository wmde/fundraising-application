'use strict';

module.exports = {
	attach: function ( input ) {

		input.on( 'keypress', function ( event ) {
			var keyCode = event.keyCode || event.which,
				keysAllowed = [ 44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8, 9, 13 ];

			if ( $.inArray( keyCode, keysAllowed ) === -1 && event.ctrlKey === false ) {
				event.preventDefault();
			}

			if ( ( keyCode === 44 || keyCode === 46 ) && input.val().indexOf( '.' ) > 0 ) {
				event.preventDefault();
			}

			if ( keyCode === 44 ) {
				setTimeout(
					function () {
						input.val(
							input.val().replace( ',', '.' )
						);
					}, 10
				);
			}
		} );

	}
};
