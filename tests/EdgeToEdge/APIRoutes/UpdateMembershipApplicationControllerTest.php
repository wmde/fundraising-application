<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\APIRoutes;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidPayments;
use WMDE\Fundraising\Frontend\App\Controllers\API\Membership\UpdateMembershipApplicationController;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\RebuildDatabaseSchemaTrait;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;

#[CoversClass( UpdateMembershipApplicationController::class )]
class UpdateMembershipApplicationControllerTest extends WebRouteTestCase {

	use RebuildDatabaseSchemaTrait;
	use GetApplicationVarsTrait;

	private const PATH = 'api/v1/membership/update';
	private const CORRECT_ACCESS_TOKEN = 'a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1a1';
	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	private const INVALID_UPDATE_TOKEN = '2ba905fe68e61f3a681d8faf689bfeeb8c942b5b';

	public function testWhenCorrectPrivatePersonDataIsPosted_membershipIsChanged(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$application = $this->newStoredMembershipApplication( $factory );

		$this->performRequest(
			$client,
			$this->newPrivateApplicantData(),
			$application->getId(),
			self::CORRECT_ACCESS_TOKEN,
			self::CORRECT_UPDATE_TOKEN
		);
		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );

		$updatedApplication = $factory->getMembershipApplicationRepository()->getMembershipApplicationById( $application->getId() );

		$this->assertNotNull( $updatedApplication );
		$this->assertNotEquals( $application, $updatedApplication );
		$this->assertSame( 'Frau', $updatedApplication->getApplicant()->getName()->salutation );
		$this->assertSame( 'Dr', $updatedApplication->getApplicant()->getName()->title );
		$this->assertSame( 'Dr Onion Tomato', $updatedApplication->getApplicant()->getName()->getFullName() );
		$this->assertSame( 'Onion Straße 1', $updatedApplication->getApplicant()->getPhysicalAddress()->streetAddress );
		$this->assertSame( '12345', $updatedApplication->getApplicant()->getPhysicalAddress()->postalCode );
		$this->assertSame( 'Onion Town', $updatedApplication->getApplicant()->getPhysicalAddress()->city );
		$this->assertSame( 'DE', $updatedApplication->getApplicant()->getPhysicalAddress()->countryCode );
		$this->assertSame( 'onion@tomato.com', $updatedApplication->getApplicant()->getEmailAddress()->getFullAddress() );
		$this->assertTrue( $updatedApplication->getApplicant()->isPrivatePerson() );
	}

	public function testGivenRequestWithoutParameters_resultIsNotFound(): void {
		$client = $this->createClient();

		$client->jsonRequest(
			Request::METHOD_PUT,
			self::PATH . '/' . self::CORRECT_UPDATE_TOKEN,
			[]
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'update_membership_empty_request_body',
			'errors' => []
		], $response );
	}

	public function testWhenInvalidUpdateTokenIsSupplied_requestIsDenied(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$application = $this->newStoredMembershipApplication( $factory );

		$membershipId = $application->getId();
		$this->performRequest(
			$client,
			$this->newPrivateApplicantData(),
			$membershipId,
			self::CORRECT_ACCESS_TOKEN,
			self::INVALID_UPDATE_TOKEN
		);

		$response = $client->getResponse();

		$this->assertSame( Response::HTTP_BAD_REQUEST, $response->getStatusCode() );
		$this->assertJsonResponse( [
			'ERR' => 'update_membership_failed',
			'errors' => [ 'membership_application_update_failure_access_denied' ]
		], $response );
	}

	/**
	 * @param Client<Request, Response> $client
	 * @param array<string, bool|string|int> $data
	 * @param int $membershipId
	 * @param string $accessToken
	 * @param string $updateToken
	 *
	 * @return Crawler
	 */
	private function performRequest( Client $client, array $data, int $membershipId, string $accessToken, string $updateToken ): Crawler {
		return $client->jsonRequest(
			Request::METHOD_PUT,
			self::PATH . '/' . $accessToken,
			array_merge( [
				'membershipId' => $membershipId,
				'updateToken' => $updateToken
			], $data )
		);
	}

	private function newStoredMembershipApplication( FunFunFactory $factory ): MembershipApplication {
		$application = ValidMembershipApplication::newApplication();
		$factory->getPaymentRepository()->storePayment( ValidPayments::newDirectDebitPayment() );
		$factory->getMembershipApplicationRepository()->storeApplication( $application );

		$entityManager = $factory->getEntityManager();
		$entityManager->persist( new AuthenticationToken(
			$application->getId(),
			AuthenticationBoundedContext::Membership,
			self::CORRECT_ACCESS_TOKEN,
			self::CORRECT_UPDATE_TOKEN,
			null
		) );
		$entityManager->flush();

		return $application;
	}

	/**
	 * @return array<string, string>
	 */
	private function newPrivateApplicantData(): array {
		return [
			'addressType' => 'person',
			'salutation' => 'Frau',
			'title' => 'Dr',
			'firstName' => 'Onion',
			'lastName' => 'Tomato',
			'companyName' => '',
			'street' => 'Onion Straße 1',
			'postcode' => '12345',
			'city' => 'Onion Town',
			'country' => 'DE',
			'email' => 'onion@tomato.com',
			'paymentType' => 'BEA',
		];
	}
}
