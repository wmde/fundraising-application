<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

class PostalLocalityJsonPresenter {

	private array $postalLocalities;
	private string $postcodePattern = '/^[0-9]{5}$/';

	public function __construct( array $postalLocalities ) {
		$this->postalLocalities = $postalLocalities;
	}

	public function present( string $postcode ): array {
		if( !$this->isValidPostcode( $postcode ) ) {
			return [];
		}

		$filteredPostalLocalities = array_filter( $this->postalLocalities, function ( $entry ) use ( $postcode ) {
			return $entry->postcode === $postcode;
		} );

		$filteredPostalLocalities = array_map( function ( $entry ) {
			return $entry->locality;
		}, $filteredPostalLocalities );

		$filteredResults = array_unique( $filteredPostalLocalities );

		sort( $filteredResults );

		return  $filteredResults;
	}

	private function isValidPostcode ( string $postcode ): bool {
		if ( $postcode === '' ) {
			return false;
		}
		if ( !preg_match( $this->postcodePattern, $postcode ) ) {
			return false;
		}
		return true;
	}

}