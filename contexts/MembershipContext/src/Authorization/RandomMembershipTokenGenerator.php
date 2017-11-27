<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class RandomMembershipTokenGenerator implements MembershipTokenGenerator {

	private $tokenLength;
	private $validityTimeSpan;

	public function __construct( int $tokenLength, \DateInterval $validityTimeSpan ) {
		$this->tokenLength = $tokenLength;
		$this->validityTimeSpan = $validityTimeSpan;
	}

	public function generateToken(): string {
		return bin2hex( random_bytes( $this->tokenLength ) );
	}

	public function generateTokenExpiry(): \DateTime {
		return ( new \DateTime() )->add( $this->validityTimeSpan );
	}

}
