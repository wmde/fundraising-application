<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class RandomTokenGenerator implements TokenGenerator {

	private $tokenLength;
	private $validityTimeSpan;

	public function __construct( int $tokenLength, string $validityTimeSpan ) {
		$this->tokenLength = $tokenLength;
		$this->validityTimeSpan = $validityTimeSpan;
	}

	public function generateToken(): string {
		return bin2hex( random_bytes( $this->tokenLength ) );
	}

	public function generateTokenExpiry(): string {
		// TODO: change to return DateTime (the formatting here is a detail of how the stuff is persisted)
		return ( new \DateTime() )->add( new \DateInterval( $this->validityTimeSpan ) )->format( 'Y-m-d H:i:s' );
	}

}
