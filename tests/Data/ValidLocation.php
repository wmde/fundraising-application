<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;

class ValidLocation {

	public static function validLocationForPostcodeAndCity( string $postcode, string $city ): Location {
		return new Location(
			'Baden-Württemberg',
			'DE1',
			'Freiburg',
			'DE13',
			'Aach',
			'Landkreis',
			'DE138',
			'Aach',
			'Stadt',
			'08335001',
			'083355001001',
			47.84279,
			8.85104,
			'1',
			$city,
			47.84277,
			8.85111,
			$postcode
		);
	}
}
