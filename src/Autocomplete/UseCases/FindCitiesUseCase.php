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
		$alphanumericPostCode = preg_replace( '/[^0-9]/', '', trim( $postcode ) );
		return $this->locationRepository->getCitiesForPostcode( strval( $alphanumericPostCode ) );
	}
}
