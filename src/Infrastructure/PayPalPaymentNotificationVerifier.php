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

	/**
	 * PayPalPaymentNotificationVerifier constructor.
	 * @param Client $httpClient
	 * @param array $config
	 */
	public function __construct( $httpClient, array $config ) {
		$this->httpClient = $httpClient;
		$this->config = $config;
	}

	public function verify( array $data ): bool {
		if ( !$this->matchReceiverAddress( $data ) ) {
			// TODO: might want to log this
			return false;
		}

		if ( !$this->isPaymentStatusAllowed( $data ) ) {
			// TODO: might want to log this
			return false;
		}

		$result = $this->httpClient->post(
			$this->config['base-url'],
			array_merge( [ 'cmd' => '_notify_validate' ], $data )
		);

		if ( $result->getStatusCode() !== 200 ) {
			// TODO: might want to log this
			return false;
		}

		return ( trim( $result->getBody()->getContents() ) === 'VERIFIED' );
	}

	private function matchReceiverAddress( $data ): bool {
		return array_key_exists( 'receiver_email', $data ) &&
			$data['receiver_email'] === $this->config['account-address'];
	}

	private function isPaymentStatusAllowed( $request ): bool {
		return array_key_exists( 'payment_status', $request ) &&
			in_array( $request['payment_status'], $this->allowedStatuses );
	}

}
