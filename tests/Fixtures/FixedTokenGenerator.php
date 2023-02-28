<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\DonationContext\Authorization\TokenGenerator;

class FixedTokenGenerator implements TokenGenerator {
	private \DateTime $expiry;

	public function __construct( private readonly string $token, ?\DateTime $expiry = null ) {
		$this->expiry = $expiry === null ? new \DateTime() : $expiry;
	}

	public function generateToken(): string {
		return $this->token;
	}

	public function generateTokenExpiry(): \DateTime {
		return $this->expiry;
	}
}
