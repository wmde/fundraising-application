<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use GuzzleHttp\Client;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalPaymentNotificationVerifier {

	/** @var Client */
	private $httpClient;
	private $config;
	private $allowedStatuses = [ 'Completed' ];
	private $allowedCurrencyCodes = [ 'EUR' ];

	public function __construct( Client $httpClient, array $config ) {
		$this->httpClient = $httpClient;
		$this->config = $config;
	}

	/**
	 * Verifies the request's integrity and reassures with PayPal
	 * servers that the request wasn't tampered with during transfer
	 *
	 * @param array $request
	 *
	 * @return bool
	 * @throws PayPalPaymentNotificationVerifierException
	 */
	public function verify( array $request ) {
		if ( !$this->matchesReceiverAddress( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException( 'Payment receiver address does not match' );
		}

		if ( !$this->hasAllowedPaymentStatus( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException( 'Payment status is not configured as confirmable' );
		}

		if ( !$this->hasValidItemName( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException( 'Invalid item name' );
		}

		if ( !$this->hasValidCurrencyCode( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException( 'Invalid currency code' );
		}

		$result = $this->httpClient->post(
			$this->config['base-url'],
			array_merge( [ 'cmd' => '_notify_validate' ], $request )
		);

		if ( $result->getStatusCode() !== 200 ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider returned an error (HTTP status: ' . $result->getStatusCode() . ')'
			);
		}

		if ( trim( $result->getBody()->getContents() ) !== 'VERIFIED' ) {
			throw new PayPalPaymentNotificationVerifierException( 'Payment provider did not confirm the sent data' );
		}
	}

	private function matchesReceiverAddress( array $request ): bool {
		return array_key_exists( 'receiver_email', $request ) &&
			$request['receiver_email'] === $this->config['account-address'];
	}

	private function hasAllowedPaymentStatus( array $request ): bool {
		return array_key_exists( 'payment_status', $request ) &&
			in_array( $request['payment_status'], $this->allowedStatuses );
	}

	private function hasValidItemName( array $request ): bool {
		return array_key_exists( 'item_name', $request ) &&
			$request['item_name'] === $this->config['item-name'];
	}

	private function hasValidCurrencyCode( array $request ): bool {
		return array_key_exists( 'mc_currency', $request ) &&
			in_array( $request['mc_currency'], $this->allowedCurrencyCodes );
	}
}
