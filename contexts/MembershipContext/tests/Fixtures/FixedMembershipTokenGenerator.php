<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Fixtures;

use WMDE\Fundraising\Frontend\MembershipContext\Authorization\MembershipTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FixedMembershipTokenGenerator implements MembershipTokenGenerator {

	private $token;
	private $expiry;

	public function __construct( string $token, \DateTime $expiry = null ) {
		$this->token = $token;
		$this->expiry = $expiry === null ? new \DateTime() : $expiry;
	}

	public function generateToken(): string {
		return $this->token;
	}

	public function generateTokenExpiry(): \DateTime {
		return $this->expiry;
	}

}