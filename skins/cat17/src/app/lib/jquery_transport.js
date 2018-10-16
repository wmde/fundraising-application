/**
 * Transform the jQuery 2 Deferred object to a Promises/A+ object.
 *
 * Using jQuery 2 Deferred directly will lead to endless loops on failure, because
 * the "fail" function returns the xhrObject, which is itself a Promise,
 * which will then be called by the Redux promise_middleware again and again.
 *
 * When we update to jQuery >= 3.0 or use a different HTTP request library, this wrapper function can be deleted
 *
 * @param {Deferred} jQueryDeferredObject
 * @return {Promise}
 */
function jQueryDeferredToPromise( jQueryDeferredObject ) {
	return new Promise( function ( resolve, reject ) {
		jQueryDeferredObject.then( resolve, function ( xhrObject, statusCode, statusMessage ) {
			reject( statusMessage );
		} );
	} );
}

import jQuery from 'jquery';

/**
 * @implements Transport
 */
export default class JQueryTransport {

	constructor( $ = jQuery ) {
		this.jQuery = $;
	}

	/**
	 * @inheritDoc
	 */
	getData( url, requestData ) {
		return jQueryDeferredToPromise(
			this.jQuery.get(
				url,
				requestData,
				null,
				'json'
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	postData( url, requestData ) {
		return jQueryDeferredToPromise(
			this.jQuery.post(
				url,
				requestData,
				null,
				'json'
			)
		);
	}
}