<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use WMDE\Fundraising\Frontend\App\RouteHandlers\ShowDonationConfirmationHandler;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ShowMembershipConfirmationRouteTest extends WebRouteTestCase {

	const CORRECT_ACCESS_TOKEN = 'justSomeToken';

	private function newStoredMembershipApplication( FunFunFactory $factory ): Application {
		$factory->setTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_ACCESS_TOKEN
		) );

		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$factory->getMembershipApplicationRepository()->storeApplication( $membershipApplication );

		return $membershipApplication;
	}

	public function testWhenDonationTimestampCookieIsSet_itIsNotOverwritten() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$donation = $this->newStoredMembershipApplication( $factory );

			$client->getCookieJar()->set(
				new Cookie( ShowDonationConfirmationHandler::SUBMISSION_COOKIE_NAME, 'some value' )
			);
			$client->request(
				'GET',
				'show-membership-confirmation',
				[
					'id' => $donation->getId(),
					'accessToken' => self::CORRECT_ACCESS_TOKEN
				]
			);

			$this->assertSame(
				'some value',
				$client->getCookieJar()->get( ShowDonationConfirmationHandler::SUBMISSION_COOKIE_NAME )->getValue()
			);
		} );
	}

}
