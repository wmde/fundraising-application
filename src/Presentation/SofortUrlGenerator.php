<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Client;
use WMDE\Fundraising\Frontend\Infrastructure\Sofort\Transfer\Request;
use WMDE\Euro\Euro;
use RuntimeException;

/**
 * Generate the URL of the Sofort checkout process
 */
class SofortUrlGenerator {

	private const CURRENCY = 'EUR';

	/**
	 * @var SofortUrlConfig
	 */
	private $config;
	/**
	 * @var Client
	 */
	private $client;

	public function __construct( SofortUrlConfig $config, Client $client ) {
		$this->config = $config;
		$this->client = $client;
	}

	/**
	 * Generate a URL to use (refer the donor to) to finalize a purchase on a 3rd party payment provider page
	 *
	 * @param int $itemId Id of the item to pay
	 * @param Euro $amount The amount of money to pay
	 * @param string $accessToken A token to return to the payment process after completing the 3rd party process
	 * @return string
	 */
	public function generateUrl( int $itemId, Euro $amount, string $accessToken ): string {
		$request = new Request();
		$request->setAmount( $amount );
		$request->setCurrencyCode( self::CURRENCY );
		$request->setReasons( [ $this->config->getReasonText(), $itemId ] );
		$request->setSuccessUrl(
			$this->config->getReturnUrl() . '?' . http_build_query( [
				'id' => $itemId,
				'accessToken' => $accessToken
			] )
		);
		$request->setAbortUrl( $this->config->getCancelUrl() );
		// @todo To use in T167882
		$request->setNotificationUrl( '' );

		try {
			$response = $this->client->get( $request );
		} catch ( RuntimeException $exception ) {
			throw new RuntimeException( 'Could not generate Sofort URL: ' . $exception->getMessage() );
		}

		return $response->getPaymentUrl();
	}
}
