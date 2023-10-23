<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Authentication\DonationUrlAuthenticationLoader;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class DonationUrlAuthenticationLoaderStub implements DonationUrlAuthenticationLoader {
	public function __construct( private readonly array $authorizationParameters = [] ) {
	}

	public function getDonationUrlAuthenticator( int $donationId ): URLAuthenticator {
		return new UrlAuthenticatorStub();
	}

	public function addDonationAuthorizationParameters( int $donationId, array $parameters ): array {
		return [
			...$parameters,
			...$this->authorizationParameters
		];
	}
}
