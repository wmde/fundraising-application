<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\App\Controllers\Payment\SofortNotificationController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;

#[CoversClass( SofortNotificationController::class )]
class SofortPaymentNotificationRouteTest extends WebRouteTestCase {

	private const VALID_TOKEN = 'my-secret_token';
	private const INVALID_TOKEN = 'fffffggggg';

	private const VALID_TRANSACTION_ID = '99999-53245-5483-4891';

	/**
	 * Time string that comes from Sofort's API
	 */
	private const VALID_TRANSACTION_TIME = '2010-04-14T19:01:08+02:00';

	/**
	 * Time string expected to be in the database. Implied timezone is UTC
	 */
	private const VALID_TRANSACTION_VALUATION_DATE = '2010-04-14 17:01:08';

	public function storedDonations(): StoredDonations {
		return new StoredDonations( $this->getFactory() );
	}

	public function testGivenWrongBookingData_applicationRefuses(): void {
		$client = $this->createClient();
		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			'{"not":"real","proper":"data"}'
		);

		$this->assertIsBadRequestResponse( $client->getResponse() );
	}

	public function testGivenWrongBookingData_applicationLogs(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$logger = $this->getLogger( $factory );

		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			'{"not":"real","proper":"data"}'
		);

		$this->assertErrorCauseIsLogged( $logger, 'An Exception happened: Invalid notification request' );
		$this->assertRequestVarsAreLogged( $logger, '{"not":"real","proper":"data"}' );
		$this->assertLogLevel( $logger, LogLevel::ERROR );
	}

	private function assertIsBadRequestResponse( Response $response ): void {
		$this->assertSame( 'Bad request', $response->getContent() );
		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
	}

	public function testGivenWrongToken_applicationRefuses(): void {
		$client = $this->createClient();
		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::INVALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertIsBadRequestResponse( $client->getResponse() );
	}

	public function testGivenWrongToken_applicationLogs(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$logger = $this->getLogger( $factory );

		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::INVALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertErrorCauseIsLogged( $logger, 'Wrong access code for donation' );
		$this->assertRequestVarsAreLogged( logger: $logger, token: self::INVALID_TOKEN );
		$this->assertLogLevel( $logger, LogLevel::ERROR );
	}

	public function testGivenBadTimeFormat_applicationRefuses(): void {
		$client = $this->createClient();
		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, 'now' )
		);

		$this->assertIsBadRequestResponse( $client->getResponse() );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation( self::VALID_TOKEN );

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertSuccessResponse( $client->getResponse() );

		/** @var SofortPayment $paymentMethod */
		$paymentMethod = $factory->getPaymentRepository()->getPaymentById( $donation->getPaymentId() );
		$this->assertEquals(
			self::VALID_TRANSACTION_VALUATION_DATE,
			$paymentMethod->getDisplayValues()['valuationDate']
		);
	}

	public function testGivenValidRequest_donationStateIsChangedToBooked(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation( self::VALID_TOKEN );

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertTrue(
			$factory->getPaymentRepository()->getPaymentById( $donation->getPaymentId() )->isCompleted()
		);
	}

	public function testGivenAlreadyConfirmedPayment_requestDataIsLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$logger = $this->getLogger( $factory );

		$donation = $this->storedDonations()->newStoredCompleteSofortDonation( self::VALID_TOKEN );

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertSuccessResponse( $client->getResponse() );
		$this->assertErrorCauseIsLogged( $logger, 'Payment was booked before' );
		$this->assertRequestVarsAreLogged( $logger );
		$this->assertLogLevel( $logger, LogLevel::ERROR );
	}

	private function assertErrorCauseIsLogged( LoggerSpy $logger, string $expectedMessage ): void {
		$this->assertSame(
			[ $expectedMessage ],
			$logger->getLogCalls()->getMessages()
		);
	}

	private function assertRequestVarsAreLogged( LoggerSpy $logger, ?string $logContent = null, ?string $token = null ): void {
		$context = $logger->getFirstLogCall()->getContext();
		$this->assertStringContainsString(
			$logContent ?? '<transaction>' . self::VALID_TRANSACTION_ID . '</transaction>',
			$context['request_content']
		);

		$this->assertEquals(
			$token ?? self::VALID_TOKEN,
			$context['query_vars']['updateToken']
		);
	}

	private function assertLogLevel( LoggerSpy $logger, string $expectedLevel ): void {
		$this->assertSame( $expectedLevel, $logger->getFirstLogCall()->getLevel() );
	}

	public function testGivenUnknownDonation_requestDataIsLogged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$logger = $this->getLogger( $factory );

		$donation = $this->storedDonations()->newStoredCompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . ( $donation->getId() + 1 ) . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertIsErrorResponse( $client->getResponse() );
		$this->assertErrorCauseIsLogged( $logger, 'Donation not found' );
		$this->assertRequestVarsAreLogged( $logger );
		$this->assertLogLevel( $logger, LogLevel::ERROR );
	}

	private function assertIsErrorResponse( Response $response ): void {
		$this->assertSame( 'Error', $response->getContent() );
		$this->assertSame( Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode() );
	}

	public function testGivenBadTimeStamp_requestDataIsLoggedAndResponseIsOk(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$logger = $this->getLogger( $factory );

		$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, 'not a timestamp' )
		);

		$this->assertIsBadRequestResponse( $client->getResponse() );
		$this->assertErrorCauseIsLogged( $logger, 'An Exception happened: Invalid notification request' );
		$this->assertRequestVarsAreLogged( $logger );
		$this->assertLogLevel( $logger, LogLevel::ERROR );
	}

	public function testOnInternalError_applicationReturnsErrorResponse(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$repository = new ThrowingDonationRepository();
		$repository->throwOnGetDonationById();
		$factory->setDonationRepository( $repository );

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . 1 . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertSame( 500, $client->getResponse()->getStatusCode() );
		$this->assertStringContainsString( "Error", $client->getResponse()->getContent() ?: '' );
	}

	public function testOnInternalError_applicationLogsError(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$repository = new ThrowingDonationRepository();
		$repository->throwOnGetDonationById();
		$factory->setDonationRepository( $repository );

		$logger = new LoggerSpy();
		$factory->setSofortLogger( $logger );

		$client->request(
			Request::METHOD_POST,
			'/sofort-payment-notification?id=' . 1 . '&updateToken=' . self::VALID_TOKEN,
			[],
			[],
			[],
			$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
		);

		$this->assertCount( 1, $logger->getLogCalls() );
		$firstCallContext = $logger->getFirstLogCall()->getContext();
		$this->assertArrayHasKey( 'stacktrace', $firstCallContext );
		$this->assertArrayHasKey( 'request_content', $firstCallContext );
		$this->assertArrayHasKey( 'query_vars', $firstCallContext );
		$this->assertSame( 'An Exception happened: Could not get donation', $logger->getFirstLogCall()->getMessage() );
	}

	private function getLogger( FunFunFactory $factory ): LoggerSpy {
		$logger = new LoggerSpy();
		$factory->setSofortLogger( $logger );
		return $logger;
	}

	private function buildRawRequestBody( string $transactionId, string $time ): string {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
			. "<status_notification><transaction>$transactionId</transaction>"
			. "<time>$time</time>"
			. '</status_notification>';
	}

	private function assertSuccessResponse( Response $response ): void {
		$this->assertSame( 'Ok', $response->getContent() );
		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
	}

}
