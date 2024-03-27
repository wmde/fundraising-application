<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\APIRoutes;

use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation as DoctrineDonation;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDoctrineDonation;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\API\Donation\UpdateDonorController
 */
class UpdateDonorControllerTest extends WebRouteTestCase {

	use RebuildDatabaseSchemaTrait;
	use GetApplicationVarsTrait;

	private const PATH = 'api/v1/donation/update';
	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	private const INVALID_UPDATE_TOKEN = '2ba905fe68e61f3a681d8faf689bfeeb8c942b5b';

	public function testWhenCorrectPrivatePersonDataIsPosted_addressIsChanged(): void {
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

		$expectedDonorData = $this->newPrivateDonorData();

		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertJsonResponse( $expectedDonorData, $response );
	}

	public function testWhenCorrectCompanyDataIsPosted_addressIsChanged(): void {
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

		$expectedDonorData = $this->newCompanyDonorData();

		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertJsonResponse( $expectedDonorData, $response );
	}

	public function testGivenRequestWithoutParameters_resultIsNotFound(): void {
		$client = $this->createClient();

		$client->jsonRequest(
			Request::METHOD_PUT,
			self::PATH,
			[]
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_NOT_FOUND, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'No route found for "PUT http://localhost/api/v1/donation/update"'
		], $response );
	}

	public function testWhenInvalidUpdateTokenIsSupplied_requestIsDenied(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$donation = $this->newStoredDonation( $factory );

		$donationId = $donation->getId();
		$this->performRequest(
			$client,
			$this->newPrivateDonorData(),
			$donationId,
			self::CORRECT_UPDATE_TOKEN,
			self::INVALID_UPDATE_TOKEN
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'update_donor_failed',
			'errors' => [ 'donor_change_failure_access_denied' ]
		], $response );
	}

	public function testWhenDonationIsExported_requestIsDenied(): void {
		$client = $this->createClient();
		$donation = $this->newExportedDebitDoctrineDonation();

		$donationId = $donation->getId() ?? 0;
		$this->performRequest(
			$client,
			$this->newPrivateDonorData(),
			$donationId,
			self::CORRECT_UPDATE_TOKEN,
			self::CORRECT_UPDATE_TOKEN
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'update_donor_failed',
			'errors' => [ 'donor_change_failure_exported' ]
		], $response );
	}

	public function testWhenDonationDataIsInvalid_requestIsDenied(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$donation = $this->newStoredDonation( $factory );
		$donorData = $this->newPrivateDonorData();
		$donorData['email'] = 'this_is_not_a_valid_email_address.de';

		$this->performRequest(
			$client,
			$donorData,
			$donation->getId(),
			self::CORRECT_UPDATE_TOKEN,
			self::CORRECT_UPDATE_TOKEN
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'update_donor_failed',
			'errors' => [ 'donor_change_failure_validation_error' ]
		], $response );
	}

	/**
	 * @param Client $client
	 * @param array<string, bool|string> $data
	 * @param int $donationId
	 * @param string $accessToken
	 * @param string $updateToken
	 */
	private function performRequest( Client $client, array $data, int $donationId, string $accessToken, string $updateToken ): Crawler {
		return $client->jsonRequest(
			Request::METHOD_PUT,
			self::PATH . '/' . $accessToken,
			array_merge( [
				'donationId' => $donationId,
				'updateToken' => $updateToken
			], $data )
		);
	}

	private function newStoredDonation( FunFunFactory $factory ): Donation {
		return ( new StoredDonations( $factory ) )->newStoredIncompleteAnonymousPayPalDonation( self::CORRECT_UPDATE_TOKEN );
	}

	/**
	 * @return array<string, bool|string>
	 */
	private function newPrivateDonorData(): array {
		return [
			'addressType' => AddressType::PERSON,
			'mailingList' => true,
			'salutation' => 'Herr',
			'title' => '',
			'firstName' => 'Hans',
			'lastName' => 'Wurst',
			'fullName' => 'Hans Wurst',
			'street' => 'Teststraße 123',
			'postcode' => '12345',
			'city' => 'Mönchengladbach',
			'country' => 'DE',
			'email' => 'test@test.de',
		];
	}

	/**
	 * @return array<string, bool|string>
	 */
	private function newCompanyDonorData(): array {
		return [
			'addressType' => AddressType::LEGACY_COMPANY,
			'mailingList' => false,
			'companyName' => 'Wikimedia Deutschland Money Makers GmbH',
			'fullName' => 'Wikimedia Deutschland Money Makers GmbH',
			'street' => 'Teststraße 123',
			'postcode' => '12345',
			'city' => 'Mönchengladbach',
			'country' => 'DE',
			'email' => 'test@test.de',
		];
	}

	private function newExportedDebitDoctrineDonation(): DoctrineDonation {
		$factory = $this->getFactory();
		$donation = ValidDoctrineDonation::newExportedirectDebitDoctrineDonation();
		$donationId = $donation->getId() ?? 0;
		$accessToken = new AuthenticationToken(
			$donationId,
			AuthenticationBoundedContext::Donation,
			self::CORRECT_UPDATE_TOKEN,
			self::CORRECT_UPDATE_TOKEN
		);

		$entityManager = $factory->getEntityManager();
		$entityManager->persist( $accessToken );
		$entityManager->persist( $donation );
		$entityManager->flush();
		return $donation;
	}
}
