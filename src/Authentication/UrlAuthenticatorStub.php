<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\CreditCardURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\LegacyPayPalURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\PayPalURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\SofortURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

/**
 * Used for changing membership fees at the moment.
 * As long as our memberships do not support external payments like PayPal we use this Stub class.
 */
class UrlAuthenticatorStub implements URLAuthenticator {

	public function addAuthenticationTokensToApplicationUrl( string $url ): string {
		return $url;
	}

	/**
	 * @param class-string $urlGeneratorClass
	 * @param string[] $requestedParameters
	 *
	 * @return array<string, int|string>
	 */
	public function getAuthenticationTokensForPaymentProviderUrl( string $urlGeneratorClass, array $requestedParameters ): array {
		if (
			$urlGeneratorClass === PayPalURLGenerator::class ||
			$urlGeneratorClass === LegacyPayPalURLGenerator::class ||
			$urlGeneratorClass === CreditCardURLGenerator::class
		) {
			throw new \InvalidArgumentException( 'Authenticator stub does not support external payment types: ' . $urlGeneratorClass );
		}

		$params = [];

		foreach ( $requestedParameters as $p ) {
			$params[$p] = '';
		}

		return $params;
	}
}
