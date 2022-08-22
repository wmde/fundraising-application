<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use WMDE\Fundraising\DonationContext\Authorization\TokenGenerator;

/**
 * TODO: move into Donation BC
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FixedTokenGenerator implements TokenGenerator {

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
