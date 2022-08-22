<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeUrlGenerator implements UrlGenerator {

	public function generateRelativeUrl( string $routeName, array $parameters = [] ): string {
		return '/such.a.url/' . $routeName . '?' . http_build_query( $parameters );
	}

	public function generateAbsoluteUrl( string $routeName, array $parameters = [] ): string {
		return 'https://such.a.url/' . $routeName . '?' . http_build_query( $parameters );
	}

}
