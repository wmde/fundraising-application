<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use WMDE\Fundraising\DonationContext\DonationAcceptedEventHandler;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\DonationAcceptedController
 */
class DonationAcceptedRouteTest extends WebRouteTestCase {

	private const WRONG_UPDATE_TOKEN = 'Wrong update token';
	private const CORRECT_UPDATE_TOKEN = 'Correct update token';

	public function testGivenInvalidUpdateToken_errorIsReturned(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$existingDonationId = $this->storeDonation( $factory );

		$client->request(
			'GET',
			'/donation-accepted',
			[
				'update_token' => self::WRONG_UPDATE_TOKEN,
				'donation_id' => (string)$existingDonationId,
			]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR', 'message' => DonationAcceptedEventHandler::AUTHORIZATION_FAILED ],
			$client->getResponse()
		);
	}

	private function storeDonation( FunFunFactory $factory ): int {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator( self::CORRECT_UPDATE_TOKEN ) );

		$donation = ( new StoredDonations( $factory ) )->newStoredDirectDebitDonation();

		return $donation->getId();
	}

	public function testGivenKnownIdAndValidUpdateToken_successIsReturned(): void {
		$client = $this->createClient();
		$factory = $this->getFactory();
		$existingDonationId = $this->storeDonation( $factory );

		$client->request(
			'GET',
			'/donation-accepted',
			[
				'update_token' => self::CORRECT_UPDATE_TOKEN,
				'donation_id' => (string)$existingDonationId,
			]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

}
