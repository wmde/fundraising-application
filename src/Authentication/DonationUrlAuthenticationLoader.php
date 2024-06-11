<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

/**
 * This is for getting an UrlAuthenticator for a donation when redirecting
 */
interface DonationUrlAuthenticationLoader {
	public function getDonationUrlAuthenticator( int $donationId ): URLAuthenticator;

	/**
	 * This is for adding authorization parameters (e.g. "updateToken") to POST requests or template data, where they must not be in the URL
	 * @param int $donationId
	 * @param array<string, scalar> $parameters
	 * @return array<string, scalar>
	 */
	public function addDonationAuthorizationParameters( int $donationId, array $parameters ): array;
}
