<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class HandlePayPalPaymentNotificationRouteTest extends WebRouteTestCase {

	const BASE_URL = 'https://that.paymentprovider.com/';
	const EMAIL_ADDRESS = 'foerderpp@wikimedia.de';
	const ITEM_NAME = 'My preciousss';
	const UPDATE_TOKEN = 'my_secret_token';
	const DONATION_ID = 1;
	const VALID_VERIFICATION_RESPONSE = 'VERIFIED';
	const FAILING_VERIFICATION_RESPONSE = 'FAIL';
	private const PATH = '/handle-paypal-payment-notification';

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator(
				self::UPDATE_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			) );

			$factory->getDonationRepository()->storeDonation( ValidDonation::newIncompletePayPalDonation() );

			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $this->newHttpParamsForPayment() )
			);

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $factory->getDonationRepository(), $this->newHttpParamsForPayment() );
		} );
	}

	private function newNonNetworkUsingNotificationVerifier( array $requestParams ): PayPalPaymentNotificationVerifier {
		return new PayPalPaymentNotificationVerifier(
			$this->newGuzzleClientMock( self::VALID_VERIFICATION_RESPONSE, $requestParams ),
			self::BASE_URL,
			self::EMAIL_ADDRESS
		);
	}

	private function newFailingNotifierMock(): PayPalPaymentNotificationVerifier {
		return new PayPalPaymentNotificationVerifier(
			$this->newGuzzleClientMock( self::FAILING_VERIFICATION_RESPONSE, $this->newHttpParamsForPayment() ),
			self::BASE_URL,
			self::EMAIL_ADDRESS
		);
	}

	private function newGuzzleClientMock( string $responseBody, array $requestParams ): GuzzleClient {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->any() )
			->method( 'getContents' )
			->willReturn( $responseBody );

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
				[ 'form_params' => array_merge( $requestParams, [ 'cmd' => '_notify-validate' ] ) ]
			)
			->willReturn( $response );

		return $client;
	}

	private function assertPayPalDataGotPersisted( DonationRepository $donationRepo, array $request ): void {
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
			'custom' => '{"id": "1", "utoken": "my_secret_token"}',
			'txn_id' => '61E67681CH3238416',
			'payment_type' => 'instant',
			'txn_type' => 'express_checkout',
			'payment_date' => '20:12:59 Jan 13, 2009 PST',
		];
	}

	public function testGivenInvalidReceiverEmail_applicationReturnsError(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $this->newHttpParamsForPayment() )
			);

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'receiver_email' => 'mr.robot@evilcorp.com',
					'payment_status' => 'Completed'
				]
			);

			$this->assertSame( 'Payment receiver address does not match', $client->getResponse()->getContent() );
			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
		} );
	}

	/**
	 * @dataProvider unsupportedPaymentStatusProvider
	 */
	public function testGivenUnsupportedPaymentStatus_applicationReturnsOK( array $params ): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) use ( $params ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $params )
			);

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$params
			);

			$this->assertSame( '', $client->getResponse()->getContent() );
			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
		} );
	}

	public function unsupportedPaymentStatusProvider(): \Iterator {
		yield [ $this->newPendingPaymentParams() ];
		yield [ $this->newCancelPaymentParams() ];
	}

	public function testGivenUnsupportedPaymentStatus_requestDataIsLogged(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $this->newPendingPaymentParams() )
			);

			$logger = new LoggerSpy();
			$factory->setPaypalLogger( $logger );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newPendingPaymentParams()
			);

			$this->assertSame(
				[ 'PayPal request not handled' ],
				$logger->getLogCalls()->getMessages()
			);

			$this->assertSame(
				'Pending',
				$logger->getLogCalls()->getFirstCall()->getContext()['post_vars']['payment_status']
			);
		} );
	}

	public function testGivenFailingVerification_applicationReturnsError(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newFailingNotifierMock()
			);

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newHttpParamsForPayment()
			);

			$this->assertSame( 'An error occurred while trying to confirm the sent data', $client->getResponse()->getContent() );
			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testGivenUnsupportedCurrency_applicationReturnsError(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $this->newHttpParamsForPayment() )
			);

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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier(
				$this->newNonNetworkUsingNotificationVerifier( $this->newSubscriptionModificationParams() )
			);
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

	public function testGivenNegativeTransactionFee_exceptionIsThrown(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setPayPalPaymentNotificationVerifier( $this->newSucceedingNotificationVerifier() );

			$logger = new LoggerSpy();
			$factory->setPaypalLogger( $logger );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				$this->newValidRequestParametersWithNegativeTransactionFee()
			);

			$this->assertSame(
				[ 'PayPal request not handled' ],
				$logger->getLogCalls()->getMessages()
			);
		} );
	}

	private function newValidRequestParametersWithNegativeTransactionFee(): array {
		$parameters = $this->newHttpParamsForPayment();
		$parameters['mc_fee'] = '-12.34';
		$parameters['payment_status'] = 'Refunded';
		return $parameters;
	}

	private function newSucceedingNotificationVerifier(): PaymentNotificationVerifier {
		$verifier = $this->createMock( PaymentNotificationVerifier::class );

		$verifier->expects( $this->any() )->method( 'verify' )->willReturn( true );

		return $verifier;
	}

}
