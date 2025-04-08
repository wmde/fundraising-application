<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipConfirmationController;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\AuthenticationToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SuccessfulMembershipAuthorizer;
use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidPayments;
use WMDE\Fundraising\MembershipContext\Tests\TestDoubles\FakeMembershipRepository;

#[CoversClass( ShowMembershipConfirmationController::class )]
class ShowMembershipConfirmationRouteTest extends WebRouteTestCase {

	private const PATH = '/show-membership-confirmation';
	private const CORRECT_ACCESS_TOKEN = 'justSomeToken';
	private const WRONG_ACCESS_TOKEN = 'foobar';
	private const UNKNOWN_APPLICATION_ID = 9998;

	private function newStoredMembershipApplication( FunFunFactory $factory ): MembershipApplication {
		$factory->getPaymentRepository()->storePayment( ValidPayments::newDirectDebitPayment() );

		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$factory->getMembershipApplicationRepository()->storeApplication( $membershipApplication );
		$factory->getTokenRepository()->storeToken(
			new AuthenticationToken(
				$membershipApplication->getId(),
				AuthenticationBoundedContext::Membership,
				self::CORRECT_ACCESS_TOKEN,
				self::CORRECT_ACCESS_TOKEN
			)
		);

		return $membershipApplication;
	}

	public function testCallWithWrongAccessToken_deniedPageIsShown(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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

	public function testCallOnUnknownApplicationId_deniedPageIsShown(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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
		$this->modifyEnvironment( static function ( FunFunFactory $factory ) use ( $membershipApplication ): void {
			$factory->setMembershipApplicationAuthorizationChecker( new SuccessfulMembershipAuthorizer() );

			$applicationRepository = new FakeMembershipRepository( $membershipApplication );
			$applicationRepository->throwOnRead();
			$factory->setMembershipApplicationRepository( $applicationRepository );
		} );
		$client = $this->createClient();

		$client->request(
			Request::METHOD_GET,
			self::PATH,
			[
				'id' => $membershipApplication->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		$this->assertStringContainsString( 'Internal Error: A database error occurred', $client->getResponse()->getContent() ?: '' );
		$this->assertTrue( $client->getResponse()->isServerError() );
	}

	private function assertAccessIsDenied( string $expectedMessage, Client $client ): void {
		$this->assertStringContainsString( $expectedMessage, $client->getResponse()->getContent() ?: '' );
		$this->assertTrue( $client->getResponse()->isForbidden() );
	}

}
