<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * This class checks some basic properties of a PayPal IPN message
 * and sends it back to PayPal to make sure it originated from PayPal.
 *
 * See https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNImplementation/#specs
 *
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PayPalPaymentNotificationVerifier implements PaymentNotificationVerifier {

	private const ALLOWED_CURRENCY_CODES = [ 'EUR' ];
	private const NOTIFICATION_TYPES_WITH_DIFFERENT_CURRENCY_FIELDS = [
		'recurring_payment_suspended_due_to_max_failed_payment'
	];

	private Client $httpClient;

	/**
	 * @var string PayPal IPN verification end point
	 */
	private string $baseUrl;

	/**
	 * @var string Email address of our PayPal account
	 */
	private string $accountAddress;

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
	public function verify( array $request ): void {
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

		$result = $this->httpClient->request(
			'POST',
			$this->baseUrl,
			[
				RequestOptions::FORM_PARAMS => array_merge( [ 'cmd' => '_notify-validate' ], $request ),
				// disable throwing exceptions, return status instead
				RequestOptions::HTTP_ERRORS => false
			]
		);

		if ( $result->getStatusCode() !== 200 ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider returned an error (HTTP status: ' . $result->getStatusCode() . ')',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}

		$responseBody = trim( $result->getBody()->getContents() );
		if ( $responseBody === 'VERIFIED' ) {
			return;
		}

		$failureMessage = $responseBody === 'INVALID' ?
			'Payment provider did not confirm the sent data' :
			sprintf( "An error occurred while trying to confirm the sent data. PayPal response: %s", $responseBody );

		throw new PayPalPaymentNotificationVerifierException(
			$failureMessage,
			PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
		);
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
		return array_key_exists( 'mc_currency', $request ) &&
			in_array( $request['mc_currency'], self::ALLOWED_CURRENCY_CODES );
	}

	private function hasDifferentCurrencyField( array $request ): bool {
		return array_key_exists( 'txn_type', $request ) &&
			in_array( $request['txn_type'], self::NOTIFICATION_TYPES_WITH_DIFFERENT_CURRENCY_FIELDS );
	}

}
