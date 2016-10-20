<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\App\RouteHandlers\ShowDonationConfirmationHandler;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';

	const ACCESS_DENIED_TEXT = 'keine Berechtigung';

	public function testGivenValidRequest_confirmationPageContainsDonationData() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertDonationDataInResponse( $donation, $responseContent );
			$this->assertContains( 'Template Name: DonationConfirmation.twig', $responseContent );
		} );
	}

	public function testGivenValidPostRequest_confirmationPageContainsDonationData() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertDonationDataInResponse( $donation, $responseContent );
			$this->assertContains( 'Template Name: DonationConfirmation.twig', $responseContent );
		} );
	}

	public function testGivenValidPostRequest_embeddedMembershipFormContainsDonationData() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertEmbeddedMembershipFormIsPrefilled( $donation, $responseContent );
			$this->assertContains( 'Template Name: DonationConfirmation.twig', $responseContent );
		} );
	}

	private function assertEmbeddedMembershipFormIsPrefilled( Donation $donation, string $responseContent ) {
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

	public function testGivenAlternativeConfirmationPageConfig_alternativeContentIsDisplayed() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newConfirmationPageConfig() )
			);
			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertContains( 'Alternative content', $responseContent );
			$this->assertContains( 'Template Name: DonationConfirmationAlternative.twig', $responseContent );
			$this->assertContains( 'Template Campaign: example', $responseContent );
		} );
	}

	public function testGivenAnonymousDonation_confirmationPageReflectsThat() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertContains( 'Anonym', $responseContent );
		} );
	}

	public function testGivenAnonymousDonation_confirmationPageShowsStatusText() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$donation = $this->newBookedAnonymousPayPalDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() );

			$this->assertContains( 'Ihre Spende per PayPal wurde gebucht', $responseContent );
		} );
	}

	private function retrieveDonationConfirmation( Client $client, int $donationId ) {
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
		$factory->setTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$donation = ValidDonation::newDirectDebitDonation();

		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	private function newBookedAnonymousPayPalDonation( FunFunFactory $factory ): Donation {
		$factory->setTokenGenerator( new FixedTokenGenerator( self::CORRECT_ACCESS_TOKEN ) );
		$donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	private function assertDonationDataInResponse( Donation $donation, string $responseContent ) {
		$donor = $donation->getDonor();
		$personName = $donor->getName();
		$physicalAddress = $donor->getPhysicalAddress();
		/** @var DirectDebitPayment $paymentMethod */
		$paymentMethod = $donation->getPaymentMethod();

		$this->assertContains( 'donation.id: ' . $donation->getId(), $responseContent );
		$this->assertContains( 'donation.status: ' . $donation->getStatus(), $responseContent );
		$this->assertContains( 'donation.amount: ' . $donation->getAmount()->getEuroString(), $responseContent );
		$this->assertContains( 'donation.interval: ' . $donation->getPaymentIntervalInMonths(), $responseContent );
		$this->assertContains( 'donation.paymentType: ' . $donation->getPaymentType(), $responseContent );
		$this->assertContains( 'donation.optsIntoNewsletter: ' . $donation->getOptsIntoNewsletter(), $responseContent );
		$this->assertContains( 'donation.updateToken: ' . self::CORRECT_ACCESS_TOKEN, $responseContent );

		$this->assertContains( 'person.salutation: ' . $personName->getSalutation(), $responseContent );
		$this->assertContains( 'person.fullName: ' . $personName->getFullName(), $responseContent );
		$this->assertContains( 'person.firstName: ' . $personName->getFirstName(), $responseContent );
		$this->assertContains( 'person.lastName: ' . $personName->getLastName(), $responseContent );
		$this->assertContains( 'person.streetAddress: ' . $physicalAddress->getStreetAddress(), $responseContent );
		$this->assertContains( 'person.postalCode: ' . $physicalAddress->getPostalCode(), $responseContent );
		$this->assertContains( 'person.city: ' . $physicalAddress->getCity(), $responseContent );
		$this->assertContains( 'person.email: ' . $donor->getEmailAddress(), $responseContent );

		$this->assertContains( 'bankData.iban: ' . $paymentMethod->getBankData()->getIban()->toString(), $responseContent );
		$this->assertContains( 'bankData.bic: ' . $paymentMethod->getBankData()->getBic(), $responseContent );
		$this->assertContains( 'bankData.bankname: ' . $paymentMethod->getBankData()->getBankName(), $responseContent );
	}

	public function testGivenWrongToken_accessIsDenied() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);
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

	private function assertDonationIsNotShown( Donation $donation, string $responseContent ) {
		$this->assertNotContains( $donation->getDonor()->getName()->getFirstName(), $responseContent );
		$this->assertNotContains( $donation->getDonor()->getName()->getLastName(), $responseContent );

		$this->assertContains( self::ACCESS_DENIED_TEXT, $responseContent );
	}

	public function testGivenWrongId_accessIsDenied() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$donation = $this->newStoredDonation( $factory );

			$responseContent = $this->retrieveDonationConfirmation( $client, $donation->getId() + 1 );

			$this->assertDonationIsNotShown( $donation, $responseContent );
		} );
	}

	public function testWhenNoDonation_accessIsDenied() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$responseContent = $this->retrieveDonationConfirmation( $client, 1 );
			$this->assertContains( self::ACCESS_DENIED_TEXT, $responseContent );
		} );
	}

	private function newConfirmationPageConfig() {
		return [
			'default' => 'DonationConfirmation.twig',
			'campaigns' => [
				[
					'code' => 'example',
					'active' => true,
					'startDate' => '1970-01-01 00:00:00',
					'endDate' => '2038-12-31 23:59:59',
					'templates' => [ 'DonationConfirmationAlternative.twig' ]
				]
			]
		];
	}

	private function newEmptyConfirmationPageConfig() {
		return [
			'default' => 'DonationConfirmation.twig',
			'campaigns' => [
				[
					'code' => 'example',
					'active' => false,
					'startDate' => '1970-01-01 00:00:00',
					'endDate' => '1970-12-31 23:59:59',
					'templates' => []
				]
			]
		];
	}

}
