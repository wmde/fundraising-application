<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

/**
 * The Authentication Token returned for error conditions:
 *
 * - Token not found
 * - Legacy data not found
 *
 * @codeCoverageIgnore
 */
class NullToken extends AuthenticationToken {
	private string $errorMessage;

	public function __construct( int $id, AuthenticationBoundedContext $context, ?string $errorMessage = null ) {
		parent::__construct( $id, $context, '', '' );
		$this->errorMessage = $errorMessage ?? self::getErrorMessage( $id, $context );
	}

	public function getAccessToken(): string {
		throw new \LogicException( $this->errorMessage );
	}

	public function getUpdateToken(): string {
		throw new \LogicException( $this->errorMessage );
	}

	public function updateTokenHasExpired( \DateTimeImmutable $now ): bool {
		return true;
	}

	public function updateTokenMatches( string $updateToken ): bool {
		return false;
	}

	public function accessTokenMatches( string $accessToken ): bool {
		return false;
	}

	public static function getErrorMessage( int $id, AuthenticationBoundedContext $context ): string {
		return sprintf(
			'No token found for ID %d and context %s This should never happen, you forgot to call authorizeDonationAccess or authorizeMembershipAccess somewhere',
			$id,
			$context->name
		);
	}

}
