<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class UrlAuthenticatorStub implements URLAuthenticator {

	public function addAuthenticationTokensToApplicationUrl( string $url ): string {
		return $url;
	}

	/**
	 * @param string $urlGeneratorClass
	 * @param mixed[] $requestedParameters
	 *
	 * @return mixed[]
	 */
	public function getAuthenticationTokensForPaymentProviderUrl( string $urlGeneratorClass, array $requestedParameters ): array {
		return [];
	}
}
