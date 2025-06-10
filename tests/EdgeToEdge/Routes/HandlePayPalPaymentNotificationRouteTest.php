<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\App\Controllers\Payment\PaypalNotificationController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingPayPalVerificationService;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingVerificationServiceFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingVerificationServiceFactory;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\PayPal\PayPalVerificationService;
use WMDE\Fundraising\PaymentContext\Services\ExternalVerificationService\SucceedingVerificationService;

#[CoversClass( PaypalNotificationController::class )]
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

	public function testGivenRequestWithMissingItemId_getsIdFromCustomArray(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

		$this->storedDonations()->newStoredIncompletePayPalDonation( self::UPDATE_TOKEN );

		$request = $this->newHttpParamsForPayment();
		unset( $request['item_number'] );

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			$request
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
	}

	public function testGivenRequestWithEmptyItemId_getsIdFromCustomArray(): void {
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

	/**
	 * @param array<string, scalar> $request
	 * @return void
	 */
	private function assertPayPalDataGotPersisted( array $request ): void {
		$donation = $this->getFactory()->getDonationRepository()->getDonationById( self::DONATION_ID );
		$this->assertNotNull( $donation );

		/** @var string $encodedBookingData */
		$encodedBookingData = $this->getFactory()->getConnection()
			->executeQuery( "SELECT booking_data from payment_paypal WHERE ID=" . $donation->getPaymentId() )
			->fetchOne();
		$bookingData = json_decode( $encodedBookingData, true, 512, JSON_THROW_ON_ERROR );

		$this->assertIsArray( $bookingData );
		$this->assertSame( $request['payer_id'], $bookingData['payer_id'] );
		$this->assertSame( $request['subscr_id'], $bookingData['subscr_id'] );
		$this->assertSame( $request['payer_status'], $bookingData['payer_status'] );
		$this->assertSame( $request['mc_currency'], $bookingData['mc_currency'] );
		$this->assertSame( $request['mc_fee'], $bookingData['mc_fee'] );
		$this->assertSame( $request['mc_gross'], $bookingData['mc_gross'] );
		$this->assertSame( $request['settle_amount'], $bookingData['settle_amount'] );
		$this->assertSame( $request['txn_id'], $bookingData['txn_id'] );
		$this->assertSame( $request['payment_type'], $bookingData['payment_type'] );
		$this->assertSame( $request['payment_status'], $bookingData['payment_status'] );
		$this->assertSame( $request['txn_type'], $bookingData['txn_type'] );
		$this->assertSame( $request['payment_date'], $bookingData['payment_date'] );
	}

	/**
	 * @return array<string, int|string>
	 */
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
	 * @param array<string, int|string> $params
	 * @param string $paymentStatus Unused in this test, but needed as a parameter because
	 *                              PHPUnit calls this method with all parameters from the data provider
	 */
	#[DataProvider( 'unsupportedPaymentStatusProvider' )]
	public function testGivenUnsupportedPaymentStatus_applicationReturnsOK( array $params, string $paymentStatus ): void {
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
	 *
	 * @param array<string, int|string> $params
	 * @param string $paymentStatus
	 */
	#[DataProvider( 'unsupportedPaymentStatusProvider' )]
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

		$postVars = $logger->getFirstLogCall()->getContext()['post_vars'];
		$this->assertIsArray( $postVars );
		$this->assertSame( $paymentStatus, $postVars['payment_status']
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

		$postVars = $logger->getFirstLogCall()->getContext()['post_vars'];
		$this->assertIsArray( $postVars );
		$loggedDataAsString = implode( '', $postVars );

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

		$postVars = $logger->getFirstLogCall()->getContext()['post_vars'];
		$this->assertIsArray( $postVars );
		$this->assertSame( 'subscr_modify', $postVars['txn_type'] );
	}

	/**
	 * @return array<string, string|int>
	 */
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

	/**
	 * @return array<string, int|string>
	 */
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

	/**
	 * @return array<string, int|string>
	 */
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
		$this->assertStringContainsString( "Could not get donation", $client->getResponse()->getContent() ?: '' );
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

		$this->assertCount( 1, $paypalLogger->getLogCalls() );
		$firstCallContext = $paypalLogger->getFirstLogCall()->getContext();
		$this->assertArrayHasKey( 'stacktrace', $firstCallContext );
		$this->assertArrayHasKey( 'post_vars', $firstCallContext );
		$this->assertSame( 'An Exception happened: Could not get donation', $paypalLogger->getFirstLogCall()->getMessage() );

		$this->assertCount( 1, $mainErrorLogger->getLogCalls() );
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

	/**
	 * @return array<string, int|string>
	 */
	private static function newValidRequestParametersWithNegativeTransactionFee(): array {
		$parameters = self::newHttpParamsForPayment();
		$parameters['mc_fee'] = '-12.34';
		$parameters['payment_status'] = 'Refunded';
		return $parameters;
	}
}
