/*
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
		return $( tableRow ).find( ':nth-child(3)' ).html(); //findet das 3. Element in der Reihe (also 3. Spalte (Kommentar))
	}

	function showComment( tableRow ) {
		$( '.commented' ).removeClass( 'active' );
		tableRow.addClass( 'active' );
	}

	function hideComment( tableRow ) {
		tableRow.removeClass( 'active' );
	}
})( jQuery );

*/

window.onload = () => {

	// remove table heading
	let bodyEl : Element = document.getElementsByClassName('donors')[0]!;
	let tableHead : HTMLElement = document.getElementsByTagName( 'thead' )[ 0 ];
	bodyEl.removeChild(tableHead);



	let tableRows : HTMLCollection | null = document.getElementsByTagName( 'tr' )!;


	for ( let tableRow of tableRows ) {
		//tableRow.classList.add( 'accordion' );


		// merge donor name cell and donation amount cell to one new div
		let newHeading : HTMLElement = document.createElement( 'div' );
		let newHeadingH : HTMLElement = document.createElement('h3');
		newHeadingH.classList.add( 'accordion-heading' );

		let newHeadingText: string = '';
		newHeadingText += tableRow.getElementsByTagName( 'td' )[ 0 ].textContent;
		newHeadingText += ', ';
		newHeadingText += tableRow.getElementsByTagName( 'td' )[ 1 ].textContent;

		newHeadingH.appendChild( document.createTextNode( newHeadingText ) );
		newHeading.appendChild( newHeadingH );
		//newHeading.addEventListener("click", toggleComment(tableRow));

		tableRow.appendChild( newHeading );


		//create a new div for the comment (might be empty)
		let newComment : HTMLElement = document.createElement('div');
		let newCommentP : HTMLElement = document.createElement('p');
		newComment.classList.add('accordion-content');
		let newCommentText: string = '';
		newCommentText += tableRow.getElementsByTagName( 'td' )[ 2 ].textContent;
		newCommentP.appendChild(document.createTextNode(newCommentText));
		newComment.appendChild( newCommentP );

		tableRow.appendChild( newComment );


		//remove old td elements from this row
		let tds : HTMLCollection = tableRow.getElementsByTagName( 'td' );
		while (tds[0]) {
			for ( let td of tds ) {
				tableRow.removeChild(td);
			}
		}

		// unterscheidung machen, ob ein Kommentar existiert, oer nicht
	}


	function toggleComment(tableRow: Element) {
		let comment : HTMLElement = tableRow.getElementsByTagName( 'div' )[ 1 ];
		comment.style.display = 'none';
		//style von der tableRow auch ändern
	};



	// clickevent zu tr(1,2) hinzufügen

	// tr(3) toggeln (active/inactive)
};


