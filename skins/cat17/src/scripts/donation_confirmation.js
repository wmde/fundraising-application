$( function () {
	// code from https://stackoverflow.com/a/9748733/130121
	$( '.cancel-link' ).click( function ( evt ) {
		var p = $(this).attr( 'href' ).split( '?' );
		var action = p[0];
		var params = p[1].split('&');
		var form = $( document.createElement( 'form' ) ).attr( 'action', action ).attr( 'method','post' );
		$('body').append( form );
		for (var i = 0; i < params.length; i++ ) {
			var tmp = params[ i ].split( '=' );
			var key = tmp[ 0 ], value = tmp[ 1 ];
			$( document.createElement( 'input' ) ).attr( 'type', 'hidden' ).attr( 'name', key ).attr( 'value', value ).appendTo( form );
		}
		form.submit();
		evt.preventDefault();

	} );

	// TODO Show SEPA-Mandat

	// TODO Show comment form
} );