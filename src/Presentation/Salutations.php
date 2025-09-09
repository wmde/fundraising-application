<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

class Salutations {

	/**
	 * @param array<array<string, string|array<string, string>>> $salutations
	 */
	public function __construct( private readonly array $salutations ) {
	}

	/**
	 * @return array<array<string, string|array<string, string>>>
	 */
	public function getList(): array {
		return $this->salutations;
	}

	/**
	 * @return array<string, string|array<string, string>>|null
	 */
	public function getSalutation( string $value ): ?array {
		$data = array_filter( $this->salutations, static fn ( $salutation ) => $salutation['value'] == $value );

		if ( count( $data ) === 0 ) {
			return null;
		}

		return reset( $data );
	}

}
