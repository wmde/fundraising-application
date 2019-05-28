function toggleComment( supporterDiv: Element ) {
	let heading : HTMLElement = supporterDiv.getElementsByTagName( 'h3' )[ 0 ];
	let comment : HTMLElement = supporterDiv.getElementsByTagName( 'div' )[ 1 ];
	if ( comment.style.display === 'none' ) {
		comment.style.display = '';
		supporterDiv.classList.add( 'accordion' );
		heading.classList.add( 'has-text-primary', 'has-text-weight-bold' );
		heading.classList.remove( 'accordion-heading' );
	} else {
		comment.style.display = 'none';
		supporterDiv.classList.remove( 'accordion' );
		heading.classList.add( 'accordion-heading' );
		heading.classList.remove( 'has-text-primary', 'has-text-weight-bold' );
	}
	// TODO needs distinction if a comment exists or not?
}

function createNewAccordionElements() {

	let tableRows : HTMLCollection = document.getElementsByTagName( 'tr' )!;

	// TODO clean up code and make it more readable (put stuff in functions...) e.g. initSupportersTable()

	for ( let tableRow of tableRows ) {

		// merge donor name cell and donation amount cell to one new div
		let newHeading : HTMLElement = document.createElement( 'div' );
		let newHeadingH : HTMLElement = document.createElement( 'h3' );
		newHeadingH.classList.add( 'accordion-heading' );

		let newHeadingText: string = '';
		newHeadingText += tableRow.getElementsByTagName( 'td' )[ 0 ].textContent;
		newHeadingText += ', ';
		newHeadingText += tableRow.getElementsByTagName( 'td' )[ 1 ].textContent;

		newHeadingH.appendChild( document.createTextNode( newHeadingText ) );
		newHeading.appendChild( newHeadingH );

		tableRow.appendChild( newHeading );

		// create a new div for the comment (might be empty)
		let newComment : HTMLElement = document.createElement( 'div' );
		let newCommentP : HTMLElement = document.createElement( 'p' );
		newComment.classList.add( 'accordion-content' );
		let newCommentText: string = '';
		newCommentText += tableRow.getElementsByTagName( 'td' )[ 2 ].textContent;
		newCommentP.appendChild( document.createTextNode( newCommentText ) );
		newComment.appendChild( newCommentP );
		newComment.style.display = 'none';

		tableRow.appendChild( newComment );

		// remove old td elements from this row
		let tds : HTMLCollection = tableRow.getElementsByTagName( 'td' );
		while ( tds[ 0 ] ) {
			for ( let td of tds ) {
				tableRow.removeChild( td );
			}
		}

		// rename tag to div
		let newSupporterDiv = document.createElement( 'div' );
		newSupporterDiv.classList.add( 'supporter' );
		newSupporterDiv.innerHTML = tableRow.innerHTML;

		tableRow.parentNode!.parentNode!.appendChild( newSupporterDiv );
	}

}

function removeTableHeading() {
	let bodyEl : Element = document.getElementsByClassName( 'donors' )[ 0 ]!;
	let tableHead : HTMLElement = document.getElementsByTagName( 'thead' )[ 0 ];
	bodyEl.removeChild( tableHead );
}

function renameTableToDiv() {

	// this automatically removes tbody too

	let bodyEl : Element = document.getElementsByClassName( 'donors' )[ 0 ]!;

	let tableBody : Element = document.getElementsByTagName( 'tbody' )[ 0 ];
	tableBody.parentNode!.removeChild( tableBody );

	let newSupportersListDiv = document.createElement( 'div' );
	newSupportersListDiv.classList.add( 'supporters', 'donors' );
	newSupportersListDiv.innerHTML = bodyEl.innerHTML;
	bodyEl.parentNode!.replaceChild( newSupportersListDiv, bodyEl );
}

function addToggleFunction() {
	let supporterDivs : HTMLCollection = document.getElementsByClassName( 'supporter' );
	for ( let sDiv of supporterDivs ) {
		sDiv.addEventListener( 'click', () => toggleComment( sDiv ) );
	}
}

window.onload = () => {

	removeTableHeading();

	createNewAccordionElements();

	renameTableToDiv();

	addToggleFunction();

};

// TODO maybe write function to generically unwrap htmlElement children from all the needles table/div elements
// https://plainjs.com/javascript/manipulation/unwrap-a-dom-element-35/
