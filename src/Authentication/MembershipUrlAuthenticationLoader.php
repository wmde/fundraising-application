<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

/**
 * This is for getting an UrlAuthenticator for a membership when redirecting
 */
interface MembershipUrlAuthenticationLoader {
	public function getMembershipUrlAuthenticator( int $membershipId ): URLAuthenticator;

	/**
	 * This is for adding authorization parameters to POST requests, where they must not be in the URL
	 * @param int $membershipId
	 * @param array<string,mixed> $parameters
	 * @return array<string,mixed>
	 */
	public function addMembershipAuthorizationParameters( int $membershipId, array $parameters ): array;
}
