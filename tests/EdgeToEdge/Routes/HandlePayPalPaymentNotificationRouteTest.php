<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingPayPalVerificationService;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingVerificationServiceFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
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
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
		} );
	}

	public function testGivenRequestWithMissingItemId_getsIdFromCustomArray(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$request = $this->newHttpParamsForPayment();
			$request['item_number'] = '';

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$request
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
		} );
	}

	public function testGivenValidRequestToLegacyPath_applicationIndicatesSuccess(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$this->storedDonations()->newStoredIncompletePayPalDonation();
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$client->request(
				Request::METHOD_POST,
				self::LEGACY_PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $this->newHttpParamsForPayment() );
		} );
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

	private function newHttpParamsForPayment(): array {
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
			'txn_type' => 'express_checkout',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	public function testGivenInvalidReceiverEmail_applicationReturnsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$this->storedDonations()->newStoredIncompletePayPalDonation();
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
		} );
	}

	/**
	 * @dataProvider unsupportedPaymentStatusProvider
	 */
	public function testGivenUnsupportedPaymentStatus_applicationReturnsOK( array $params ): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ) use ( $params ): void {
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$params
			);

			$this->assertSame( '', $client->getResponse()->getContent() );
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		} );
	}

	/**
	 * @dataProvider unsupportedPaymentStatusProvider
	 */
	public function testGivenUnsupportedPaymentStatus_requestDataIsLogged( array $params, string $paymentStatus ): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ) use ( $params, $paymentStatus ): void {
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
		} );
	}

	public function unsupportedPaymentStatusProvider(): \Iterator {
		yield [ $this->newPendingPaymentParams(), 'Pending' ];
		yield [ $this->newCancelPaymentParams(), 'Cancel' ];
		yield [ $this->newValidRequestParametersWithNegativeTransactionFee(), 'Refunded' ];
	}

	public function testGivenPersonalPaypalInfosOnError_PrivateInfoIsExcludedFromGettingLogged(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory(
				new FailingVerificationServiceFactory( PayPalVerificationService::ERROR_UNSUPPORTED_CURRENCY )
			);
			$this->storedDonations()->newStoredIncompletePayPalDonation();

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
		} );
	}

	public function testGivenFailingVerification_applicationReturnsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory(
				new FailingVerificationServiceFactory( sprintf( PayPalVerificationService::ERROR_UNKNOWN, 'FAIL' ) )
			);
			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 'An error occurred while trying to confirm the sent data. PayPal response: FAIL', $client->getResponse()->getContent() );
			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenUnsupportedCurrency_applicationReturnsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory(
				new FailingVerificationServiceFactory( PayPalVerificationService::ERROR_UNSUPPORTED_CURRENCY )
			);
			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$requestData = $this->newHttpParamsForPayment();
			$requestData['mc_currency'] = 'DOGE';
			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$requestData
			);

			$this->assertSame( 'Unsupported currency', $client->getResponse()->getContent() );
			$this->assertSame( 406, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenTransactionTypeForSubscriptionChanges_requestDataIsLogged(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );
			$this->storedDonations()->newStoredIncompletePayPalDonation();

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
		} );
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

	private function newPendingPaymentParams(): array {
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

	private function newCancelPaymentParams(): array {
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
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
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
		} );
	}

	public function testDonationIsNotFound_andCreationFails_logsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
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
		} );
	}

	public function testOnInternalError_applicationIndicatesError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$repository = new ThrowingDonationRepository();
			$repository->throwOnGetDonationById();
			$factory->setDonationRepository( $repository );

			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 500, $client->getResponse()->getStatusCode() );
			$this->assertStringContainsString( "Could not get donation", $client->getResponse()->getContent() );
		} );
	}

	public function testOnInternalError_applicationLogsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->setSucceedingDonationTokenGenerator( $factory );
			$factory->setVerificationServiceFactory( new SucceedingVerificationServiceFactory() );

			$repository = new ThrowingDonationRepository();
			$repository->throwOnGetDonationById();
			$factory->setDonationRepository( $repository );

			$logger = new LoggerSpy();
			$factory->setPaypalLogger( $logger );

			$this->storedDonations()->newStoredIncompletePayPalDonation();

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 1, $logger->getLogCalls()->count() );
			$firstCallContext = $logger->getFirstLogCall()->getContext();
			$this->assertArrayHasKey( 'stacktrace', $firstCallContext );
			$this->assertArrayHasKey( 'post_vars', $firstCallContext );
			$this->assertSame( 'An Exception happened: Could not get donation', $logger->getFirstLogCall()->getMessage() );
		} );
	}

	private function newValidRequestParametersWithNegativeTransactionFee(): array {
		$parameters = $this->newHttpParamsForPayment();
		$parameters['mc_fee'] = '-12.34';
		$parameters['payment_status'] = 'Refunded';
		return $parameters;
	}

	private function setSucceedingDonationTokenGenerator( FunFunFactory $factory ): void {
		$factory->setDonationTokenGenerator(
			new FixedTokenGenerator(
				self::UPDATE_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			)
		);
	}

}
