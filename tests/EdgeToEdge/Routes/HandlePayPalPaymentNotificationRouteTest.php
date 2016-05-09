<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\DataAccess\DoctrineDonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\LoggingPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalPaymentNotificationRouteTest extends WebRouteTestCase {

	const BASE_URL = 'https://that.paymentprovider.com/';
	const EMAIL_ADDRESS = 'foerderpp@wikimedia.de';
	const ITEM_NAME = 'My preciousss';
	const UPDATE_TOKEN = 'my_secret_token';
	const DONATION_ID = 1;

	public function testGivenValidRequest_applicationIndicatesSuccess() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->getDonationRepository()->storeDonation( ValidDonation::newIncompletePayPalDonation() );
			$authorizer = new DoctrineDonationAuthorizationUpdater( $factory->getEntityManager() );
			$authorizer->allowModificationViaToken(
				self::DONATION_ID,
				self::UPDATE_TOKEN,
				DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			);
			$factory->setPayPalPaymentNotificationVerifier(
				new LoggingPaymentNotificationVerifier( $this->newNotifierMock(), $factory->getLogger() )
			);

			$client->request(
				'POST',
				'/handle-paypal-payment-notification',
				$this->newRequest()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $factory->getDonationRepository(), $this->newRequest() );
		} );
	}

	private function newNotifierMock() {
		return new PayPalPaymentNotificationVerifier(
			$this->newGuzzleClientMock(),
			[
				'base-url' => self::BASE_URL,
				'account-address' => self::EMAIL_ADDRESS,
				'item-name' => self::ITEM_NAME
			]
		);
	}

	private function newGuzzleClientMock(): GuzzleClient {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->any() )
			->method( 'getContents' )
			->willReturn( 'VERIFIED' );

		$response = $this->getMockBuilder( Response::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getBody' ] )
			->getMock();

		$response->expects( $this->any() )
			->method( 'getBody' )
			->willReturn( $body );

		$client = $this->getMockBuilder( GuzzleClient::class )
			->disableOriginalConstructor()
			->setMethods( [ 'post' ] )
			->getMock();

		$client->expects( $this->any() )
			->method( 'post' )
			->with(
				self::BASE_URL,
				[
					'cmd' => '_notify_validate',
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
					'item_name' => 'My preciousss',
					'item_number' => 1,
					'custom' => '{"id": "1", "utoken": "my_secret_token"}',
					'txn_id' => '61E67681CH3238416',
					'payment_type' => 'instant',
					'txn_type' => 'express_checkout',
					'payment_date' => '20:12:59 Jan 13, 2009 PST',
				]
			)
			->willReturn( $response );

		return $client;
	}

	private function assertPayPalDataGotPersisted( DonationRepository $donationRepo, array $request ) {
		$donation = $donationRepo->getDonationById( self::DONATION_ID );

		/** @var PayPalPayment $paymentMethod */
		$paymentMethod = $donation->getPayment()->getPaymentMethod();
		$pplData = $paymentMethod->getPayPalData();

		$this->assertSame( $request['payer_id'], $pplData->getPayerId() );
		$this->assertSame( $request['subscr_id'], $pplData->getSubscriberId() );
		$this->assertSame( $request['payer_status'], $pplData->getPayerStatus() );
		$this->assertSame( $request['first_name'], $pplData->getFirstName() );
		$this->assertSame( $request['last_name'], $pplData->getLastName() );
		$this->assertSame( $request['address_name'], $pplData->getAddressName() );
		$this->assertSame( $request['address_status'], $pplData->getAddressStatus() );
		$this->assertSame( $request['mc_currency'], $pplData->getCurrencyCode() );
		$this->assertSame( $request['mc_fee'], $pplData->getFee()->getEuroString() );
		$this->assertSame( $request['mc_gross'], $pplData->getAmount()->getEuroString() );
		$this->assertSame( $request['settle_amount'], $pplData->getSettleAmount()->getEuroString() );

		$this->assertSame( $request['txn_id'], $pplData->getPaymentId() );
		$this->assertSame( $request['payment_type'], $pplData->getPaymentType() );
		$this->assertSame( $request['payment_status'] . '/' . $request['txn_type'], $pplData->getPaymentStatus() );
		$this->assertSame( $request['payer_id'], $pplData->getPayerId() );
		$this->assertSame( $request['payment_date'], $pplData->getPaymentTimestamp() );
		$this->assertSame( $request['subscr_id'], $pplData->getSubscriberId() );
	}

	private function newRequest() {
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
			'txn_type' => 'express_checkout',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	public function testGivenInvalidRequest_applicationReturnsError() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setPayPalPaymentNotificationVerifier(
				new LoggingPaymentNotificationVerifier( $this->newNotifierMock(), $factory->getLogger() )
			);

			$client->request(
				'POST',
				'/handle-paypal-payment-notification',
				[
					'receiver_email' => self::EMAIL_ADDRESS,
					'payment_status' => 'Unknown'
				]
			);

			$this->assertSame( '', $client->getResponse()->getContent() );
			$this->assertSame( 500, $client->getResponse()->getStatusCode() );
		} );
	}

}
