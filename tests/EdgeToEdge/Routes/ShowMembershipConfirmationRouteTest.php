<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\RouteHandlers\ShowDonationConfirmationHandler;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FixedMembershipTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipConfirmationRouteTest extends WebRouteTestCase {

	private const PATH = '/show-membership-confirmation';
	private const CORRECT_ACCESS_TOKEN = 'justSomeToken';
	private const WRONG_ACCESS_TOKEN = 'foobar';

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

			$this->assertContains( 'access_denied_membership_confirmation', $client->getResponse()->getContent() );
			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
		} );
	}

	public function testCallOnAnonymizedRecord_deniedPageIsShown(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setMembershipTokenGenerator( new FixedMembershipTokenGenerator(
				self::CORRECT_ACCESS_TOKEN
			) );

			// @todo Get anonymized membership application from (context's) test data
			$doctrineApplication = new DoctrineApplication();
			$doctrineApplication->setBackup( new DateTime( '-1 day' ) );

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

			$this->assertContains( 'access_denied_membership_confirmation', $client->getResponse()->getContent() );
			$this->assertSame( 403, $client->getResponse()->getStatusCode() );
		} );
	}

}
