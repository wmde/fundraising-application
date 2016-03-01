<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TokenGenerator {

	public function __construct( int $tokenLength, string $validityTimeSpan ) {
		$this->tokenLength = $tokenLength;
		$this->validityTimeSpan = $validityTimeSpan;
	}

	public function generateToken(): string {
		return bin2hex( random_bytes( $this->tokenLength ) );
	}

	public function generateTokenExpiry(): string {
		return ( new \DateTime() )->add( new \DateInterval( $this->validityTimeSpan ) )->format( 'Y-m-d H:i:s' );
	}

}
