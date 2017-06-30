<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use Sofort\SofortLib\Sofortueberweisung;
use WMDE\Euro\Euro;
use RuntimeException;

class SofortUrlGenerator {

	private const CURRENCY = 'EUR';

	/**
	 * @var \WMDE\Fundraising\Frontend\Presentation\SofortUrlConfig
	 */
	private $config;
	/**
	 * @var \Sofort\SofortLib\Sofortueberweisung
	 */
	private $api;

	public function __construct( SofortUrlConfig $config, Sofortueberweisung $api ) {
		$this->config = $config;
		$this->api = $api;
	}

	public function generateUrl( int $itemId, Euro $amount, string $accessToken ): string {
		$this->api->setAmount( $amount->getEuroString() );
		$this->api->setCurrencyCode( self::CURRENCY );
		$this->api->setReason( $this->config->getReasonText(), $itemId );

		$this->api->setSuccessUrl(
			$this->config->getReturnUrl() . '?' . http_build_query( [
				'id' => $itemId,
				'accessToken' => $accessToken
			] ),
			true
		);
		$this->api->setAbortUrl( $this->config->getCancelUrl() );

		// @todo Do we need that?
		//$this->api->setNotificationUrl('YOUR_NOTIFICATION_URL');

		$this->api->sendRequest();

		if ( $this->api->isError() ) {
			throw new RuntimeException( 'Could not generate Sofort URL: ' . $this->api->getError() );
		}

		// @todo Do we have use for that?
		// unique transaction-ID useful to check payment status
		$transactionId = $this->api->getTransactionId();

		return $this->api->getPaymentUrl();
	}
}
