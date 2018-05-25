(function( $ ) {
	$( document ).ready( function() {
		initSupportersTable();
		attachEvents();
	} );

	function initSupportersTable() {
		$( '.donors tbody tr' ).each( function() {
			if( getComment( this ) ) {
				$( this ).addClass( 'commented' );
			}
		} );
	}

	function attachEvents() {
		$( '.commented' ).click( function() {
			if( $( this ).hasClass( 'active' ) ) {
				hideComment( $( this ) );
			} else {
				showComment( $( this ) );
			}
		} );
	}

	function getComment( tableRow ) {
		return $( tableRow ).find( ':nth-child(3)' ).html();
	}

	function showComment( tableRow ) {
		$( '.commented' ).removeClass( 'active' );
		tableRow.addClass( 'active' );
	}

	function hideComment( tableRow ) {
		tableRow.removeClass( 'active' );
	}
})( jQuery );
