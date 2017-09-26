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
		$checksum = substr( md5( $this->normalizeString( $string ) ), 0, 8 );
		if(PHP_INT_SIZE > 4) {
			return $this->checksumCharacters[hexdec( $checksum ) % count( $this->checksumCharacters )];
		} else {
			return $this->checksumCharacters[(int) fmod(
				(float) base_convert( $checksum, 16, 10 ),
				count( $this->checksumCharacters )
			)];
		}

	}

	private function normalizeString( string $string ): string {
		return strtoupper( str_replace( [ '-', '_', ' ' ], [ '', '', '' ], $string ) );
	}

}