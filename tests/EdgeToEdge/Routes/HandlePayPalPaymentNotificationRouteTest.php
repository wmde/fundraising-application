<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingPayPalVerificationService;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingVerificationServiceFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingVerificationServiceFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\PayPal\PayPalVerificationService;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\SucceedingVerificationService;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Payment\PaypalNotificationController
 */
class HandlePayPalPaymentNotificationRouteTest extends WebRouteTestCase {

	private const EMAIL_ADDRESS = 'foerderpp@wikimedia.de';
	private const ITEM_NAME = 'My preciousss';
	private const UPDATE_TOKEN = 'my_secret_token';
	private const DONATION_ID = 1;
	private const PATH = '/handle-paypal-payment-notification';
	private const LEGACY_PATH = '/spenden/paypal_handler.php';

	public function storedDonations(): StoredDonations {
		return new StoredDonations( $this->getFactory() );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertSame( '', $client->getResponse()->getContent(), 'Success response should be empty' );
		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
	}

	public function testGivenValidRequestForRecurringDonation_applicationIndicatesSuccess(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		// TODO Change stored donations/payments for paypal to include Order/Subscription IDs
		$this->storedDonations()->newStoredIncompletePayPalDonation();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForRecurringDonationPayment()
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertSame( '', $client->getResponse()->getContent(), 'Success response should be empty' );
		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
	}

	// TODO testGivenValidRequestForRecurringDonationWithMissingSubscriptionId_applicationIndicatesFailure(): void

	// TODO testGivenValidRequestForOneTimeDonation_applicationIndicatesSuccess(): void
	// TODO testGivenValidRequestForOneTimeDonationWithMissingSubscriptionId_applicationIndicatesFailure(): void

	public function testGivenRequestWithMissingItemId_getsIdFromCustomArray(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$request = $this->newHttpParamsForPayment();
		$request['item_number'] = '';

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$request
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
	}

	public function testGivenValidRequestToLegacyPath_applicationIndicatesSuccess(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$client->request(
			Request::METHOD_POST,
			self::LEGACY_PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
	}

	private function assertPayPalDataGotPersisted( array $request ): void {
		$donation = $this->getFactory()->getDonationRepository()->getDonationById( self::DONATION_ID );
		$payment = $this->getFactory()->getPaymentRepository()->getPaymentById( $donation->getPaymentId() );
		$paymentData = $payment->getLegacyData();

		$this->assertSame( $request['payer_id'], $paymentData->paymentSpecificValues['paypal_payer_id'] );
		$this->assertSame( $request['subscr_id'], $paymentData->paymentSpecificValues['paypal_subscr_id'] );
		$this->assertSame( $request['payer_status'], $paymentData->paymentSpecificValues['paypal_payer_status'] );
		$this->assertSame( $request['mc_currency'], $paymentData->paymentSpecificValues['paypal_mc_currency'] );
		$this->assertSame( $request['mc_fee'], $paymentData->paymentSpecificValues['paypal_mc_fee'] );
		$this->assertSame( $request['mc_gross'], $paymentData->paymentSpecificValues['paypal_mc_gross'] );
		$this->assertSame( $request['settle_amount'], $paymentData->paymentSpecificValues['paypal_settle_amount'] );
		$this->assertSame( $request['txn_id'], $paymentData->paymentSpecificValues['ext_payment_id'] );
		$this->assertSame( $request['payment_type'], $paymentData->paymentSpecificValues['ext_payment_type'] );
		$this->assertSame( $request['payment_status'] . '/' . $request['txn_type'], $paymentData->paymentSpecificValues['ext_payment_status'] );
		$this->assertSame( $request['payment_date'], $paymentData->paymentSpecificValues['ext_payment_timestamp'] );
	}

	private static function newHttpParamsForPayment(): array {
		return [
			'receiver_email' => self::EMAIL_ADDRESS,
			'payment_status' => 'Completed',
			'payer_id' => 'LPLWNMTBWMFAY',
			'subscr_id' => '8RHHUM3W3PRH7QY6B59',
			'payer_status' => 'verified',
			'address_status' => 'confirmed',
			'mc_gross' => '1.23',
			'mc_currency' => 'EUR',
			'mc_fee' => '0.23',
			'settle_amount' => '2.34',
			'first_name' => 'Generous',
			'last_name' => 'Donor',
			'address_name' => 'Generous Donor',
			'item_name' => self::ITEM_NAME,
			'item_number' => 1,
			'custom' => '{"sid": 1, "utoken": "my_secret_token"}',
			'txn_id' => '61E67681CH3238416',
			'payment_type' => 'instant',
			'txn_type' => 'web_accept',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	private static function newHttpParamsForRecurringDonationPayment(): array {
		return [
			"mc_gross" => "5.00",
			"outstanding_balance" => "0.00",
			"period_type" => " Regular",
			"next_payment_date" => "02:00:00 Feb 04, 2024 PST",
			"protection_eligibility" => "Eligible",
			"payment_cycle" => "every 6 Months",
			"tax" => "0.00",
			"payment_date" => "00:59:58 Aug 04, 2023 PDT",
			"payment_status" => "Completed",
			"product_name" => "Halbjährliche Spende an Wikimedia",
			"charset" => "UTF-8",
			"recurring_payment_id" => "I-GLDWYUKDXAVE",
			"first_name" => "Donny",
			"mc_fee" => "0.45",
			"notify_version" => "3.9",
			"amount_per_cycle" => "5.00",
			"payer_status" => "verified",
			"currency_code" => "EUR",
			"business" => self::EMAIL_ADDRESS,
			"verify_sign" => "AAXUDeWm6RGbJ0GeUjbjQLb8U6hmApvZpWfSinHyAtPQf-gajnSWjpoh",
			"initial_payment_amount" => "0.00",
			"profile_status" => "Active",
			"amount" => "1.23",
			"txn_id" => "61E67681CH3238416",
			"payment_type" => "instant",
			"last_name" => "Donut",
			"receiver_email" => "paypal-test-merchant@wikimedia.de",
			"payment_fee" => "",
			"receiver_id" => "MDBLBA6HBUV58",
			"txn_type" => "recurring_payment",
			"mc_currency" => "EUR",
			"residence_country" => "DE",
			"transaction_subject" => "Halbjährliche Spende an Wikimedia",
			"payment_gross" => "",
			"shipping" => "0.00",
			"product_type" => "1",
			"time_created" => "00:59:57 Aug 04, 2023 PDT",
			"ipn_track_id" => "f370317d5076d"
		];
	}

	public function testGivenInvalidReceiverEmail_applicationReturnsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( PayPalVerificationService::ERROR_WRONG_RECEIVER )
		);

		$request = $this->newHttpParamsForPayment();
		$request['receiver_email'] = 'mr.robot@evilcorp.com';

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$request
		);

		$this->assertSame( 'Payment receiver address does not match', $client->getResponse()->getContent() );
		$this->assertSame( 403, $client->getResponse()->getStatusCode() );
	}

	/**
	 * @dataProvider unsupportedPaymentStatusProvider
	 */
	public function testGivenUnsupportedPaymentStatus_applicationReturnsOK( array $params ): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$params
		);

		$this->assertSame( '', $client->getResponse()->getContent() );
		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
	}

	/**
	 * @dataProvider unsupportedPaymentStatusProvider
	 */
	public function testGivenUnsupportedPaymentStatus_requestDataIsLogged( array $params, string $paymentStatus ): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$logger = new LoggerSpy();
		$factory->setPaypalLogger( $logger );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$params
		);

		$this->assertSame(
			[ 'PayPal request not handled' ],
			$logger->getLogCalls()->getMessages()
		);

		$this->assertSame(
			$paymentStatus,
			$logger->getLogCalls()->getFirstCall()->getContext()['post_vars']['payment_status']
		);
	}

	public static function unsupportedPaymentStatusProvider(): \Iterator {
		yield [ self::newPendingPaymentParams(), 'Pending' ];
		yield [ self::newCancelPaymentParams(), 'Cancel' ];
		yield [ self::newValidRequestParametersWithNegativeTransactionFee(), 'Refunded' ];
	}

	public function testGivenPersonalPaypalInfosOnError_PrivateInfoIsExcludedFromGettingLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( PayPalVerificationService::ERROR_UNSUPPORTED_CURRENCY )
		);
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$logger = new LoggerSpy();
		$factory->setPaypalLogger( $logger );

		$requestData = $this->newHttpParamsForPayment();
		$requestData['mc_currency'] = 'unsupportedCurrencyTM';
		$requestData['payer_email'] = 'IshouldNotGetLogged@privatestuff.de';
		$requestData['payer_id'] = '123456personalID';

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$requestData
		);

		$this->assertSame( 'Unsupported currency', $client->getResponse()->getContent() );

		$this->assertSame(
			[ 'Unsupported currency' ],
			$logger->getLogCalls()->getMessages()
		);

		$loggedDataAsString = implode( $logger->getLogCalls()->getFirstCall()->getContext()['post_vars'] );

		$this->assertStringNotContainsString( 'IshouldNotGetLogged@privatestuff.de', $loggedDataAsString );
		$this->assertStringNotContainsString( '123456personalID', $loggedDataAsString );
		$this->assertStringContainsString( 'unsupportedCurrencyTM', $loggedDataAsString );
	}

	public function testGivenFailingVerification_applicationReturnsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( sprintf( PayPalVerificationService::ERROR_UNKNOWN, 'FAIL' ) )
		);
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 'An error occurred while trying to confirm the sent data. PayPal response: FAIL', $client->getResponse()->getContent() );
		$this->assertSame( 403, $client->getResponse()->getStatusCode() );
	}

	public function testGivenUnsupportedCurrency_applicationReturnsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( PayPalVerificationService::ERROR_UNSUPPORTED_CURRENCY )
		);
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$requestData = $this->newHttpParamsForPayment();
		$requestData['mc_currency'] = 'DOGE';
		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$requestData
		);

		$this->assertSame( 'Unsupported currency', $client->getResponse()->getContent() );
		$this->assertSame( 406, $client->getResponse()->getStatusCode() );
	}

	public function testGivenTransactionTypeForSubscriptionChanges_requestDataIsLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );
		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$logger = new LoggerSpy();
		$factory->setPaypalLogger( $logger );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newSubscriptionModificationParams()
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );

		$this->assertSame(
			[ 'PayPal request not handled' ],
			$logger->getLogCalls()->getMessages()
		);

		$this->assertSame(
			'subscr_modify',
			$logger->getLogCalls()->getFirstCall()->getContext()['post_vars']['txn_type']
		);
	}

	private function newSubscriptionModificationParams(): array {
		return [
			'receiver_email' => self::EMAIL_ADDRESS,
			'payment_status' => 'Completed',
			'payer_id' => 'LPLWNMTBWMFAY',
			'subscr_id' => '8RHHUM3W3PRH7QY6B59',
			'payer_status' => 'verified',
			'address_status' => 'confirmed',
			'mc_gross' => '1.23',
			'mc_currency' => 'EUR',
			'mc_fee' => '0.23',
			'settle_amount' => '2.34',
			'first_name' => 'Generous',
			'last_name' => 'Donor',
			'address_name' => 'Generous Donor',
			'item_name' => self::ITEM_NAME,
			'item_number' => 1,
			'custom' => '{"id": "1", "utoken": "my_secret_token"}',
			'txn_id' => '61E67681CH3238416',
			'payment_type' => 'instant',
			'txn_type' => 'subscr_modify',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	private static function newPendingPaymentParams(): array {
		return [
			'receiver_email' => self::EMAIL_ADDRESS,
			'payment_status' => 'Pending',
			'payer_id' => 'LPLWNMTBWMFAY',
			'subscr_id' => '8RHHUM3W3PRH7QY6B59',
			'payer_status' => 'verified',
			'address_status' => 'confirmed',
			'mc_gross' => '1.23',
			'mc_currency' => 'EUR',
			'mc_fee' => '0.23',
			'settle_amount' => '2.34',
			'first_name' => 'Generous',
			'last_name' => 'Donor',
			'address_name' => 'Generous Donor',
			'item_name' => self::ITEM_NAME,
			'item_number' => 1,
			'custom' => '{"id": "1", "utoken": "my_secret_token"}',
			'txn_id' => '61E67681CH3238416',
			'payment_type' => 'instant',
			'txn_type' => 'express_checkout',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	private static function newCancelPaymentParams(): array {
		return [
			'receiver_email' => self::EMAIL_ADDRESS,
			'payment_status' => 'Cancel',
			'payer_id' => 'LPLWNMTBWMFAY',
			'subscr_id' => '8RHHUM3W3PRH7QY6B59',
			'payer_status' => 'verified',
			'address_status' => 'confirmed',
			'mc_gross' => '-1.23',
			'mc_currency' => 'EUR',
			'mc_fee' => '0.23',
			'settle_amount' => '-2.34',
			'first_name' => 'Generous',
			'last_name' => 'Donor',
			'address_name' => 'Generous Donor',
			'item_name' => self::ITEM_NAME,
			'item_number' => 1,
			'custom' => '{"id": "1", "utoken": "my_secret_token"}',
			'txn_id' => '61E67681CH3238416',
			'payment_type' => 'instant',
			'txn_type' => 'express_checkout',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	public function testDonationIsNotFound_createsNewAnonymousDonation(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( 'Donation not found' )
		);
		$factory->setPayPalVerificationService( new SucceedingVerificationService() );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
		$this->assertSame( '', $client->getResponse()->getContent() );
		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
	}

	public function testDonationIsNotFound_andCreationFails_logsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory(
			new FailingVerificationServiceFactory( 'Donation not found' )
		);
		$factory->setPayPalVerificationService( new FailingPayPalVerificationService( 'Awoo! Nyaa!' ) );
		$logger = new LoggerSpy();
		$factory->setPaypalLogger( $logger );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame(
			[ 'Awoo! Nyaa!' ],
			$logger->getLogCalls()->getMessages()
		);
	}

	public function testOnInternalError_applicationIndicatesError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$repository = new ThrowingDonationRepository();
		$repository->throwOnGetDonationById();
		$factory->setDonationRepository( $repository );

		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 500, $client->getResponse()->getStatusCode() );
		$this->assertStringContainsString( "Could not get donation", $client->getResponse()->getContent() );
	}

	public function testOnInternalError_applicationLogsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$repository = new ThrowingDonationRepository();
		$repository->throwOnGetDonationById();
		$factory->setDonationRepository( $repository );

		$paypalLogger = new LoggerSpy();
		$mainErrorLogger = new LoggerSpy();

		$factory->setPaypalLogger( $paypalLogger );
		$factory->setLogger( $mainErrorLogger );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 1, $paypalLogger->getLogCalls()->count() );
		$firstCallContext = $paypalLogger->getFirstLogCall()->getContext();
		$this->assertArrayHasKey( 'stacktrace', $firstCallContext );
		$this->assertArrayHasKey( 'post_vars', $firstCallContext );
		$this->assertSame( 'An Exception happened: Could not get donation', $paypalLogger->getFirstLogCall()->getMessage() );

		$this->assertSame( 1, $mainErrorLogger->getLogCalls()->count() );
		$this->assertSame( 'An Exception happened: Could not get donation', $mainErrorLogger->getFirstLogCall()->getMessage() );
	}

	public function testGivenInternalErrorIsPaymentAlreadyBooked_applicationIndicatesSuccess(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$paypalLogger = new LoggerSpy();
		$mainErrorLogger = new LoggerSpy();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );
		$factory->setPaypalLogger( $paypalLogger );
		$factory->setLogger( $mainErrorLogger );

		// trying to book an already-stored PayPal donation should trigger an error
		$this->storedDonations()->newStoredCompletePayPalDonation( self::UPDATE_TOKEN );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$this->newHttpParamsForPayment()
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertCount( 1, $mainErrorLogger->getLogCalls() );
		$this->assertCount( 1, $paypalLogger->getLogCalls() );
		$this->assertSame( LogLevel::WARNING, $mainErrorLogger->getFirstLogCall()->getLevel(), 'Double-booked payment should be warnings, not errors' );
		$this->assertSame( LogLevel::WARNING, $paypalLogger->getFirstLogCall()->getLevel(), 'Double-booked payment should be warnings, not errors' );
	}

	private static function newValidRequestParametersWithNegativeTransactionFee(): array {
		$parameters = self::newHttpParamsForPayment();
		$parameters['mc_fee'] = '-12.34';
		$parameters['payment_status'] = 'Refunded';
		return $parameters;
	}
}
