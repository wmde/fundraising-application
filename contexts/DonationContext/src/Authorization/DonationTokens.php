<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Authorization;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTokens {

	private $accessToken;
	private $updateToken;

	public function __construct( string $accessToken, string $updateToken ) {
		if ( $accessToken === '' ) {
			throw new \InvalidArgumentException( 'Access token cannot be empty' );
		}

		if ( $updateToken === '' ) {
			throw new \InvalidArgumentException( 'Update token cannot be empty' );
		}

		$this->accessToken = $accessToken;
		$this->updateToken = $updateToken;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function getUpdateToken(): string {
		return $this->updateToken;
	}

}
