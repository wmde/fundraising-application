<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FixedTokenGenerator implements TokenGenerator {

	private $token;
	private $expiry;

	public function __construct( string $token, \DateTime $expiry ) {
		$this->token = $token;
		$this->expiry = $expiry;
	}

	public function generateToken(): string {
		return $this->token;
	}

	public function generateTokenExpiry(): string {
		return $this->expiry;
	}

}