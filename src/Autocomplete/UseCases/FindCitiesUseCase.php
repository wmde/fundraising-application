<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\UseCases;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\LocationRepository;

class FindCitiesUseCase {

	private LocationRepository $locationRepository;

	public function __construct( LocationRepository $locationRepository ) {
		$this->locationRepository = $locationRepository;
	}

	/**
	 * @param string $postcode
	 *
	 * @return string[]
	 */
	public function getCitiesForPostcode( string $postcode ): array {
		return $this->locationRepository->getCitiesForPostcode( $postcode );
	}
}
