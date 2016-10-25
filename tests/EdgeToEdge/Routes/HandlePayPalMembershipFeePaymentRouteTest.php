<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifier;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class HandlePayPalMembershipFeePaymentRouteTest extends WebRouteTestCase {

	const MEMBERSHIP_APPLICATION_ID = 1;
	const UPDATE_TOKEN = 'some token';
	const BASE_URL = 'https://that.paymentprovider.com/';
	const EMAIL_ADDRESS = 'paypaldev-facilitator@wikimedia.de';
	const ITEM_NAME = 'Your membership';
	const VERIFICATION_SUCCESSFUL = 'VERIFIED';
	const VERIFICATION_FAILED = 'INVALID';

	public function testGivenValidSubscriptionSignupRequest_applicationIndicatesSuccess() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setTokenGenerator( new FixedTokenGenerator(
				self::UPDATE_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			) );

			$factory->setNullMessenger();

			$factory->getMembershipApplicationRepository()->storeApplication( ValidMembershipApplication::newDomainEntityUsingPayPal() );

			$request = $this->newSubscriptionSignupRequest();
			$factory->setPayPalMembershipFeeNotificationVerifier(
				$this->newSucceedingVerifier( array_merge( [ 'cmd' => '_notify-validate' ], $request ) )
			);

			$client->request(
				'POST',
				'/handle-paypal-membership-fee-payments',
				$request
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertPayPalDataGotPersisted( $factory->getMembershipApplicationRepository(), $request );
		} );
	}

	private function newSucceedingVerifier( array $request ) {
		return $this->newVerifierMock( $request, self::VERIFICATION_SUCCESSFUL );
	}

	private function newFailingVerifier( array $request ) {
		return $this->newVerifierMock( $request, self::VERIFICATION_FAILED );
	}

	private function newVerifierMock( array $request, string $expectedResponse ) {
		return new PayPalPaymentNotificationVerifier(
			$this->newGuzzleClientMock( $request, $expectedResponse ),
			self::BASE_URL,
			self::EMAIL_ADDRESS
		);
	}

	private function newGuzzleClientMock( array $request, string $expectedResponse ): GuzzleClient {
		$body = $this->getMockBuilder( Stream::class )
			->disableOriginalConstructor()
			->setMethods( [ 'getContents' ] )
			->getMock();

		$body->expects( $this->any() )
			->method( 'getContents' )
			->willReturn( $expectedResponse );

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
				[ 'form_params' => $request ]
			)
			->willReturn( $response );

		return $client;
	}

	public function testWhenPaymentProviderDoesNotVerify_errorCodeIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$request = $this->newSubscriptionSignupRequest();

			$factory->setPayPalMembershipFeeNotificationVerifier(
				$this->newFailingVerifier(
					array_merge( [ 'cmd' => '_notify-validate' ], $request )
				)
			);

			$client->request( 'POST', '/handle-paypal-membership-fee-payments', $request );

			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
			$this->assertSame( 'Payment provider did not confirm the sent data', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenWrongTransactionType_applicationIndicatesAnError() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$invalidRequest = $this->newInvalidTransactionRequest();

			$factory->setPayPalMembershipFeeNotificationVerifier(
				$this->newSucceedingVerifier(
					array_merge( [ 'cmd' => '_notify-validate' ], $invalidRequest )
				)
			);

			$client->request( 'POST', '/handle-paypal-membership-fee-payments', $invalidRequest );

			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
			$this->assertSame( 'Payment receiver address does not match', $client->getResponse()->getContent() );
		} );
	}

	private function newSubscriptionSignupRequest() {
		return [
			'txn_type' => 'subscr_signup',

			'receiver_email' => 'paypaldev-facilitator@wikimedia.de',
			'item_number' => 1,
			'item_name' => 'Your membership',
			'payment_type' => 'instant',
			'mc_currency' => 'EUR',

			'subscr_id' => '8RHHUM3W3PRH7QY6B59',
			'subscr_date' => '20:12:59 Jan 13, 2009 PST',
			'payer_id' => 'LPLWNMTBWMFAY',
			'payer_status' => 'verified',
			'address_status' => 'confirmed',
			'first_name' => 'Generous',
			'last_name' => 'Donor',
			'address_name' => 'Generous Donor',

			'custom' => '{"id": "1", "utoken": "some token"}'
		];
	}

	private function newInvalidTransactionRequest() {
		return [
			'txn_type' => 'invalid_transaction',
		];
	}

	private function assertPayPalDataGotPersisted( ApplicationRepository $applicationRepo, array $request ) {
		$membershipApplication = $applicationRepo->getApplicationById( self::MEMBERSHIP_APPLICATION_ID );

		/** @var PayPalPayment $paymentMethod */
		$paymentMethod = $membershipApplication->getPayment()->getPaymentMethod();
		$pplData = $paymentMethod->getPayPalData();

		$this->assertSame( $request['payer_id'], $pplData->getPayerId() );
		$this->assertSame( $request['subscr_id'], $pplData->getSubscriberId() );
		$this->assertSame( $request['payer_status'], $pplData->getPayerStatus() );
		$this->assertSame( $request['first_name'], $pplData->getFirstName() );
		$this->assertSame( $request['last_name'], $pplData->getLastName() );
		$this->assertSame( $request['address_name'], $pplData->getAddressName() );
		$this->assertSame( $request['address_status'], $pplData->getAddressStatus() );
		$this->assertSame( $request['mc_currency'], $pplData->getCurrencyCode() );
		$this->assertSame( $request['payment_type'], $pplData->getPaymentType() );
	}

}
