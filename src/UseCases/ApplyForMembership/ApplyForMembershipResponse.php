<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipResponse {

	public static function newSuccessResponse( string $accessToken, string $updateToken,
		MembershipApplication $application ): self {

		$response = new self( true );
		$response->accessToken = $accessToken;
		$response->updateToken = $updateToken;
		$response->application = $application;
		return $response;
	}

	public static function newFailureResponse(): self {
		return new self( false );
	}

	private $isSuccess;

	private $accessToken;
	private $updateToken;
	private $application;

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

	/**
	 * WARNING: we're returning the domain object to not have to create a  more verbose response model.
	 * Keep in mind that you should not use domain logic in the presenter, or put presentation helpers
	 * in the domain object!
	 *
	 * @return MembershipApplication
	 */
	public function getMembershipApplication(): MembershipApplication {
		if ( !$this->isSuccess ) {
			throw new \RuntimeException( 'The result only has a membership application object when successful' );
		}

		return $this->application;
	}

}
