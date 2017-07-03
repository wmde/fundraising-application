<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer;

use RuntimeException;
use Sofort\SofortLib\Sofortueberweisung;

/**
 * Facade in front of Sofortueberweisung, an API to generate URLs of Sofort's checkout process
 */
class Client {

	/**
	 * @var Sofortueberweisung
	 */
	private $api;

	/**
	 * @param string $configkey The secret key to use for Sofort API communication
	 */
	public function __construct( string $configkey ) {
		$this->api = new Sofortueberweisung( $configkey );
	}

	/**
	 * Set API to use instead of the one chosen by the facade
	 *
	 * @param Sofortueberweisung $sofortueberweisung
	 */
	public function setApi( Sofortueberweisung $sofortueberweisung ): void {
		$this->api = $sofortueberweisung;
	}

	/**
	 * @throws RuntimeException
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function get( Request $request ): Response {

		// Mapping currency amount to 3rd party float format. Known flaw
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
		$response->setPaymentUrl( $this->api->getPaymentUrl() );
		$response->setTransactionId( $this->api->getTransactionId() );

		return $response;
	}
}
