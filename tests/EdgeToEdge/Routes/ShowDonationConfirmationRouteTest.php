<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\App\Controllers\ShowDonationConfirmationController;
use WMDE\Fundraising\Frontend\App\Controllers\UpdateDonorController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\ShowDonationConfirmationController
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	private const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';
	private const MAPPED_STATUS = 'status-new';

	private const ACCESS_DENIED_TEXT = 'access_denied_donation_confirmation';

	public function testGivenValidRequest_confirmationPageContainsDonationData(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertDonationDataInResponse( $donation, $responseContent );
		} );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertStringContainsString( 'Anonym', $responseContent );
		} );
	}

	public function testGivenAnonymousDonation_confirmationPageShowsStatusText(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertStringContainsString( 'status-booked', $responseContent );
		} );
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
		$this->assertStringContainsString( 'donation.status: ' . self::MAPPED_STATUS, $responseContent );
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
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
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() + 1 );

			$this->assertDonationIsNotShown( $donation, $responseContent );
		} );
	}

	public function testWhenNoDonation_accessIsDenied(): void {
		$client = $this->createClient( [] );
		$responseContent = $this->retrieveDonationConfirmation( $client, 1 );

		$this->assertStringContainsString( self::ACCESS_DENIED_TEXT, $responseContent );
	}

	public function testWhenDonationTimestampCookiePreexists_itIsNotOverwritten(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newStoredDonation( $factory );

			$client->getCookieJar()->set(
				new Cookie( ShowDonationConfirmationController::SUBMISSION_COOKIE_NAME, 'some value' )
			);
			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'id' => $donation->getId(),
					'accessToken' => self::CORRECT_ACCESS_TOKEN
				]
			);

			$this->assertSame(
				'some value',
				$client->getCookieJar()->get( ShowDonationConfirmationController::SUBMISSION_COOKIE_NAME )->getValue()
			);
		} );
	}
}
