(function( $ ) {
	$( document ).ready( function() {
		attachEvents();
	} );

	function attachEvents() {
		$( '#contact-form input, #contact-form textarea' ).focusout( function() {
			if ( $( this ).val() ) {
				$( this ).addClass( 'filled' );
			}
			else {
				$( this ).removeClass( 'filled' );
			}
		} );
	}

})( jQuery );
