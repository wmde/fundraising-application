<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;

class ValidLocation {

	public static function validLocationForCommunity( string $postcode, string $community ): Location {
		return new Location(
			'Baden-Württemberg',
			'DE1',
			'Freiburg',
			'DE13',
			'Aach',
			'Landkreis',
			'DE138',
			$community,
			'Stadt',
			'08335001',
			'083355001001',
			47.84279,
			8.85104,
			'1',
			'Aach',
			47.84277,
			8.85111,
			$postcode
		);
	}
}
