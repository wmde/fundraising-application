<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Clock\Clock;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\DonationContext\Infrastructure\DonationAuthorizationChecker;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\MembershipContext\Authorization\MembershipAuthorizationChecker;

class AuthorizationChecker implements DonationAuthorizationChecker, MembershipAuthorizationChecker {
	private string $updateToken;
	private string $accessToken;
	private SystemClock|Clock $clock;

	public function __construct( private readonly TokenRepository $repository, string $updateToken = '', string $accessToken = '', Clock $clock = null ) {
		$this->updateToken = $updateToken;
		$this->accessToken = $accessToken;
		$this->clock = $clock ?? new SystemClock();
	}

	public function userCanModifyDonation( int $donationId ): bool {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
		if ( $token === null ) {
			return false;
		}
		if ( !hash_equals( $token->updateToken, $this->updateToken ) ) {
			return false;
		}
		return !$token->updateTokenHasExpired( $this->clock->now() );
	}

	public function systemCanModifyDonation( int $donationId ): bool {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
		if ( $token === null ) {
			return false;
		}
		return hash_equals( $token->updateToken, $this->updateToken );
	}

	public function canAccessDonation( int $donationId ): bool {
		$token = $this->repository->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
		if ( $token === null ) {
			return false;
		}
		return hash_equals( $token->accessToken, $this->accessToken );
	}

	public function canModifyMembership( int $membershipId ): bool {
		$token = $this->repository->getTokenById( $membershipId, AuthenticationBoundedContext::Membership );
		if ( $token === null ) {
			return false;
		}
		return hash_equals( $token->updateToken, $this->updateToken );
	}

	public function canAccessMembership( int $membershipId ): bool {
		$token = $this->repository->getTokenById( $membershipId, AuthenticationBoundedContext::Membership );
		if ( $token === null ) {
			return false;
		}
		return hash_equals( $token->accessToken, $this->accessToken );
	}
}
