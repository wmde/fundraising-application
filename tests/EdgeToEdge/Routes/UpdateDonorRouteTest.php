<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\OverridingCampaignConfigurationLoader;
use WMDE\Fundraising\Store\DonationData;

/**
 * @license GNU GPL v2+
 */
class UpdateDonorRouteTest extends WebRouteTestCase {

	private const PATH = 'donation/update';
	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	const INVALID_UPDATE_TOKEN = '2ba905fe68e61f3a681d8faf689bfeeb8c942b5b';

	public function testWhenCorrectPrivatePersonDataIsPosted_addressIsChanged(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );
				$donation = $this->newStoredDonation( $factory );
				$this->performRequest(
					$client,
					$this->newPrivateDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$response = $client->getResponse();
				$this->assertTrue( $response->isRedirect( $this->newValidSuccessRedirectUrl( $donation, $factory ) ) );

				$crawler = $client->followRedirect();
				$dataVars = $this->getDataApplicationVars( $crawler );
				$this->assertEquals( $this->newPrivateDonorData()['addressType'], $dataVars->addressType );
				$this->assertEquals( $this->newPrivateDonorData()['salutation'], $dataVars->address->salutation );
				$this->assertEquals( $this->newPrivateDonorData()['firstName'], $dataVars->address->firstName );
				$this->assertEquals( $this->newPrivateDonorData()['lastName'], $dataVars->address->lastName );
				$this->assertEquals( $this->newPrivateDonorData()['street'], $dataVars->address->streetAddress );
				$this->assertEquals( $this->newPrivateDonorData()['postcode'], $dataVars->address->postalCode );
				$this->assertEquals( $this->newPrivateDonorData()['city'], $dataVars->address->city );
				$this->assertEquals( $this->newPrivateDonorData()['country'], $dataVars->address->countryCode );
				$this->assertEquals( $this->newPrivateDonorData()['email'], $dataVars->address->email );
			}
		);
	}

	public function testWhenCorrectCompanyDataIsPosted_addressIsChanged(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );
				$donation = $this->newStoredDonation( $factory );
				$this->performRequest(
					$client,
					$this->newCompanyDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$response = $client->getResponse();
				$this->assertTrue( $response->isRedirect( $this->newValidSuccessRedirectUrl( $donation, $factory ) ) );

				$crawler = $client->followRedirect();
				$dataVars = $this->getDataApplicationVars( $crawler );
				$this->assertEquals( $this->newCompanyDonorData()['addressType'], $dataVars->addressType );
				$this->assertEquals( $this->newCompanyDonorData()['companyName'], $dataVars->address->fullName );
				$this->assertEquals( $this->newCompanyDonorData()['street'], $dataVars->address->streetAddress );
				$this->assertEquals( $this->newCompanyDonorData()['postcode'], $dataVars->address->postalCode );
				$this->assertEquals( $this->newCompanyDonorData()['city'], $dataVars->address->city );
				$this->assertEquals( $this->newCompanyDonorData()['country'], $dataVars->address->countryCode );
				$this->assertEquals( $this->newCompanyDonorData()['email'], $dataVars->address->email );
			}
		);
	}

	public function testGivenRequestWithoutParameters_resultIsError(): void {
		$client = $this->createClient();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[]
		);

		$response = $client->getResponse();
		$this->assertTrue( $response->isForbidden(), 'Request is forbidden' );
	}

	public function testWhenInvalidUpdateTokenIsSupplied_requestIsDenied(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );
				$donation = $this->newStoredDonation( $factory );

				$this->performRequest(
					$client,
					$this->newPrivateDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::INVALID_UPDATE_TOKEN
				);

				$response = $client->getResponse();
				$this->assertTrue( $response->isForbidden() );
			}
		);
	}

	public function testWhenDonationIsExported_requestIsDenied(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );

				$donation = ValidDoctrineDonation::newExportedirectDebitDoctrineDonation();
				$donation->modifyDataObject(
					function ( DonationData $data ) {
						$data->setAccessToken( self::CORRECT_UPDATE_TOKEN );
						$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
					}
				);
				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$this->performRequest(
					$client,
					$this->newPrivateDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);

				$response = $client->getResponse();
				$this->assertTrue( $response->isForbidden() );
			}
		);
	}

	public function testWhenDonationDataIsInvalid_requestIsDenied(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );
				$donation = $this->newStoredDonation( $factory );
				$donorData = $this->newPrivateDonorData();
				$donorData['email'] = 'this_is_not_a_valid_email_address.de';
				$crawler = $this->performRequest(
					$client,
					$donorData,
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$dataVars = $this->getDataApplicationVars( $crawler );
				$response = $client->getResponse();
				$this->assertTrue( $response->isSuccessful() );
				$this->assertContains(
					'donor_change_failure_validation_error',
					$dataVars->updateData->message
				);
			}
		);
	}

	public function testWhenDonationAlreadyHasAddress_requestIsDenied(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'laika' );

				$donation = ValidDoctrineDonation::newDirectDebitDoctrineDonation();
				$donation->modifyDataObject(
					function ( DonationData $data ) {
						$data->setAccessToken( self::CORRECT_UPDATE_TOKEN );
						$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
					}
				);
				$factory->getEntityManager()->persist( $donation );
				$factory->getEntityManager()->flush();

				$this->performRequest(
					$client,
					$this->newPrivateDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$response = $client->getResponse();
				$this->assertTrue( $response->isForbidden() );
			}
		);
	}

	private function performRequest( Client $client, array $data, int $donationId, string $accessToken, string $updateToken ): Crawler {
		return $client->request(
			Request::METHOD_POST,
			self::PATH . '?accessToken=' . $accessToken,
			array_merge(
				[
					'donation_id' => $donationId,
					'updateToken' => $updateToken
				],
				$data
			)
		);
	}

	private function newStoredDonation( FunFunFactory $factory ): Donation {
		$factory->setDonationTokenGenerator(
			new FixedTokenGenerator(
				self::CORRECT_UPDATE_TOKEN,
				new \DateTime( '9001-01-01' )
			)
		);

		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();

		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	private function newPrivateDonorData(): array {
		return [
			'addressType' => DonorName::PERSON_PRIVATE,
			'city' => 'Mönchengladbach',
			'country' => 'DE',
			'email' => 'test@test.de',
			'firstName' => 'Hans',
			'lastName' => 'Wurst',
			'postcode' => '12345',
			'salutation' => 'Herr',
			'street' => 'Teststraße 123',
		];
	}

	private function newCompanyDonorData(): array {
		return [
			'addressType' => DonorName::PERSON_COMPANY,
			'city' => 'Mönchengladbach',
			'companyName' => 'Wikimedia Deutschland Money Makers GmbH',
			'country' => 'DE',
			'email' => 'test@test.de',
			'postcode' => '12345',
			'street' => 'Teststraße 123',

		];
	}

	private function setDefaultSkin( FunFunFactory $factory, string $skinName ): void {
		$factory->setCampaignConfigurationLoader(
			new OverridingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(),
				[ 'skins' => [ 'default_bucket' => $skinName ] ]
			)
		);
	}

	private function newValidSuccessRedirectUrl( Donation $donation, FunFunFactory $ffFactory ): string {
		return $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
			'show-donation-confirmation',
			[
				'id' => $donation->getId(),
				'accessToken' => self::CORRECT_UPDATE_TOKEN
			]
		);
	}

	private function getDataApplicationVars( Crawler $crawler ): object {
		return json_decode( $crawler->filter( '#app' )->getNode( 0 )->getAttribute( 'data-application-vars' ) );
	}
}
