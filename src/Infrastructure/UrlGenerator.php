<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * Convert route names to URLs
 */
interface UrlGenerator {

	/**
	 * @param string $routeName
	 * @param array<string, string> $parameters
	 */
	public function generateAbsoluteUrl( string $routeName, array $parameters = [] ): string;

	/**
	 * @param string $routeName
	 * @param array<string, scalar> $parameters
	 */
	public function generateRelativeUrl( string $routeName, array $parameters = [] ): string;

}
