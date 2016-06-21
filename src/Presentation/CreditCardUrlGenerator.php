<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardUrlGenerator {

	private $config;

	public function __construct( CreditCardUrlConfig $config ) {
		$this->config = $config;
	}

	public function generateUrl( string $firstName, string $lastName, string $payText, int $donationId,
								 string $accessToken, string $updateToken, Euro $amount ) {
		// TODO: implement sealed parameters (https://techdoc.micropayment.de/payment/payments/payments_en.html#id302721)
		$baseUrl = $this->config->getBaseUrl();
		$params = [
			'project' => $this->config->getProjectId(),
			'bgcolor' => $this->config->getBackgroundColor(),
			'paytext' => $payText,
			'mp_user_firstname' => $firstName,
			'mp_user_surname' => $lastName,
			'sid' => $donationId,
			'skin' => $this->config->getSkin(),
			'token' => $accessToken,
			'utoken' => $updateToken,
			'amount' => $amount->getEuroCents(),
			'theme' => $this->config->getTheme()
		];
		if ( $this->config->isTestMode() ) {
			$params['testmode'] = '1';
		}

		return $baseUrl . http_build_query( $params );
	}

}
