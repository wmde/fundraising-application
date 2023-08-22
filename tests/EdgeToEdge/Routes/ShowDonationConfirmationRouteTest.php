<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ShowDonationConfirmationController
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';

	private const ACCESS_DENIED_TEXT = 'access_denied_donation_confirmation';

	private Donation $donation;
	private Payment $payment;

	public function testGivenValidRequest_confirmationPageContainsDonationData(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );
		} );

		$client = $this->createClient();
		$client->request(
			'GET',
			'show-donation-confirmation',
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );

		$paymentData = $this->payment->getDisplayValues();
		$personNameValues = $this->donation->getDonor()->getName()->toArray();
		$physicalAddress = $this->donation->getDonor()->getPhysicalAddress();

		$this->assertEquals( $this->donation->getId(), $dataVars->donation->id );
		$this->assertEquals( $this->payment->getAmount()->getEuroFloat(), $dataVars->donation->amount );
		$this->assertEquals( $paymentData['interval'], $dataVars->donation->interval );
		$this->assertEquals( $paymentData['paymentType'], $dataVars->donation->paymentType );
		$this->assertEquals( $this->donation->getDonor()->wantsNewsletter(), $dataVars->donation->newsletter );
		$this->assertEquals( self::CORRECT_ACCESS_TOKEN, $dataVars->donation->accessToken );

		$this->assertEquals( $personNameValues['salutation'], $dataVars->address->salutation );
		$this->assertEquals( $this->donation->getDonor()->getName()->getFullName(), $dataVars->address->fullName );
		$this->assertEquals( $personNameValues['firstName'], $dataVars->address->firstName );
		$this->assertEquals( $personNameValues['lastName'], $dataVars->address->lastName );
		$this->assertEquals( $physicalAddress->getStreetAddress(), $dataVars->address->street );
		$this->assertEquals( $physicalAddress->getPostalCode(), $dataVars->address->postcode );
		$this->assertEquals( $physicalAddress->getCity(), $dataVars->address->city );
		$this->assertEquals( $physicalAddress->getCountryCode(), $dataVars->address->country );
		$this->assertEquals( $this->donation->getDonor()->getEmailAddress(), $dataVars->address->email );

		$this->assertEquals( $paymentData['iban'], $dataVars->bankData->iban );
		$this->assertEquals( $paymentData['bic'], $dataVars->bankData->bic );
		$this->assertEquals( ValidPayments::PAYMENT_BANK_NAME, $dataVars->bankData->bankname );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredBookedAnonymousPayPalDonation( $factory );
		} );
		$client = $this->createClient();
		$client->request(
			'GET',
			'show-donation-confirmation',
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertStringContainsString( 'anonym', $dataVars->addressType );
	}

	private function retrieveDonationConfirmation( Client $client, int $donationId ): string {
		$client->request(
			'GET',
			'show-donation-confirmation',
			[
				'id' => $donationId,
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		return $client->getResponse()->getContent();
	}

	private function givenStoredDirectDebitDonation( FunFunFactory $factory ): void {
		$this->storePayment( $factory, ValidPayments::newDirectDebitPayment() );
		$this->donation = ValidDonation::newDirectDebitDonation();
		$factory->getDonationRepository()->storeDonation( $this->donation );
		$this->createTokenForDonation( $factory );
	}

	private function createTokenForDonation( FunFunFactory $factory ): void {
		$em = $factory->getEntityManager();
		$em->persist( new AuthenticationToken( $this->donation->getId(), AuthenticationBoundedContext::Donation, self::CORRECT_ACCESS_TOKEN, self::CORRECT_ACCESS_TOKEN ) );
		$em->flush();
	}

	private function givenStoredBookedAnonymousPayPalDonation( FunFunFactory $factory ): void {
		$this->storePayment( $factory, ValidPayments::newBookedPayPalPayment() );

		$this->donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$factory->getDonationRepository()->storeDonation( $this->donation );
		$this->createTokenForDonation( $factory );
	}

	private function storePayment( FunFunFactory $factory, Payment $payment ): void {
		$factory->getPaymentRepository()->storePayment( $payment );
		$this->payment = $payment;
	}

	public function testGivenWrongToken_accessIsDenied(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );

			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'donationId' => $this->donation->getId(),
					'accessToken' => 'WrongAccessToken'
				]
			);

			$this->assertDonationIsNotShown( $this->donation, $client->getResponse()->getContent() );
		} );
	}

	private function assertDonationIsNotShown( Donation $donation, string $responseContent ): void {
		$this->assertStringNotContainsString( $donation->getDonor()->getPhysicalAddress()->getStreetAddress(), $responseContent );

		$this->assertStringContainsString( self::ACCESS_DENIED_TEXT, $responseContent );
	}

	public function testGivenWrongId_accessIsDenied(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );
		} );
		$client = $this->createClient();

		$responseContent = $this->retrieveDonationConfirmation( $client, $this->donation->getId() + 1 );

		$this->assertDonationIsNotShown( $this->donation, $responseContent );
	}

	public function testWhenNoDonation_accessIsDenied(): void {
		$client = $this->createClient();
		$responseContent = $this->retrieveDonationConfirmation( $client, 1 );

		$this->assertStringContainsString( self::ACCESS_DENIED_TEXT, $responseContent );
	}

	public function testRateLimitTimestampIsStoredInSession(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );
		} );
		$client = $this->createClient();

		$client->request(
			'GET',
			'show-donation-confirmation',
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		/** @var SessionInterface $session */
		$session = $client->getRequest()->getSession();
		$donationTimestamp = $session->get( FunFunFactory::DONATION_RATE_LIMIT_SESSION_KEY );
		$this->assertNotNull( $donationTimestamp );
		$this->assertEqualsWithDelta( time(), $donationTimestamp->getTimestamp(), 5.0, 'Timestamp should be not more than 5 seconds old' );
	}

}
