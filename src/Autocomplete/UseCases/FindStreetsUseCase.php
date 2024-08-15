<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete\UseCases;

use WMDE\Fundraising\Frontend\Autocomplete\Domain\LocationRepository;

class FindStreetsUseCase {

	public function __construct( private readonly LocationRepository $locationRepository ) {
	}

	/**
	 * @return string[]
	 */
	public function getStreetsForPostcode( string $postcode ): array {
		$alphanumericPostCode = preg_replace( '/[^0-9]/', '', trim( $postcode ) ) ?? '';
		return $this->locationRepository->getStreetsForPostcode( $alphanumericPostCode );
	}
}
