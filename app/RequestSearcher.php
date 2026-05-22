<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use Symfony\Component\HttpFoundation\Request;

/**
 * Symfony removed the request->get() method that will check a request
 * for attribute, query, request in that order in version 8.
 *
 * We require this functionality when setting form defaults from both
 * POST or GET requests so this mimics the removed symfony method.
 */
class RequestSearcher {

	public static function get( Request $request, string $key, bool|float|int|null|string $default = null ): bool|float|int|null|string {
		if ( $request->attributes->has( $key ) ) {
			return $request->attributes->get( $key );
		}

		if ( $request->query->has( $key ) ) {
			return $request->query->get( $key );
		}

		return $request->request->get( $key, $default );
	}
}
