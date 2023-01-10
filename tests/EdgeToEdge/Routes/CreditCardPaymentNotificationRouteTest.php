<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\PaymentContext\Domain\PaymentRepository;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Payment\CreditCardPaymentNotificationController
 */
class CreditCardPaymentNotificationRouteTest extends WebRouteTestCase {

	private const FUNCTION = 'billing';
	private const DONATION_ID = 1;
	private const TRANSACTION_ID = 'customer.prefix-ID2tbnag4a9u';
	private const CUSTOMER_ID = 'e20fb9d5281c1bca1901c19f6e46213191bb4c17';
	private const SESSION_ID = 'CC13064b2620f4028b7d340e3449676213336a4d';
	private const AUTH_ID = 'd1d6fae40cf96af52477a9e521558ab7';
	private const ACCESS_TOKEN = 'my_secret_access_token';
	private const UPDATE_TOKEN = 'my_secret_update_token';
	private const TITLE = 'Your generous donation';
	private const COUNTRY_CODE = 'DE';
	private const CURRENCY_CODE = 'EUR';
	private const STATUS = 'processed';

	private const PATH = '/handle-creditcard-payment-notification';

	public function testGivenInvalidRequest_applicationIndicatesError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setCreditCardLogger( $logger );

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[ 'function' => 'BAD FUNCTION' ]
			);

			$this->assertSame( 1, $logger->getLogCalls()->count() );
			$this->assertStringContainsString( "status=error\n", $client->getResponse()->getContent() );
			$this->assertStringContainsString( 'msg=', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenNonBillingRequest_applicationIndicatesError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'function' => 'error',
					'errorcode' => 'ipg04',
					'errormessage' => 'Card used is not permitted',
				]
			);
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertStringContainsString( "status=error\n", $client->getResponse()->getContent() );
			$this->assertStringContainsString( 'msg=Function "error" not supported by this end point', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenNonBillingRequest_applicationLogsRequest(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setCreditCardLogger( $logger );
			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'function' => 'error',
					'errorcode' => 'ipg04',
					'errormessage' => 'Card used is not permitted',
				]
			);

			$this->assertSame( 1, $logger->getLogCalls()->count() );
			$firstCallContext = $logger->getFirstLogCall()->getContext();
			$this->assertSame( 'ipg04', $firstCallContext['errorcode'] );
			$this->assertSame( 'Card used is not permitted', $firstCallContext['errormessage'] );
		} );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->storedDonations()->newStoredIncompleteCreditCardDonation( self::UPDATE_TOKEN );

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				$this->newRequest()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertStringContainsString( "status=ok\n", $client->getResponse()->getContent() );
			$this->assertStringContainsString(
				"url=http://my.donation.app/show-donation-confirmation?id=1&accessToken=my_secret_access_token\n",
				$client->getResponse()->getContent()
			);
			$this->assertCreditCardDataGotPersisted(
				$factory->getDonationRepository(),
				$factory->getPaymentRepository(),
				$this->newRequest()
			);
		} );
	}

	public function testGivenRequestForMissingDonation_applicationIndicatesError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$client->request(
				Request::METHOD_GET,
				self::PATH,
				$this->newRequest()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertStringContainsString( "status=error\n", $client->getResponse()->getContent() );
			$this->assertStringContainsString( "msg=Donation not found\n", $client->getResponse()->getContent() );
		} );
	}

	public function testGivenRequestForMissingDonation_applicationLogsRequest(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setLogger( $logger );
			$requestData = $this->newRequest();

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				$requestData
			);

			$this->assertSame( 1, $logger->getLogCalls()->count() );
			$firstCallContext = $logger->getFirstLogCall()->getContext();
			$requestData['amount'] = (string)$requestData['amount'];
			$this->assertSame( $requestData, $firstCallContext['queryParams'] );
			$this->assertSame( 'Credit Card Notification Error: Donation not found', $logger->getFirstLogCall()->getMessage() );
		} );
	}

	public function testOnInternalError_applicationIndicatesError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$repository = new ThrowingDonationRepository();
			$repository->throwOnGetDonationById();
			$factory->setDonationRepository( $repository );

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				$this->newRequest()
			);

			$this->assertSame( 500, $client->getResponse()->getStatusCode() );
			$this->assertStringContainsString( "Could not get donation", $client->getResponse()->getContent() );
		} );
	}

	public function testOnInternalError_applicationLogsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setLogger( $logger );

			$repository = new ThrowingDonationRepository();
			$repository->throwOnGetDonationById();
			$factory->setDonationRepository( $repository );

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				$this->newRequest()
			);

			$this->assertSame( 1, $logger->getLogCalls()->count() );
			$firstCallContext = $logger->getFirstLogCall()->getContext();
			$this->assertArrayHasKey( 'stacktrace', $firstCallContext );
			$this->assertSame( 'An Exception happened: Could not get donation', $logger->getFirstLogCall()->getMessage() );
		} );
	}

	private function newRequest(): array {
		return [
			'function' => self::FUNCTION,
			'donation_id' => (string)self::DONATION_ID,
			// Amount should match ValidDonation::DONATION_AMOUNT
			'amount' => 1337,
			'transactionId' => self::TRANSACTION_ID,
			'customerId' => self::CUSTOMER_ID,
			'sessionId' => self::SESSION_ID,
			'auth' => self::AUTH_ID,
			'utoken' => self::UPDATE_TOKEN,
			'token' => self::ACCESS_TOKEN,
			'title' => self::TITLE,
			'country' => self::COUNTRY_CODE,
			'currency' => self::CURRENCY_CODE,
		];
	}

	private function assertCreditCardDataGotPersisted( DonationRepository $donationRepo, PaymentRepository $paymentRepository, array $request ): void {
		$donation = $donationRepo->getDonationById( self::DONATION_ID );
		$ccData = $paymentRepository->getPaymentById( $donation->getPaymentId() )->getLegacyData();

		$this->assertSame( $request['currency'], $ccData->paymentSpecificValues['mcp_currency'] );
		$this->assertSame( $request['amount'], $ccData->amountInEuroCents );
		$this->assertSame( $request['country'], $ccData->paymentSpecificValues['mcp_country'] );
		$this->assertSame( $request['auth'], $ccData->paymentSpecificValues['mcp_auth'] );
		$this->assertSame( $request['title'], $ccData->paymentSpecificValues['mcp_title'] );
		$this->assertSame( $request['sessionId'], $ccData->paymentSpecificValues['mcp_sessionid'] );
		$this->assertSame( $request['transactionId'], $ccData->paymentSpecificValues['ext_payment_id'] );
		$this->assertSame( self::STATUS, $ccData->paymentSpecificValues['ext_payment_status'] );
		$this->assertSame( $request['customerId'], $ccData->paymentSpecificValues['ext_payment_account'] );
		$this->assertNotEmpty( $ccData->paymentSpecificValues['ext_payment_timestamp'] );
	}

	private function storedDonations(): StoredDonations {
		return new StoredDonations( $this->getFactory() );
	}

}
