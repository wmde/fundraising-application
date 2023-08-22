<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;

interface TokenRepository {
	public function storeToken( AuthenticationToken $token ): void;

	public function getTokenById( int $id, AuthenticationBoundedContext $authenticationDomain ): ?AuthenticationToken;
}
