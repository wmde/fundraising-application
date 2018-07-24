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
				$this->setDefaultSkin( $factory, 'cat17' );
				$donation = $this->newStoredDonation( $factory );
				$this->performRequest(
					$client,
					$this->newPrivateDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$response = $client->getResponse();
				$this->assertTrue( $response->isRedirect( $this->newValidSuccessRedirectUrl( $donation ) ) );

				$crawler = $client->followRedirect();
				$this->assertContains(
					'Hans Wurst, Teststraße 123, 12345 Mönchengladbach',
					$crawler->filter( '.receipt-info .caption' )->html()
				);
			}
		);
	}

	public function testWhenCorrectCompanyDataIsPosted_addressIsChanged(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );
				$donation = $this->newStoredDonation( $factory );
				$this->performRequest(
					$client,
					$this->newCompanyDonorData(),
					$donation->getId(),
					self::CORRECT_UPDATE_TOKEN,
					self::CORRECT_UPDATE_TOKEN
				);
				$response = $client->getResponse();
				$this->assertTrue( $response->isRedirect( $this->newValidSuccessRedirectUrl( $donation ) ) );

				$crawler = $client->followRedirect();
				$this->assertContains(
					'Wikimedia Deutschland Money Makers GmbH, Teststraße 123, 12345 Mönchengladbach',
					$crawler->filter( '.receipt-info .caption' )->html()
				);
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
				$this->setDefaultSkin( $factory, 'cat17' );
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
				$this->setDefaultSkin( $factory, 'cat17' );

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
				$this->setDefaultSkin( $factory, 'cat17' );
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

				$response = $client->getResponse();
				$this->assertTrue( $response->isSuccessful() );
				$this->assertContains(
					'donor_change_failure_validation_error',
					$crawler->filter( '.messages .h3' )->html()
				);
			}
		);
	}

	public function testWhenDonationAlreadyHasAddress_requestIsDenied(): void {
		$this->createEnvironment(
			[],
			function ( Client $client, FunFunFactory $factory ): void {
				$this->setDefaultSkin( $factory, 'cat17' );

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

	private function newValidSuccessRedirectUrl( Donation $donation ): string {
		return sprintf(
			'/show-donation-confirmation?id=%s&accessToken=%s',
			$donation->getId(),
			self::CORRECT_UPDATE_TOKEN
		);
	}
}
