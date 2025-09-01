var matomoSettings = document.getElementById('matomo-loader');
var initialPaymentType = matomoSettings.dataset.initialPaymentType || '';
var siteId = matomoSettings.dataset.siteId;

var _paq = _paq || [];
_paq.push(['requireCookieConsent']);
_paq.push( ['setCustomDimension', 2, 'laika' ] );
_paq.push( [ 'trackPageView' ] );
_paq.push( [ 'enableLinkTracking' ] );
_paq.push( [ 'trackVisibleContentImpressions' ] );
if ( initialPaymentType ) {
	_paq.push( [ 'setCustomDimension', 1, initialPaymentType ] );
}

( function () {
	var u = matomoSettings.dataset.matomoBaseUrl;
	_paq.push( [ 'setTrackerUrl', u + 'piwik.php' ] );
	_paq.push( [ 'setSiteId', siteId ] );
	var d = document,
		g = d.createElement( 'script' ),
		s = d.getElementsByTagName( 'script' )[ 0 ];
	g.type = 'text/javascript';
	g.async = true;
	g.defer = true;
	g.src = u + 'piwik.js';
	s.parentNode.insertBefore( g, s );
} ) ();
