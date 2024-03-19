<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

class AuthenticationToken {

	public function __construct(
		public readonly int $id,
		public readonly AuthenticationBoundedContext $authenticationBoundedContext,
		private readonly string $accessToken,
		private readonly string $updateToken,
		private readonly ?\DateTimeImmutable $updateTokenTokenExpiry = null
	) {
	}

	public function updateTokenHasExpired( \DateTimeImmutable $now ): bool {
		return $this->updateTokenTokenExpiry && $this->updateTokenTokenExpiry < $now;
	}

	public function getAccessToken(): string {
		return $this->accessToken;
	}

	public function getUpdateToken(): string {
		return $this->updateToken;
	}

	public function updateTokenMatches( string $updateToken ): bool {
		return hash_equals( $this->updateToken, $updateToken );
	}

	public function accessTokenMatches( string $accessToken ): bool {
		return hash_equals( $this->accessToken, $accessToken );
	}
}
