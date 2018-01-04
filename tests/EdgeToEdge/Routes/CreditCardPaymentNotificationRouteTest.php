<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardExpiry;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\FakeCreditCardService;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
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

	public function testGivenInvalidRequest_applicationIndicatesError(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setCreditCardService( new FakeCreditCardService() );
			$client->request(
				'GET',
				'/handle-creditcard-payment-notification',
				[]
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertContains( 'status=error', $client->getResponse()->getContent() );
			$this->assertContains( 'msg=', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenValidRequest_applicationIndicatesSuccess(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setDonationTokenGenerator( new FixedTokenGenerator(
				self::UPDATE_TOKEN,
				\DateTime::createFromFormat( 'Y-m-d H:i:s', '2039-12-31 23:59:59' )
			) );

			$factory->setCreditCardService( new FakeCreditCardService() );

			$factory->getDonationRepository()->storeDonation( ValidDonation::newIncompleteCreditCardDonation() );

			$client->request(
				'GET',
				'/handle-creditcard-payment-notification',
				$this->newRequest()
			);

			$this->assertSame( 200, $client->getResponse()->getStatusCode() );
			$this->assertContains( 'status=ok', $client->getResponse()->getContent() );
			$this->assertContains(
				'url=http://my.donation.app/show-donation-confirmation?id=1&accessToken=my_secret_access_token',
				$client->getResponse()->getContent()
			);
			$this->assertCreditCardDataGotPersisted( $factory->getDonationRepository(), $this->newRequest() );
		} );
	}

	private function newRequest(): array {
		return [
			'function' => self::FUNCTION,
			'donation_id' => (string)self::DONATION_ID,
			'amount' => 1337, // Should match ValidDonation::DONATION_AMOUNT
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

	private function assertCreditCardDataGotPersisted( DonationRepository $donationRepo, array $request ): void {
		$donation = $donationRepo->getDonationById( self::DONATION_ID );

		/** @var \WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardPayment $paymentMethod */
		$paymentMethod = $donation->getPayment()->getPaymentMethod();
		$ccData = $paymentMethod->getCreditCardData();

		$this->assertSame( $request['currency'], $ccData->getCurrencyCode() );
		$this->assertSame( $request['amount'], $ccData->getAmount()->getEuroCents() );
		$this->assertSame( $request['country'], $ccData->getCountryCode() );
		$this->assertSame( $request['auth'], $ccData->getAuthId() );
		$this->assertSame( $request['title'], $ccData->getTitle() );
		$this->assertSame( $request['sessionId'], $ccData->getSessionId() );
		$this->assertSame( $request['transactionId'], $ccData->getTransactionId() );
		$this->assertSame( self::STATUS, $ccData->getTransactionStatus() );
		$this->assertSame( $request['customerId'], $ccData->getCustomerId() );
		$this->assertEquals( new CreditCardExpiry( 9, 2038 ), $ccData->getCardExpiry() );
		$this->assertNotEmpty( $ccData->getTransactionTimestamp() );
	}

}
