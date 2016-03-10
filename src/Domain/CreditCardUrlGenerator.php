<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardUrlGenerator {

	private $config;

	public function __construct( CreditCardConfig $config ) {
		$this->config = $config;
	}

	public function generateUrl( string $firstName, string $lastName, string $payText,
								  int $donationId, string $updateToken, float $amount ) {
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
			'utoken' => $updateToken,
			'amount' => intval( $amount * 100 ),
			'theme' => $this->config->getTheme()
		];

		return $baseUrl . http_build_query( $params );
	}

}
