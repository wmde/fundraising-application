<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\PsrLogTestDoubles\LoggerSpy;

class SofortPaymentNotificationRouteTest extends WebRouteTestCase {

	private const VALID_TOKEN = 'my-secret_token';
	private const INVALID_TOKEN = 'fffffggggg';

	private const VALID_TRANSACTION_ID = '99999-53245-5483-4891';
	private const VALID_TRANSACTION_TIME = '2010-04-14T19:01:08+02:00';

	public function testGivenWrongPaymentType_applicationRefuses(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ValidDonation::newIncompletePayPalDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[],
				[],
				[],
				$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
			);

			$this->assertIsBadRequestResponse( $client->getResponse() );
		} );
	}

	private function newEnvironment( callable $onEnvironmentCreated ): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ) use ( $onEnvironmentCreated ): void {
				$factory->setTokenGenerator( new FixedTokenGenerator(
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
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

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

	public function testGivenBadTimeFormat_applicationRefuses(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

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
			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

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

			$this->assertEquals(
				new DateTime( self::VALID_TRANSACTION_TIME ),
				$factory->getDonationRepository()->getDonationById( $donation->getId() )->getPaymentMethod()->getConfirmedAt()
			);
		} );
	}

	public function testGivenValidRequest_donationStateIsChangedToBooked(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[],
				[],
				[],
				$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
			);

			$this->assertSame(
				Donation::STATUS_EXTERNAL_BOOKED,
				$factory->getDonationRepository()->getDonationById( $donation->getId() )->getStatus()
			);
		} );
	}

	public function testGivenAlreadyConfirmedPayment_requestDataIsLogged(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setSofortLogger( $logger );

			$donation = ValidDonation::newCompletedSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[],
				[],
				[],
				$this->buildRawRequestBody( self::VALID_TRANSACTION_ID, self::VALID_TRANSACTION_TIME )
			);

			$this->assertIsBadRequestResponse( $client->getResponse() );
			$this->assertErrorCauseIsLogged( $logger, 'Duplicate notification' );
			$this->assertRequestVarsAreLogged( $logger );
			$this->assertLogLevel( $logger, LogLevel::INFO );
		} );
	}

	private function assertErrorCauseIsLogged( LoggerSpy $logger, string $expectedMessage ): void {
		$this->assertSame(
			[ $expectedMessage ],
			$logger->getLogCalls()->getMessages()
		);
	}

	private function assertRequestVarsAreLogged( LoggerSpy $logger ): void {
		$this->assertContains(
			'<transaction>' . self::VALID_TRANSACTION_ID . '</transaction>',
			$logger->getLogCalls()->getFirstCall()->getContext()['request_content']
		);

		$this->assertEquals(
			self::VALID_TOKEN,
			$logger->getLogCalls()->getFirstCall()->getContext()['query_vars']['updateToken']
		);
	}

	private function assertLogLevel( LoggerSpy $logger, string $expectedLevel ): void {
		$this->assertSame( $expectedLevel, $logger->getLogCalls()->getFirstCall()->getLevel() );
	}

	public function testGivenUnknownDonation_requestDataIsLogged(): void {
		$this->newEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$logger = new LoggerSpy();
			$factory->setSofortLogger( $logger );

			$donation = ValidDonation::newCompletedSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

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
			$logger = new LoggerSpy();
			$factory->setSofortLogger( $logger );

			$donation = ValidDonation::newCompletedSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

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

	private function buildRawRequestBody( string $transactionId, string $time ): string {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n"
			. "<status_notification><transaction>$transactionId</transaction>"
			. "<time>$time</time>"
			. '</status_notification>';
	}

}
