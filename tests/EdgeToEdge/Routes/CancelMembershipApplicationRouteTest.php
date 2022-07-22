<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredMemberships;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Membership\CancelMembershipApplicationController
 */
class CancelMembershipApplicationRouteTest extends WebRouteTestCase {

	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function testGivenValidUpdateToken_confirmationPageIsShown(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$applicationId = $this->storeApplication();

			$client->request(
				'GET',
				'cancel-membership-application',
				[
					'id' => (string)$applicationId,
					'updateToken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertStringContainsString( 'membership-cancellation-text', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenInvalidUpdateToken_resultIsError(): void {
		$this->createEnvironment( function ( Client $client ): void {
			$applicationId = $this->storeApplication();

			$client->request(
				'GET',
				'cancel-membership-application',
				[
					'id' => (string)$applicationId,
					'updateToken' => 'Not the correct update token',
				]
			);

			$this->assertStringContainsString( 'membership-cancellation-failed-text', $client->getResponse()->getContent() );
		} );
	}

	private function storeApplication(): int {
		$application = ( new StoredMemberships( $this->getFactory() ) )->storeValidMembershipApplication( self::CORRECT_UPDATE_TOKEN );
		return $application->getId();
	}

}
