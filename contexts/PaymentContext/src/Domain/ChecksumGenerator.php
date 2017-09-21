<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain;

/**
 * @licence GNU GPL v2+
 */
class ChecksumGenerator {

	private $checksumCharacters;

	private $cleanupPattern;

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
		$this->cleanupPattern = '/[^' . implode( '', $checksumCharacters ) . ']/';
	}

	/**
	 * @param string $string The string to create a checksum for
	 *
	 * @return string The checksum as a single character present in the constructors array argument
	 */
	public function createChecksum( string $string ): string {
		$checksum = hexdec( substr( md5( $this->normalizeString( $string ) ), 0, 8 ) );

		return $this->checksumCharacters[$checksum % count( $this->checksumCharacters )];
	}

	private function normalizeString( string $string ): string {
		return strtoupper( preg_replace( $this->cleanupPattern, '', $string ) );
	}

}