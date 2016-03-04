<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalUrlGenerator {

	const PAYMENT_RECUR = '1';
	const PAYMENT_REATTEMPT = '1';
	const PAYMENT_CYCLE_INFINITE = '0';
	const PAYMENT_CYCLE_MONTHLY = 'M';

	private $config;

	public function __construct( PayPalConfig $config ) {
		$this->config = $config;
	}

	public function generateUrl( int $id, float $amount, int $interval, string $accessToken, string $updateToken ) {
		if ( $interval > 0 ) {
			$params = $this->getSubscriptionParams( $amount, $interval );
		} else {
			$params = $this->getSinglePaymentParams( $amount );
		}

		$params = array_merge( $params, [
			'business' => $this->config->getPayPalAccountAddress(),
			'currency_code' => 'EUR',
			'lc' => 'de',
			'item_name' => $this->config->getItemName(),
			'item_number' => $id,
			'notify_url' => $this->config->getNotifyUrl(),
			'cancel_return' => $this->config->getCancelUrl(),
			'return' => $this->config->getReturnUrl() . '?sid=' . $id,
			'custom' => json_encode( [
				'sid' => $id,
				'token' => $accessToken,
				'utoken' => $updateToken
			] )
		] );

		return $this->config->getPayPalBaseUrl() . http_build_query( $params );
	}

	private function getSubscriptionParams( $amount, $interval ): array {
		return [
			'cmd' => '_xclick-subscriptions',
			'no_shipping' => '1',
			'src' => self::PAYMENT_RECUR,
			'sra' => self::PAYMENT_REATTEMPT,
			'srt' => self::PAYMENT_CYCLE_INFINITE,
			'a3' => $amount,
			'p3' => $interval,
			't3' => self::PAYMENT_CYCLE_MONTHLY,
		];
	}

	private function getSinglePaymentParams( $amount ): array {
		return [
			'cmd' => '_donations',
			'amount' => $amount
		];
	}

}
