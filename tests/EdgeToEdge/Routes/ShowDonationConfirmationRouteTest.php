<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ShowDonationConfirmationController
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	private const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';

	private const ACCESS_DENIED_TEXT = 'access_denied_donation_confirmation';

	private Donation $donation;
	private Payment $payment;

	public function testGivenValidRequest_confirmationPageContainsDonationData(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredDirectDebitDonation( $factory );
		} );

		$client = $this->createClient();

		$responseContent = $this->retrieveDonationConfirmation( $client, $this->donation->getId() );

		$this->assertDonationDataInResponse( $this->donation, $this->payment, $responseContent );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->givenStoredBookedAnonymousPayPalDonation( $factory );
		} );
		$client = $this->createClient();

		$responseContent = $this->retrieveDonationConfirmation( $client, $this->donation->getId() );

		$this->assertStringContainsString( 'Anonym', $responseContent );
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

		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$this->donation = ValidDonation::newDirectDebitDonation();

		$factory->getDonationRepository()->storeDonation( $this->donation );
	}

	private function givenStoredBookedAnonymousPayPalDonation( FunFunFactory $factory ): void {
		$this->storePayment( $factory, ValidPayments::newBookedPayPalPayment() );

		$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::CORRECT_ACCESS_TOKEN ) );
		$this->donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$factory->getDonationRepository()->storeDonation( $this->donation );
	}

	private function storePayment( FunFunFactory $factory, Payment $payment ): void {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$factory->getPaymentRepository()->storePayment( $payment );

		$this->payment = $payment;
	}

	private function assertDonationDataInResponse( Donation $donation, Payment $payment, string $responseContent ): void {
		$donor = $donation->getDonor();
		$personName = $donor->getName();
		$personNameValues = $personName->toArray();
		$physicalAddress = $donor->getPhysicalAddress();

		$paymentData = $payment->getDisplayValues();

		$this->assertStringContainsString( 'donation.id: ' . $donation->getId(), $responseContent );
		$this->assertStringContainsString( 'donation.amount: ' . $payment->getAmount()->getEuroFloat(), $responseContent );
		$this->assertStringContainsString( 'donation.interval: ' . $paymentData['interval'], $responseContent );
		$this->assertStringContainsString( 'donation.paymentType: ' . $paymentData['paymentType'], $responseContent );
		$this->assertStringContainsString( 'donation.newsletter: ' . $donation->getDonor()->wantsNewsletter(), $responseContent );
		$this->assertStringContainsString( 'donation.updateToken: ' . self::CORRECT_ACCESS_TOKEN, $responseContent );

		$this->assertStringContainsString( 'address.salutation: ' . $personNameValues['salutation'], $responseContent );
		$this->assertStringContainsString( 'address.fullName: ' . $personName->getFullName(), $responseContent );
		$this->assertStringContainsString( 'address.firstName: ' . $personNameValues['firstName'], $responseContent );
		$this->assertStringContainsString( 'address.lastName: ' . $personNameValues['lastName'], $responseContent );
		$this->assertStringContainsString( 'address.streetAddress: ' . $physicalAddress->getStreetAddress(), $responseContent );
		$this->assertStringContainsString( 'address.postalCode: ' . $physicalAddress->getPostalCode(), $responseContent );
		$this->assertStringContainsString( 'address.city: ' . $physicalAddress->getCity(), $responseContent );
		$this->assertStringContainsString( 'address.email: ' . $donor->getEmailAddress(), $responseContent );

		$this->assertStringContainsString( 'bankData.iban: ' . $paymentData['iban'], $responseContent );
		$this->assertStringContainsString( 'bankData.bic: ' . $paymentData['bic'], $responseContent );
		$this->assertStringContainsString( 'bankData.bankname: ' . ( $paymentData['bankname'] ?? '' ), $responseContent );
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
