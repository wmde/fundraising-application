<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

/**
 * @licence GNU GPL v2+
 */
class ChecksumGenerator {

	private $checksumCharacters;

	/**
	 * @param array $checksumCharacters Characters that can be used for the checksum
	 */
	public function __construct( array $checksumCharacters ) {
		if ( count( $checksumCharacters ) < 2 ) {
			throw new \InvalidArgumentException(
				'Need at least two characters to create meaningful checksum'
			);
		}

		$this->checksumCharacters = $checksumCharacters;
	}

	/**
	 * @param string $string The string to create a checksum for
	 *
	 * @return string The checksum as a single character present in the constructors array argument
	 */
	public function createChecksum( string $string ): string {
		$checksum = array_sum(
			array_map(
				'ord', // Returns the ASCII value of the character
				str_split( md5( $string ) )
			)
		);

		return $this->checksumCharacters[$checksum % count( $this->checksumCharacters )];
	}

}