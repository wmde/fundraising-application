<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use GuzzleHttp\Client;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PayPalPaymentNotificationVerifier implements PaymentNotificationVerifier {

	private const ALLOWED_CURRENCY_CODES = [ 'EUR' ];
	private const NOTIFICATION_TYPES_WITH_DIFFERENT_CURRENCY_FIELDS = [
		'recurring_payment_suspended_due_to_max_failed_payment'
	];

	private $httpClient;
	private $baseUrl;
	private $accountAddress;

	public function __construct( Client $httpClient, string $baseUrl, string $accountAddress ) {
		$this->httpClient = $httpClient;
		$this->baseUrl = $baseUrl;
		$this->accountAddress = $accountAddress;
	}

	/**
	 * @see PaymentNotificationVerifier::verify
	 *
	 * @param array $request
	 *
	 * @throws PayPalPaymentNotificationVerifierException
	 */
	public function verify( array $request ) {
		if ( !$this->matchesReceiverAddress( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment receiver address does not match',
				PayPalPaymentNotificationVerifierException::ERROR_WRONG_RECEIVER
			);
		}

		if ( !$this->hasValidCurrencyCode( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Unsupported currency',
				PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_CURRENCY
			);
		}

		$result = $this->httpClient->post(
			$this->baseUrl,
			[ 'form_params' => array_merge( [ 'cmd' => '_notify-validate' ], $request ) ]
		);

		if ( $result->getStatusCode() !== 200 ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider returned an error (HTTP status: ' . $result->getStatusCode() . ')',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}

		$responseBody = trim( $result->getBody()->getContents() );
		if ( $responseBody === 'INVALID' ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider did not confirm the sent data',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}

		if ( $responseBody !== 'VERIFIED' ) {
			throw new PayPalPaymentNotificationVerifierException(
				'An error occurred while trying to confirm the sent data',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}
	}

	private function matchesReceiverAddress( array $request ): bool {
		return array_key_exists( 'receiver_email', $request ) &&
			$request['receiver_email'] === $this->accountAddress;
	}

	private function hasValidCurrencyCode( array $request ): bool {
		if ( $this->hasDifferentCurrencyField( $request ) ) {
			return array_key_exists( 'currency_code', $request ) &&
				in_array( $request['currency_code'], self::ALLOWED_CURRENCY_CODES );
		}
		return
			array_key_exists( 'mc_currency', $request ) &&
			in_array( $request['mc_currency'], self::ALLOWED_CURRENCY_CODES );
	}

	private function hasDifferentCurrencyField( array $request ): bool {
		return array_key_exists( 'txn_type', $request ) &&
			in_array( $request['txn_type'], self::NOTIFICATION_TYPES_WITH_DIFFERENT_CURRENCY_FIELDS );
	}
}
