<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

class SofortPaymentNotificationRouteTest extends WebRouteTestCase {

	public function testGivenWrongPaymentType_applicationRefuses(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				'my-secret_token',
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$repo = $factory->getDonationRepository();
			$repo->storeDonation( ValidDonation::newIncompletePayPalDonation() );	// creates donation w/ id=1

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=1&updateToken=my-secret_token',
				[
					'transaction' => '99999-53245-5483-4891',
					'time' => '2010-04-14T19:01:08+02:00'
				]
			);

			$this->assertSame( 400, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenWrongToken_applicationRefuses(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				'my_secret_token',
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$repo = $factory->getDonationRepository();
			$repo->storeDonation( ValidDonation::newIncompleteSofortDonation() );	// creates donation w/ id=1

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=1&updateToken=some_bogous',
				[
					'transaction' => '99999-53245-5483-4891',
					'time' => '2010-04-14T19:01:08+02:00'
				]
			);

			$this->assertSame( 400, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenBadTimeFormat_applicationRefuses(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				'my_secret_token',
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$repo = $factory->getDonationRepository();
			$repo->storeDonation( ValidDonation::newIncompleteSofortDonation() );	// creates donation w/ id=1

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=1&updateToken=some_bogous',
				[
					'transaction' => '99999-53245-5483-4891',
					'time' => 'now'
				]
			);

			$this->assertSame( 400, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				'my_secret-token',
				new DateTime( '2039-12-31 23:59:59Z' )
			) );

			$repo = $factory->getDonationRepository();
			$repo->storeDonation( ValidDonation::newIncompleteSofortDonation() );	// creates donation w/ id=1

			$client->request(
				Request::METHOD_POST,
				'/sofort-payment-notification?id=1&updateToken=my_secret-token',
				[
					'transaction' => '99999-53245-5483-4891',
					'time' => '2010-04-14T19:01:08+02:00'
				]
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );

			$donation = $repo->getDonationById( 1 );
			$this->assertEquals( new DateTime( '2010-04-14T19:01:08+02:00' ), $donation->getPaymentMethod()->getConfirmedAt() );
		} );
	}
}
