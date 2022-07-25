<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Payment\SofortNotificationController
 */
class SofortPaymentNotificationRouteTest extends WebRouteTestCase {

	private const VALID_TOKEN = 'my-secret_token';
	private const INVALID_TOKEN = 'fffffggggg';

	private const VALID_TRANSACTION_ID = '99999-53245-5483-4891';
	private const VALID_TRANSACTION_TIME = '2010-04-14T19:01:08+02:00';
	private const VALID_TRANSACTION_DATETIME = '2010-04-14 19:01:08';

	public function storedDonations(): StoredDonations {
		return new StoredDonations( $this->getFactory() );
	}

	public function testGivenWrongBookingData_applicationRefuses(): void {
		$this->newEnvironment( function ( Client $client ): void {
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
		} );
	}

	public function testGivenWrongBookingData_applicationLogs(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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

			$this->assertErrorCauseIsLogged( $logger, 'Invalid notification request' );
			$this->assertRequestVarsAreLogged( $logger, '{"not":"real","proper":"data"}' );
			$this->assertLogLevel( $logger, LogLevel::ERROR );
		} );
	}

	private function newEnvironment( callable $onEnvironmentCreated ): void {
		$this->createEnvironment(
			static function ( Client $client, FunFunFactory $factory ) use ( $onEnvironmentCreated ): void {
				$factory->setDonationTokenGenerator( new FixedTokenGenerator(
					self::VALID_TOKEN,
					new DateTime( '2039-12-31 23:59:59Z' )
				) );

				$onEnvironmentCreated( $client, $factory );
			}
		);
	}

	private function assertIsBadRequestResponse( Response $response ): void {
		$this->assertSame( 'Bad request', $response->getContent() );
		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
	}

	public function testGivenWrongToken_applicationRefuses(): void {
		$this->newEnvironment( function ( Client $client ): void {
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
		} );
	}

	public function testGivenWrongToken_applicationLogs(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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
		} );
	}

	public function testGivenBadTimeFormat_applicationRefuses(): void {
		$this->newEnvironment( function ( Client $client ): void {
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
		} );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[],
				[],
				[],
				$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
			);

			$this->assertSame( 'Ok', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_OK, $client->getResponse()->getStatusCode() );

			/** @var SofortPayment $paymentMethod */
			$paymentMethod = $factory->getPaymentRepository()->getPaymentById( $donation->getPaymentId() );
			$this->assertEquals(
				self::VALID_TRANSACTION_DATETIME,
				$paymentMethod->getDisplayValues()['valuationDate']
			);
		} );
	}

	public function testGivenValidRequest_donationStateIsChangedToBooked(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->storedDonations()->newStoredIncompleteSofortDonation();

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
		} );
	}

	public function testGivenAlreadyConfirmedPayment_requestDataIsLogged(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = $this->getLogger( $factory );

			$donation = $this->storedDonations()->newStoredCompleteSofortDonation();

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[],
				[],
				[],
				$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
			);

			$this->assertIsBadRequestResponse( $client->getResponse() );
			$this->assertErrorCauseIsLogged( $logger, 'Payment is already completed' );
			$this->assertRequestVarsAreLogged( $logger );
			$this->assertLogLevel( $logger, LogLevel::ERROR );
		} );
	}

	private function assertErrorCauseIsLogged( LoggerSpy $logger, string $expectedMessage ): void {
		$this->assertSame(
			[ $expectedMessage ],
			$logger->getLogCalls()->getMessages()
		);
	}

	private function assertRequestVarsAreLogged( LoggerSpy $logger, ?string $logContent = null, ?string $token = null ): void {
		$this->assertStringContainsString(
			$logContent ?? '<transaction>' . self::VALID_TRANSACTION_ID . '</transaction>',
			$logger->getLogCalls()->getFirstCall()->getContext()['request_content']
		);

		$this->assertEquals(
			$token ?? self::VALID_TOKEN,
			$logger->getLogCalls()->getFirstCall()->getContext()['query_vars']['updateToken']
		);
	}

	private function assertLogLevel( LoggerSpy $logger, string $expectedLevel ): void {
		$this->assertSame( $expectedLevel, $logger->getLogCalls()->getFirstCall()->getLevel() );
	}

	public function testGivenUnknownDonation_requestDataIsLogged(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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
		} );
	}

	private function assertIsErrorResponse( Response $response ): void {
		$this->assertSame( 'Error', $response->getContent() );
		$this->assertSame( Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode() );
	}

	public function testGivenBadTime_requestDataIsLogged(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = $this->getLogger( $factory );

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
			$this->assertErrorCauseIsLogged( $logger, 'Invalid notification request' );
			$this->assertRequestVarsAreLogged( $logger );
			$this->assertLogLevel( $logger, LogLevel::ERROR );
		} );
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

}
