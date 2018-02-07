<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Doctrine\ORM\ORMException;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\RouteHandlers\ShowDonationConfirmationHandler;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineApplicationRepository;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FixedMembershipTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
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

	public function testWhenDonationTimestampCookieIsSet_itIsNotOverwritten(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$membershipApplication = $this->newStoredMembershipApplication( $factory );

			$client->getCookieJar()->set(
				new Cookie( ShowDonationConfirmationHandler::SUBMISSION_COOKIE_NAME, 'some value' )
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
				$client->getCookieJar()->get( ShowDonationConfirmationHandler::SUBMISSION_COOKIE_NAME )->getValue()
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

			$this->assertAccessIsDenied( $client );
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

			$this->assertAccessIsDenied( $client );
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

			$this->assertAccessIsDenied( $client );
		} );
	}

	public function testOnDatabaseError_errorPageIsShown(): void {
		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$client = $this->createClient( [], function ( FunFunFactory $factory ) use ( $membershipApplication ): void {

			$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator(
				self::CORRECT_ACCESS_TOKEN
			) );

			$applicationRepository = $this->getMockBuilder( DoctrineApplicationRepository::class )
				->setConstructorArgs( [$factory->getEntityManager()] )
				->setMethods( ['getDoctrineApplicationById'] )
				->getMock();

			$applicationRepository->method( 'getDoctrineApplicationById' )
				->willThrowException( new ORMException() );

			$applicationRepository->storeApplication( $membershipApplication );

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

	private function assertAccessIsDenied( Client $client ): void {
		$this->assertContains( 'access_denied_membership_confirmation', $client->getResponse()->getContent() );
		$this->assertTrue( $client->getResponse()->isForbidden() );
	}

}
