<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Clock\Clock;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\TokenGenerator;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizer;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class PersistentAuthorizer implements DonationAuthorizer, MembershipAuthorizer {
	private Clock $clock;

	public function __construct(
		private readonly TokenRepository $repository,
		private readonly TokenGenerator $tokenGenerator,
		private readonly \DateInterval $updateTokenExpiry,
		Clock $clock = null
	) {
		if ( $clock === null ) {
			$this->clock = new SystemClock();
		}
	}

	public function authorizeDonationAccess( int $donationId ): URLAuthenticator {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
		if ( $token instanceof NullToken ) {
			$token = $this->createAndStoreNewToken( $donationId, AuthenticationBoundedContext::Donation );
		}

		return new AccessTokenUrlAuthenticator( $token );
	}

	public function authorizeMembershipAccess( int $membershipId ): URLAuthenticator {
		$token = $this->repository->getTokenById( $membershipId, AuthenticationBoundedContext::Membership );
		if ( $token instanceof NullToken ) {
			$token = $this->createAndStoreNewToken( $membershipId, AuthenticationBoundedContext::Membership );
		}

		return new AccessTokenUrlAuthenticator( $token );
	}

	private function createAndStoreNewToken( int $id, AuthenticationBoundedContext $context ): AuthenticationToken {
		$accessToken = $this->tokenGenerator->generateToken();
		$updateToken = $this->tokenGenerator->generateToken();
		$updateTokenExpiry = $this->clock->now()->add( $this->updateTokenExpiry );
		$token = new AuthenticationToken(
			$id,
			$context,
			$accessToken->__toString(),
			$updateToken->__toString(),
			$updateTokenExpiry
		);
		$this->repository->storeToken( $token );
		return $token;
	}
}
