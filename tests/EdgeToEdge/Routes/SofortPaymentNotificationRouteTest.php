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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$donation = ValidDonation::newIncompletePayPalDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => self::VALID_TRANSACTION_TIME
				]
			);

			$this->assertSame( 'Bad request', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenWrongToken_applicationRefuses(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::INVALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => self::VALID_TRANSACTION_TIME
				]
			);

			$this->assertSame( 'Bad request', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenBadTimeFormat_applicationRefuses(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::INVALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => 'now'
				]
			);

			$this->assertSame( 'Bad request', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$donation = ValidDonation::newIncompleteSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => self::VALID_TRANSACTION_TIME
				]
			);

			$this->assertSame( 'Ok', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_OK, $client->getResponse()->getStatusCode() );

			$donation = $factory->getDonationRepository()->getDonationById( $donation->getId() );

			$this->assertEquals( new DateTime( self::VALID_TRANSACTION_TIME ), $donation->getPaymentMethod()->getConfirmedAt() );
		} );
	}

	public function testGivenAlreadyConfirmedPayment_requestDataIsLogged(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

			$logger = new LoggerSpy();
			$factory->setSofortLogger( $logger );

			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			) );

			$donation = ValidDonation::newCompletedSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . $donation->getId() . '&updateToken=' . self::VALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => self::VALID_TRANSACTION_TIME
				]
			);

			$this->assertSame( 'Bad request', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode() );

			$this->assertSame(
				[ 'Duplicate notification' ],
				$logger->getLogCalls()->getMessages()
			);

			$this->assertSame(
				self::VALID_TRANSACTION_ID,
				$logger->getLogCalls()->getFirstCall()->getContext()['post_vars']['transaction']
			);

			$this->assertEquals(
				$donation->getId(),
				$logger->getLogCalls()->getFirstCall()->getContext()['query_vars']['id']
			);

			$this->assertSame( LogLevel::INFO, $logger->getLogCalls()->getFirstCall()->getLevel() );
		} );
	}

	public function testGivenUnknownDonation_requestDataIsLogged(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {

			$logger = new LoggerSpy();
			$factory->setSofortLogger( $logger );

			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::VALID_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			) );

			$donation = ValidDonation::newCompletedSofortDonation();
			$factory->getDonationRepository()->storeDonation( $donation );

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=' . ( $donation->getId() + 1 ) . '&updateToken=' . self::VALID_TOKEN,
				[
					'transaction' => self::VALID_TRANSACTION_ID,
					'time' => self::VALID_TRANSACTION_TIME
				]
			);

			$this->assertSame( 'Error', $client->getResponse()->getContent() );
			$this->assertSame( Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode() );

			$this->assertSame(
				[ 'Donation not found' ],
				$logger->getLogCalls()->getMessages()
			);

			$this->assertSame(
				self::VALID_TRANSACTION_ID,
				$logger->getLogCalls()->getFirstCall()->getContext()['post_vars']['transaction']
			);

			$this->assertEquals(
				$donation->getId() + 1,
				$logger->getLogCalls()->getFirstCall()->getContext()['query_vars']['id']
			);

			$this->assertSame( LogLevel::ERROR, $logger->getLogCalls()->getFirstCall()->getLevel() );
		} );
	}
}
