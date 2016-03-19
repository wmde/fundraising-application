<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation\ShowDonationConfirmationRequest;
use WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation\ShowDonationConfirmationUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation\ShowDonationConfirmationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCaseTest extends \PHPUnit_Framework_TestCase {

	const CORRECT_DONATION_ID = 1;
	const WRONG_ACCESS_TOKEN = 'I am a potato';

	public function testWhenDonationDoesNotExist_accessIsNotPermitted() {
		$useCase = new ShowDonationConfirmationUseCase();

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID,
			self::WRONG_ACCESS_TOKEN
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testGivenMismatchingAccessToken_accessIsNotPermitted() {
		// TODO: insert donation with different token

		$useCase = new ShowDonationConfirmationUseCase();

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID,
			self::WRONG_ACCESS_TOKEN
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

}
