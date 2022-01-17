<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\ShowDonationConfirmationController
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	private const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';

	private const ACCESS_DENIED_TEXT = 'access_denied_donation_confirmation';

	private Donation $donation;

	public function testGivenValidRequest_confirmationPageContainsDonationData(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->donation = $this->newStoredDonation( $factory );
		} );
		$client = $this->createClient();

		$responseContent = $this->retrieveDonationConfirmation( $client, $this->donation->getId() );

		$this->assertDonationDataInResponse( $this->donation, $responseContent );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->donation = $this->newBookedAnonymousPayPalDonation( $factory );
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

	private function newStoredDonation( FunFunFactory $factory ): Donation {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$donation = ValidDonation::newDirectDebitDonation();

		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	private function newBookedAnonymousPayPalDonation( FunFunFactory $factory ): Donation {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::CORRECT_ACCESS_TOKEN ) );
		$donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	private function assertDonationDataInResponse( Donation $donation, string $responseContent ): void {
		$donor = $donation->getDonor();
		$personName = $donor->getName();
		$personNameValues = $personName->toArray();
		$physicalAddress = $donor->getPhysicalAddress();
		/** @var DirectDebitPayment $paymentMethod */
		$paymentMethod = $donation->getPaymentMethod();

		$this->assertStringContainsString( 'donation.id: ' . $donation->getId(), $responseContent );
		$this->assertStringContainsString( 'donation.amount: ' . $donation->getAmount()->getEuroString(), $responseContent );
		$this->assertStringContainsString( 'donation.interval: ' . $donation->getPaymentIntervalInMonths(), $responseContent );
		$this->assertStringContainsString( 'donation.paymentType: ' . $donation->getPaymentMethodId(), $responseContent );
		$this->assertStringContainsString( 'donation.optsIntoNewsletter: ' . $donation->getOptsIntoNewsletter(), $responseContent );
		$this->assertStringContainsString( 'donation.updateToken: ' . self::CORRECT_ACCESS_TOKEN, $responseContent );

		$this->assertStringContainsString( 'address.salutation: ' . $personNameValues['salutation'], $responseContent );
		$this->assertStringContainsString( 'address.fullName: ' . $personName->getFullName(), $responseContent );
		$this->assertStringContainsString( 'address.firstName: ' . $personNameValues['firstName'], $responseContent );
		$this->assertStringContainsString( 'address.lastName: ' . $personNameValues['lastName'], $responseContent );
		$this->assertStringContainsString( 'address.streetAddress: ' . $physicalAddress->getStreetAddress(), $responseContent );
		$this->assertStringContainsString( 'address.postalCode: ' . $physicalAddress->getPostalCode(), $responseContent );
		$this->assertStringContainsString( 'address.city: ' . $physicalAddress->getCity(), $responseContent );
		$this->assertStringContainsString( 'address.email: ' . $donor->getEmailAddress(), $responseContent );

		$this->assertStringContainsString( 'bankData.iban: ' . $paymentMethod->getBankData()->getIban()->toString(), $responseContent );
		$this->assertStringContainsString( 'bankData.bic: ' . $paymentMethod->getBankData()->getBic(), $responseContent );
		$this->assertStringContainsString( 'bankData.bankname: ' . $paymentMethod->getBankData()->getBankName(), $responseContent );
	}

	public function testGivenWrongToken_accessIsDenied(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newStoredDonation( $factory );

			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'donationId' => $donation->getId(),
					'accessToken' => 'WrongAccessToken'
				]
			);

			$this->assertDonationIsNotShown( $donation, $client->getResponse()->getContent() );
		} );
	}

	private function assertDonationIsNotShown( Donation $donation, string $responseContent ): void {
		$this->assertStringNotContainsString( $donation->getDonor()->getPhysicalAddress()->getStreetAddress(), $responseContent );

		$this->assertStringContainsString( self::ACCESS_DENIED_TEXT, $responseContent );
	}

	public function testGivenWrongId_accessIsDenied(): void {
		$this->modifyEnvironment( function ( FunFunFactory $factory ): void {
			$this->donation = $this->newStoredDonation( $factory );
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
			$this->donation = $this->newStoredDonation( $factory );
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
