import axios, { AxiosResponse } from 'axios';
(function() {
	console.log('bla')
let isTracked: boolean = true;
const trackingUrl: any = document.getElementById( 'privacy_opt_out' )!.dataset!.tracking_url;

function updateFormState(): void {
	if ( isTracked === true ) {
		document.getElementById( 'tracking-opt-in' )!.setAttribute( 'checked', 'true' );
		document.getElementById( '.opted-out' )!.style.display = 'none';
		document.getElementById( '.privacy_selection .selected' )!.classList.remove( 'selected' );
		document.getElementById( '.choice-in' )!.classList.add( 'selected' );
	} else {
		document.getElementById( 'tracking-opt-out' )!.setAttribute( 'checked', 'true' );
		document.getElementById( '.opted-out' )!.style.display = 'block';
		document.getElementById( '.privacy_selection .selected' )!.classList.remove( 'selected' );
		document.getElementById( '.choice-out' )!.classList.add( 'selected' );
	}
}

function enableTracking(): void {
	axios.get( trackingUrl + 'index.php?module=API&method=AjaxOptOut.doTrack&format=json' )
		.then( () => {
			isTracked = true;
			updateFormState();
		} );
}

function disableTracking(): void {
	axios.get( trackingUrl + 'index.php?module=API&method=AjaxOptOut.doIgnore&format=json' )
		.then( () => {
			isTracked = false;
			updateFormState();
		} );
}

document.getElementById( 'tracking-opt-in' )!.onclick = enableTracking;
document.getElementById( 'tracking-opt-out' )!.onclick = disableTracking;
// Initial call to establish the tracking state of the current user
axios.get( trackingUrl + 'index.php?module=API&method=AjaxOptOut.isTracked&format=json' )
	.then( ( res: AxiosResponse ) => {
		isTracked = res.data.value;
		updateFormState();
	} );
})();