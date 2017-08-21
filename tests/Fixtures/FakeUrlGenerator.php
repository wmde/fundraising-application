<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeUrlGenerator implements UrlGenerator {

	public function generateUrl( string $name, array $parameters = [] ): string {
		return 'https://such.a.url/' . $name . '?' . http_build_query( $parameters );
	}

}
