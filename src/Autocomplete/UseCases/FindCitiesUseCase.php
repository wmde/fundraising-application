<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\UseCases;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\LocationRepository;

class FindCitiesUseCase {

	public function __construct( private readonly LocationRepository $locationRepository ) {
	}

	/**
	 * @return string[]
	 */
	public function getCitiesForPostcode( string $postcode ): array {
		$alphanumericPostCode = preg_replace( '/[^0-9]/', '', trim( $postcode ) ) ?? '';
		return $this->locationRepository->getCitiesForPostcode( $alphanumericPostCode );
	}
}
