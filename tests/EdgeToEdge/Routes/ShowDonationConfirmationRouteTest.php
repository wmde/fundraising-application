<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Silex\Application;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\App\Controllers\ShowDonationConfirmationController;
use WMDE\Fundraising\Frontend\App\Controllers\UpdateDonorController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
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

	public function testGivenValidPostRequest_confirmationPageContainsDonationData(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertDonationDataInResponse( $donation, $responseContent );
		} );
	}

	public function testGivenValidPostRequest_embeddedMembershipFormContainsDonationData(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertEmbeddedMembershipFormIsPrefilled( $donation, $responseContent );
		} );
	}

	private function assertEmbeddedMembershipFormIsPrefilled( Donation $donation, string $responseContent ): void {
		$personName = $donation->getDonor()->getName();
		$physicalAddress = $donation->getDonor()->getPhysicalAddress();
		/** @var DirectDebitPayment $paymentMethod */
		$paymentMethod = $donation->getPaymentMethod();
		$bankData = $paymentMethod->getBankData();
		$this->assertContains( 'initialFormValues.addressType: ' . $personName->getPersonType(), $responseContent );
		$this->assertContains( 'initialFormValues.salutation: ' . $personName->getSalutation(), $responseContent );
		$this->assertContains( 'initialFormValues.title: ' . $personName->getTitle(), $responseContent );
		$this->assertContains( 'initialFormValues.firstName: ' . $personName->getFirstName(), $responseContent );
		$this->assertContains( 'initialFormValues.lastName: ' . $personName->getLastName(), $responseContent );
		$this->assertContains( 'initialFormValues.companyName: ' . $personName->getCompanyName(), $responseContent );
		$this->assertContains( 'initialFormValues.street: ' . $physicalAddress->getStreetAddress(), $responseContent );
		$this->assertContains( 'initialFormValues.postcode: ' . $physicalAddress->getPostalCode(), $responseContent );
		$this->assertContains( 'initialFormValues.city: ' . $physicalAddress->getCity(), $responseContent );
		$this->assertContains( 'initialFormValues.country: ' . $physicalAddress->getCountryCode(), $responseContent );
		$this->assertContains( 'initialFormValues.email: ' . $donation->getDonor()->getEmailAddress(), $responseContent );
		$this->assertContains( 'initialFormValues.iban: ' . $bankData->getIban()->toString(), $responseContent );
		$this->assertContains( 'initialFormValues.bic: ' . $bankData->getBic(), $responseContent );
		$this->assertContains( 'initialFormValues.accountNumber: ' . $bankData->getAccount(), $responseContent );
		$this->assertContains( 'initialFormValues.bankCode: ' . $bankData->getBankCode(), $responseContent );
		$this->assertContains( 'initialFormValues.bankname: ' . $bankData->getBankName(), $responseContent );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertContains( 'Anonym', $responseContent );
		} );
	}

	public function testGivenAnonymousDonation_confirmationPageShowsStatusText(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertContains( 'status-booked', $responseContent );
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
		$physicalAddress = $donor->getPhysicalAddress();
		/** @var DirectDebitPayment $paymentMethod */
		$paymentMethod = $donation->getPaymentMethod();

		$this->assertContains( 'donation.id: ' . $donation->getId(), $responseContent );
		$this->assertContains( 'donation.status: ' . self::MAPPED_STATUS, $responseContent );
		$this->assertContains( 'donation.amount: ' . $donation->getAmount()->getEuroString(), $responseContent );
		$this->assertContains( 'donation.interval: ' . $donation->getPaymentIntervalInMonths(), $responseContent );
		$this->assertContains( 'donation.paymentType: ' . $donation->getPaymentMethodId(), $responseContent );
		$this->assertContains( 'donation.optsIntoNewsletter: ' . $donation->getOptsIntoNewsletter(), $responseContent );
		$this->assertContains( 'donation.updateToken: ' . self::CORRECT_ACCESS_TOKEN, $responseContent );

		$this->assertContains( 'address.salutation: ' . $personName->getSalutation(), $responseContent );
		$this->assertContains( 'address.fullName: ' . $personName->getFullName(), $responseContent );
		$this->assertContains( 'address.firstName: ' . $personName->getFirstName(), $responseContent );
		$this->assertContains( 'address.lastName: ' . $personName->getLastName(), $responseContent );
		$this->assertContains( 'address.streetAddress: ' . $physicalAddress->getStreetAddress(), $responseContent );
		$this->assertContains( 'address.postalCode: ' . $physicalAddress->getPostalCode(), $responseContent );
		$this->assertContains( 'address.city: ' . $physicalAddress->getCity(), $responseContent );
		$this->assertContains( 'address.email: ' . $donor->getEmailAddress(), $responseContent );

		$this->assertContains( 'bankData.iban: ' . $paymentMethod->getBankData()->getIban()->toString(), $responseContent );
		$this->assertContains( 'bankData.bic: ' . $paymentMethod->getBankData()->getBic(), $responseContent );
		$this->assertContains( 'bankData.bankname: ' . $paymentMethod->getBankData()->getBankName(), $responseContent );
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
		$this->assertNotContains( $donation->getDonor()->getName()->getFirstName(), $responseContent );
		$this->assertNotContains( $donation->getDonor()->getName()->getLastName(), $responseContent );

		$this->assertContains( self::ACCESS_DENIED_TEXT, $responseContent );
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

		$this->assertContains( self::ACCESS_DENIED_TEXT, $responseContent );
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

	public function testWhenAddressIsUpdated_addressConfirmationIsHighlighted(): void {
		$this->createAppEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory, Application $app ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
				$app['session']->set(
					UpdateDonorController::ADDRESS_CHANGE_SESSION_KEY,
					true
				);
				$donation = $this->newStoredDonation( $factory );
				$crawler = $this->requestDonationConfirmation( $client, $donation );

				$this->assertSame( 1, $crawler->filter( '.address-change' )->count() );
			}
		);
	}

	public function testWhenAddressIsNotUpdated_addressHighlightIsNotShown(): void {
		$this->createAppEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory, Application $app ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
				$app['session']->set(
					UpdateDonorController::ADDRESS_CHANGE_SESSION_KEY,
					false
				);
				$donation = $this->newStoredDonation( $factory );
				$crawler = $this->requestDonationConfirmation( $client, $donation );

				$this->assertSame( 0, $crawler->filter( '.address-change' )->count() );
			}
		);
	}

	private function requestDonationConfirmation( Client $client, Donation $donation ): Crawler {
		return $client->request(
			'GET',
			'show-donation-confirmation',
			[
				'id' => $donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);
	}

	private function setDefaultSkin( FunFunFactory $factory, string $skinName ): void {
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				[ 'skins' => [ 'default_bucket' => $skinName ] ]
			)
		);
	}
}
