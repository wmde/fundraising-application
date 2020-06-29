<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Convert route names to URLs
 *
 * @license GPL-2.0-or-later
 */
interface UrlGenerator {

	public function generateAbsoluteUrl( string $routeName, array $parameters = [] ): string;

	public function generateRelativeUrl( string $routeName, array $parameters = [] ): string;

}
