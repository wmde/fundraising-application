<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

class AuthenticationToken {
	private ?\DateTimeImmutable $updateTokenTokenExpiry;

	public function __construct(
		public readonly int $id,
		public readonly AuthenticationBoundedContext $authenticationBoundedContext,
		public readonly string $accessToken,
		public readonly string $updateToken,
		?\DateTimeImmutable $updateTokenTokenExpiry = null
	) {
		$this->updateTokenTokenExpiry = $updateTokenTokenExpiry;
	}

	public function updateTokenHasExpired( \DateTimeImmutable $now ): bool {
		return $this->updateTokenTokenExpiry && $this->updateTokenTokenExpiry < $now;
	}
}
