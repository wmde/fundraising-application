<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer;

use RuntimeException;
use Sofort\SofortLib\Sofortueberweisung;

class Client {

	/**
	 * @var Sofortueberweisung
	 */
	private $api;

	public function __construct( string $configkey ) {
		$this->api = new Sofortueberweisung( $configkey );
	}

	public function setApi( Sofortueberweisung $sofortueberweisung ): void {
		$this->api = $sofortueberweisung;
	}

	public function get( Request $request ): Response {

		// mapping Euro amount to 3rd party float
		$this->api->setAmount( $request->getAmount()->getEuroFloat() );

		$this->api->setCurrencyCode( $request->getCurrencyCode() );

		$reasons = $request->getReasons();

		$this->api->setReason( $reasons[0] ?? '', $reasons[1] ?? '' );

		$this->api->setSuccessUrl( $request->getSuccessUrl(), true );
		$this->api->setAbortUrl( $request->getAbortUrl() );
		$this->api->setNotificationUrl( $request->getNotificationUrl() );

		$this->api->sendRequest();

		if ( $this->api->isError() ) {
			throw new RuntimeException( $this->api->getError() );
		}

		$response = new Response();
		// @todo Do we have use for that?
		// unique transaction-ID useful to check payment status
		$response->setTransactionId( $this->api->getTransactionId() );
		$response->setPaymentUrl( $this->api->getPaymentUrl() );

		return $response;
	}
}
