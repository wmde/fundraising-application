<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface UrlGenerator {

	public function generateAbsoluteUrl( string $name, array $parameters = [] ): string;

	public function generateRelativeUrl( string $name, array $parameters = [] ): string;

}
