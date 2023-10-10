<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\TokenRepository;

class InMemoryTokenRepository implements TokenRepository {
	/**
	 * @var array<string,AuthenticationToken>
	 */
	private array $tokens = [];

	public function __construct( AuthenticationToken ...$tokens ) {
		foreach ( $tokens as $token ) {
			$this->storeToken( $token );
		}
	}

	public function storeToken( AuthenticationToken $token ): void {
		$this->tokens[$token->id . $token->authenticationBoundedContext->value] = $token;
	}

	public function getTokenById( int $id, AuthenticationBoundedContext $authenticationDomain ): AuthenticationToken {
		return $this->tokens[$id . $authenticationDomain->value] ?? new NullToken( $id, $authenticationDomain );
	}
}
