<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

class Salutations {

	/**
	 * @var array
	 */
	private array $salutations;

	public function __construct( array $salutations ) {
		$this->salutations = $salutations;
	}

	public function getList(): array {
		return $this->salutations;
	}

	public function getSalutation( string $value ): ?array {
		$data = array_filter( $this->salutations, fn ( $salutation ) => $salutation['value'] == $value );

		if ( count( $data ) === 0 ) {
			return null;
		}

		return array_values( $data )[0];
	}

}
