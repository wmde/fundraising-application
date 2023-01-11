<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\DonationContext\DataAccess\DonationData;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\Frontend\App\Routes;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\UpdateDonorController
 */
class UpdateDonorRouteTest extends WebRouteTestCase {

	use RebuildDatabaseSchemaTrait;
	use GetApplicationVarsTrait;

	private const PATH = 'donation/update';
	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	private const INVALID_UPDATE_TOKEN = '2ba905fe68e61f3a681d8faf689bfeeb8c942b5b';

	public function testWhenCorrectPrivatePersonDataIsPosted_addressIsChanged(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
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
		$expectedDonor = $this->newPrivateDonorData();
		$this->assertEquals( $expectedDonor['addressType'], $dataVars->addressType );
		$this->assertEquals( $expectedDonor['salutation'], $dataVars->address->salutation );
		$this->assertEquals( $expectedDonor['firstName'], $dataVars->address->firstName );
		$this->assertEquals( $expectedDonor['lastName'], $dataVars->address->lastName );
		$this->assertEquals( $expectedDonor['street'], $dataVars->address->street );
		$this->assertEquals( $expectedDonor['postcode'], $dataVars->address->postcode );
		$this->assertEquals( $expectedDonor['city'], $dataVars->address->city );
		$this->assertEquals( $expectedDonor['country'], $dataVars->address->country );
		$this->assertEquals( $expectedDonor['email'], $dataVars->address->email );
	}

	public function testWhenCorrectCompanyDataIsPosted_addressIsChanged(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
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
		$expectedDonorData = $this->newCompanyDonorData();
		$this->assertEquals( $expectedDonorData['addressType'], $dataVars->addressType );
		$this->assertEquals( $expectedDonorData['companyName'], $dataVars->address->fullName );
		$this->assertEquals( $expectedDonorData['street'], $dataVars->address->street );
		$this->assertEquals( $expectedDonorData['postcode'], $dataVars->address->postcode );
		$this->assertEquals( $expectedDonorData['city'], $dataVars->address->city );
		$this->assertEquals( $expectedDonorData['country'], $dataVars->address->country );
		$this->assertEquals( $expectedDonorData['email'], $dataVars->address->email );
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
		$client = $this->createClient();
		$factory = $this->getFactory();
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

	public function testWhenDonationIsExported_requestIsDenied(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
		$donation = ValidDoctrineDonation::newExportedirectDebitDoctrineDonation();
		$donation->modifyDataObject(
			static function ( DonationData $data ) {
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

	public function testWhenDonationDataIsInvalid_requestIsDenied(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();
		$factory = $this->getFactory();
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
		$this->assertStringContainsString(
			'donor_change_failure_validation_error',
			$dataVars->updateData->message
		);
	}

	protected static function createClient(): KernelBrowser {
		$client = parent::createClient();
		// Don't drop database and other services after redirect
		if ( $client instanceof KernelBrowser ) {
			$client->disableReboot();
			return $client;
		}
		throw new \LogicException( 'Unexpected client: ' . get_class( $client ) );
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

		return ( new StoredDonations( $factory ) )->newStoredIncompleteAnonymousPayPalDonation();
	}

	private function newPrivateDonorData(): array {
		return [
			'addressType' => AddressType::LEGACY_PERSON,
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
			'addressType' => AddressType::LEGACY_COMPANY,
			'city' => 'Mönchengladbach',
			'companyName' => 'Wikimedia Deutschland Money Makers GmbH',
			'country' => 'DE',
			'email' => 'test@test.de',
			'postcode' => '12345',
			'street' => 'Teststraße 123',

		];
	}

	private function newValidSuccessRedirectUrl( Donation $donation, FunFunFactory $ffFactory ): string {
		return $ffFactory->getUrlGenerator()->generateAbsoluteUrl(
			Routes::SHOW_DONATION_CONFIRMATION,
			[
				'id' => $donation->getId(),
				'accessToken' => self::CORRECT_UPDATE_TOKEN
			]
		);
	}
}
