<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Convert route names to URLs
 *
 * @licence GNU GPL v2+
 */
interface UrlGenerator {

	public function generateAbsoluteUrl( string $routeName, array $parameters = [] ): string;

	public function generateRelativeUrl( string $routeName, array $parameters = [] ): string;

}
