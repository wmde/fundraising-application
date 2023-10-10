<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\DonationUrlAuthenticationLoader;
use WMDE\Fundraising\Frontend\Authentication\MembershipUrlAuthenticationLoader;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class AuthenticationLoader implements DonationUrlAuthenticationLoader, MembershipUrlAuthenticationLoader {
	public function __construct(
		private readonly TokenRepository $repository
	) {
	}

	public function getDonationUrlAuthenticator( int $donationId ): URLAuthenticator {
		$token = $this->getTokenOrFail( $donationId, AuthenticationBoundedContext::Donation );
		return new AccessTokenUrlAuthenticator( $token );
	}

	public function getMembershipUrlAuthenticator( int $membershipId ): URLAuthenticator {
		$token = $this->getTokenOrFail( $membershipId, AuthenticationBoundedContext::Membership );
		return new AccessTokenUrlAuthenticator( $token );
	}

	public function addDonationAuthorizationParameters( int $donationId, array $parameters ): array {
		$token = $this->getTokenOrFail( $donationId, AuthenticationBoundedContext::Donation );
		return [
			...$parameters,
			'accessToken' => $token->getAccessToken(),
			'updateToken' => $token->getUpdateToken()
		];
	}

	public function addMembershipAuthorizationParameters( int $membershipId, array $parameters ): array {
		$token = $this->getTokenOrFail( $membershipId, AuthenticationBoundedContext::Membership );
		return [
			...$parameters,
			'accessToken' => $token->getAccessToken(),
			'updateToken' => $token->getUpdateToken()
		];
	}

	private function getTokenOrFail( int $id, AuthenticationBoundedContext $context ): AuthenticationToken {
		$token = $this->repository->getTokenById( $id, $context );
		if ( $token instanceof NullToken ) {
			return throw new \LogicException(
				'No token found for ID ' . $id . ' and context ' . $context->name .
				' This should never happen, you forgot to call authorizeDonationAccess or authorizeMembershipAccess somewhere'
			);
		}
		return $token;
	}
}
