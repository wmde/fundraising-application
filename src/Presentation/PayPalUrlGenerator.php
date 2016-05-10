<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;

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

	public function generateUrl( int $donationId, Euro $amount, int $interval,
								 string $updateToken, string $accessToken ): string {

		$params = array_merge(
			$this->getIntervalDependentParameters( $amount, $interval ),
			$this->getIntervalAgnosticParameters( $donationId, $updateToken, $accessToken )
		);

		return $this->config->getPayPalBaseUrl() . http_build_query( $params );
	}

	private function getIntervalAgnosticParameters( int $donationId, string $updateToken, string $accessToken ): array {
		return [
			'business' => $this->config->getPayPalAccountAddress(),
			'currency_code' => 'EUR',
			'lc' => 'de',
			'item_name' => $this->config->getItemName(),
			'item_number' => $donationId,
			'notify_url' => $this->config->getNotifyUrl(),
			'cancel_return' => $this->config->getCancelUrl(),
			'return' => $this->config->getReturnUrl() . '?donationId=' . $donationId . '&accessToken=' . $accessToken,
			'custom' => json_encode( [
				'sid' => $donationId,
				'utoken' => $updateToken
			] )
		];
	}

	private function getIntervalDependentParameters( Euro $amount, int $interval ): array {
		if ( $interval > 0 ) {
			return $this->getSubscriptionParams( $amount, $interval );
		}

		return $this->getSinglePaymentParams( $amount );
	}

	/**
	 * This method returns a set of parameters needed for recurring donations.
	 * @link https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/wp_standard_overview/
	 */
	private function getSubscriptionParams( Euro $amount, int $interval ): array {
		return [
			'cmd' => '_xclick-subscriptions',
			'no_shipping' => '1',
			'src' => self::PAYMENT_RECUR,
			'sra' => self::PAYMENT_REATTEMPT,
			'srt' => self::PAYMENT_CYCLE_INFINITE,
			'a3' => $amount->getEuroString(),
			'p3' => $interval,
			't3' => self::PAYMENT_CYCLE_MONTHLY,
		];
	}

	/**
	 * This method returns a set of parameters needed for one time donations.
	 * @link https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/wp_standard_overview/
	 */
	private function getSinglePaymentParams( Euro $amount ): array {
		return [
			'cmd' => '_donations',
			'amount' => $amount->getEuroString()
		];
	}

}
