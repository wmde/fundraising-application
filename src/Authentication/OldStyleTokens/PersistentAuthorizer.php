<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use Psr\Log\LoggerInterface;
use WMDE\Clock\Clock;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\TokenGenerator;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizer;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

/**
 * Creates new tokens for donations and memberships and stores them in the database.
 *
 * Since the combination of ID and context is unique, we will guard against duplicate ID exceptions
 * by trying to load them first. If they exist, we will log a warning and use the existing token.
 * Existing tokens would hint at the fact that the authorize methods were called multiple times,
 * but we want to allow that for now. If warnings crop up in the logs, we can investigate further.
 */
class PersistentAuthorizer implements DonationAuthorizer, MembershipAuthorizer {
	private Clock $clock;

	public function __construct(
		private readonly TokenRepository $repository,
		private readonly TokenGenerator $tokenGenerator,
		private readonly LoggerInterface $logger,
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
		} else {
			$this->logger->warning( 'Found existing token for donation. This should not happen.', [ 'donation_id' => $donationId ] );
		}

		return new AccessTokenUrlAuthenticator( $token );
	}

	public function authorizeMembershipAccess( int $membershipId ): URLAuthenticator {
		$token = $this->repository->getTokenById( $membershipId, AuthenticationBoundedContext::Membership );
		if ( $token instanceof NullToken ) {
			$token = $this->createAndStoreNewToken( $membershipId, AuthenticationBoundedContext::Membership );
		} else {
			$this->logger->warning( 'Found existing token for membership. This should not happen.', [ 'membership_id' => $membershipId ] );
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
