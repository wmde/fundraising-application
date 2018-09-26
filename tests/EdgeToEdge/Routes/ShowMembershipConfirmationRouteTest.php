<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\Controllers\ApplyForMembershipController;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FakeApplicationRepository;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FixedMembershipTokenGenerator;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\SucceedingAuthorizer;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 */
class ShowMembershipConfirmationRouteTest extends WebRouteTestCase {

	private const PATH = '/show-membership-confirmation';
	private const CORRECT_ACCESS_TOKEN = 'justSomeToken';
	private const WRONG_ACCESS_TOKEN = 'foobar';
	private const UNKNOWN_APPLICATION_ID = 9998;

	private function newStoredMembershipApplication( FunFunFactory $factory ): Application {
		$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$factory->getMembershipApplicationRepository()->storeApplication( $membershipApplication );

		return $membershipApplication;
	}

	public function testWhenMembershipApplicationTimestampCookiePreexists_itIsNotOverwritten(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$membershipApplication = $this->newStoredMembershipApplication( $factory );

			$client->getCookieJar()->set(
				new Cookie( ApplyForMembershipController::SUBMISSION_COOKIE_NAME, 'some value' )
			);
			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'id' => $membershipApplication->getId(),
					'accessToken' => self::CORRECT_ACCESS_TOKEN
				]
			);

			$this->assertSame(
				'some value',
				$client->getCookieJar()->get( ApplyForMembershipController::SUBMISSION_COOKIE_NAME )->getValue()
			);
		} );
	}

	public function testCallWithWrongAccessToken_deniedPageIsShown(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$membershipApplication = $this->newStoredMembershipApplication( $factory );

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'id' => $membershipApplication->getId(),
					'accessToken' => self::WRONG_ACCESS_TOKEN
				]
			);

			$this->assertAccessIsDenied( 'access_denied_membership_confirmation', $client );
		} );
	}

	public function testCallOnAnonymizedRecord_deniedPageIsShown(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator(
				self::CORRECT_ACCESS_TOKEN
			) );

			$doctrineApplication = ValidMembershipApplication::newAnonymizedDoctrineEntity();

			$factory->getEntityManager()->persist( $doctrineApplication );
			$factory->getEntityManager()->flush();

			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'id' => $doctrineApplication->getId(),
					'accessToken' => self::CORRECT_ACCESS_TOKEN
				]
			);

			$this->assertAccessIsDenied( 'access_denied_membership_confirmation_anonymized', $client );
		} );
	}

	public function testCallOnUnknownApplicationId_deniedPageIsShown(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$client->request(
				Request::METHOD_GET,
				self::PATH,
				[
					'id' => self::UNKNOWN_APPLICATION_ID,
					'accessToken' => self::CORRECT_ACCESS_TOKEN
				]
			);

			$this->assertAccessIsDenied( 'access_denied_membership_confirmation', $client );
		} );
	}

	public function testOnDatabaseError_errorPageIsShown(): void {
		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$client = $this->createClient( [], function ( FunFunFactory $factory ) use ( $membershipApplication ): void {
			$factory->setMembershipApplicationAuthorizerClass( SucceedingAuthorizer::class );

			$applicationRepository = new FakeApplicationRepository( $membershipApplication );
			$applicationRepository->throwOnRead();
			$factory->setMembershipApplicationRepository( $applicationRepository );
		}, self::DISABLE_DEBUG );

		$client->request(
			Request::METHOD_GET,
			self::PATH,
			[
				'id' => $membershipApplication->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		$this->assertContains( 'Internal Error: A database error occurred', $client->getResponse()->getContent() );
		$this->assertTrue( $client->getResponse()->isServerError() );
	}

	private function assertAccessIsDenied( string $expectedMessage, Client $client ): void {
		$this->assertContains( $expectedMessage, $client->getResponse()->getContent() );
		$this->assertTrue( $client->getResponse()->isForbidden() );
	}

}
