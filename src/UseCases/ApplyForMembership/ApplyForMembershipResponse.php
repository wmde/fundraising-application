<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipResponse {

	public static function newSuccessResponse( string $accessToken, string $updateToken ): self {
		$response = new self( true );
		$response->accessToken = $accessToken;
		$response->updateToken = $updateToken;
		return $response;
	}

	public static function newFailureResponse(): self {
		return new self( false );
	}

	private $isSuccess;
	private $accessToken;
	private $updateToken;

	private function __construct( bool $isSuccess ) {
		$this->isSuccess = $isSuccess;
	}

	public function isSuccessful(): bool {
		return $this->isSuccess;
	}

	public function getAccessToken(): string {
		if ( !$this->isSuccess ) {
			throw new \RuntimeException( 'The result only has an access token when successful' );
		}

		return $this->accessToken;
	}

	public function getUpdateToken(): string {
		if ( !$this->isSuccess ) {
			throw new \RuntimeException( 'The result only has an update token when successful' );
		}

		return $this->updateToken;
	}

}
