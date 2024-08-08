<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\Domain;

interface LocationRepository {

	/**
	 * @param string $postcode
	 *
	 * @return string[]
	 */
	public function getCitiesForPostcode( string $postcode ): array;

	/**
	 * @param string $postcode
	 *
	 * @return string[]
	 */
	public function getStreetsForPostcode( string $postcode ): array;
}
