<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\Domain;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\Model\Location;

interface LocationRepository {

	/**
	 * @param string $postcode
	 *
	 * @return string[]
	 */
	public function getCitiesForPostcode( string $postcode ): array;
}
