<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\CreditCardURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\LegacyPayPalURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\PaymentUrlGenerator\SofortURLGenerator;
use WMDE\Fundraising\PaymentContext\Services\URLAuthenticator;

class AccessTokenUrlAuthenticator implements URLAuthenticator {
	public function __construct( private readonly AuthenticationToken $token ) {
	}

	public function addAuthenticationTokensToApplicationUrl( string $url ): string {
		$params = [
			'id' => $this->token->id,
			'accessToken' => $this->token->getAccessToken(),
		];
		return str_contains( $url, '?' )
			? $url . '&' . http_build_query( $params )
			: $url . '?' . http_build_query( $params );
	}

	/**
	 * @param class-string $urlGeneratorClass
	 * @param string[] $requestedParameters
	 *
	 * @return array<string, int|string>
	 */
	public function getAuthenticationTokensForPaymentProviderUrl( string $urlGeneratorClass, array $requestedParameters ): array {
		$params = match ( $urlGeneratorClass ) {
			LegacyPayPalURLGenerator::class => [
				'custom' => json_encode( [
					'sid' => $this->token->id,
					'utoken' => $this->token->getUpdateToken()
				] )
			],
			CreditCardURLGenerator::class => [
				'utoken' => $this->token->getUpdateToken(),
				'token' => $this->token->getAccessToken()
			],
			SofortURLGenerator::class =>  [
				'id' => $this->token->id,
				'updateToken' => $this->token->getUpdateToken(),
			],
			default => throw new \InvalidArgumentException( 'Unsupported URL generator class: ' . $urlGeneratorClass ),
		};

		$this->checkIfRequestedParametersMatchGeneratedParameters( $params, $requestedParameters );

		return $params;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param string[] $requestedParameters
	 */
	private function checkIfRequestedParametersMatchGeneratedParameters( array $params, array $requestedParameters ): void {
		$paramNames = array_keys( $params );
		if ( array_diff( $requestedParameters, $paramNames ) !== [] ) {
			throw new \DomainException( sprintf(
				'Requested parameters (%s) do not match generated parameters (%s)',
				implode( ', ', array_map( fn ( $p ) => "'$p'", $requestedParameters ) ),
				implode( ', ', array_map( fn ( $p ) => "'$p'", $paramNames ) ),
			) );
		}
	}
}
