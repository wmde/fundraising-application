<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateTestCampaign;
use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationRouteTest extends WebRouteTestCase {

	const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';
	const SOME_UPDATE_TOKEN = 'SomeUpdateToken';

	public function testGivenPostRequest_resultHasMethodNotAllowedStatus() {
		$client = $this->createClient();

		$this->expectException( MethodNotAllowedHttpException::class );
		$client->request( 'POST', 'show-donation-confirmation' );
	}

	public function testGivenValidRequest_confirmationPageContainsDonationData() {
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
					'accessToken' => self::CORRECT_ACCESS_TOKEN,
					'updateToken' => self::SOME_UPDATE_TOKEN
				]
			);

			$this->assertDonationDataInResponse( $donation, $client->getResponse() );
		} );
	}

	public function testGivenAlternativeConfirmationPageConfig_alternativeContentIsDisplayed() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newConfirmationPageConfig() )
			);
			$donation = $this->newStoredDonation( $factory );

			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'donationId' => $donation->getId(),
					'accessToken' => self::CORRECT_ACCESS_TOKEN,
					'updateToken' => self::SOME_UPDATE_TOKEN
				]
			);

			$this->assertContains( 'Alternative content', $client->getResponse()->getContent() );
		} );
	}

	private function newStoredDonation( FunFunFactory $factory ): Donation {
		$donation = ValidDonation::newDonation();

		$factory->getDonationRepository()->storeDonation( $donation );
		$factory->newDonationAuthorizationUpdater()->allowAccessViaToken(
			$donation->getId(),
			self::CORRECT_ACCESS_TOKEN
		);

		return $donation;
	}

	private function assertDonationDataInResponse( Donation $donation, Response $response ) {
		$content = $response->getContent();

		$this->assertContains( $donation->getPersonalInfo()->getPersonName()->getFirstName(), $content );
		$this->assertContains( $donation->getPersonalInfo()->getPersonName()->getLastName(), $content );
		$this->assertContains( $donation->getBankData()->getIban()->toString(), $content );
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
					'accessToken' => 'WrongAccessToken',
					'updateToken' => self::SOME_UPDATE_TOKEN
				]
			);

			$this->assertDonationIsNotShown( $donation, $client->getResponse() );
		} );
	}

	private function assertDonationIsNotShown( Donation $donation, Response $response ) {
		$content = $response->getContent();

		$this->assertNotContains( $donation->getPersonalInfo()->getPersonName()->getFirstName(), $content );
		$this->assertNotContains( $donation->getPersonalInfo()->getPersonName()->getLastName(), $content );
		$this->assertNotContains( $donation->getBankData()->getIban()->toString(), $content );

		$this->assertContains( 'TODO: access not permitted', $content );
	}

	public function testGivenWrongId_accessIsDenied() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$donation = $this->newStoredDonation( $factory );

			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'donationId' => $donation->getId() + 1,
					'accessToken' => self::CORRECT_ACCESS_TOKEN,
					'updateToken' => self::SOME_UPDATE_TOKEN
				]
			);

			$this->assertDonationIsNotShown( $donation, $client->getResponse() );
		} );
	}

	public function testWhenNoDonation_accessIsDenied() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setDonationConfirmationPageSelector(
				new DonationConfirmationPageSelector( $this->newEmptyConfirmationPageConfig() )
			);

			$client->request(
				'GET',
				'show-donation-confirmation',
				[
					'donationId' => 1,
					'accessToken' => self::CORRECT_ACCESS_TOKEN,
					'updateToken' => self::SOME_UPDATE_TOKEN
				]
			);

			$this->assertContains( 'TODO: access not permitted', $client->getResponse()->getContent() );
		} );
	}

	private function newConfirmationPageConfig() {
		return [
			'default' => '10h16_Bestätigung.twig',
			'campaigns' => [ new TemplateTestCampaign( [
				'code' => 'example',
				'active' => true,
				'startDate' => '1970-01-01 00:00:00',
				'endDate' => '2038-12-31 23:59:59',
				'templates' => [ 'DonationConfirmationAlternative.twig' ]
			] ) ]
		];
	}

	private function newEmptyConfirmationPageConfig() {
		return [
			'default' => '10h16_Bestätigung.twig',
			'campaigns' => [ new TemplateTestCampaign( [
				'code' => 'example',
				'active' => false,
				'startDate' => '1970-01-01 00:00:00',
				'endDate' => '1970-12-31 23:59:59',
				'templates' => []
			] ) ]
		];
	}

}
