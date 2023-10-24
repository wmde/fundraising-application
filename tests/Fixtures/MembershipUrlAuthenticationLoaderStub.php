<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Authentication\MembershipUrlAuthenticationLoader;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class MembershipUrlAuthenticationLoaderStub implements MembershipUrlAuthenticationLoader {

	public function getMembershipUrlAuthenticator( int $membershipId ): URLAuthenticator {
		return new UrlAuthenticatorStub();
	}

	public function addMembershipAuthorizationParameters( int $membershipId, array $parameters ): array {
		return $parameters;
	}
}
