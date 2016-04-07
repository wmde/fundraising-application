<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
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

	public function testWhenAuthorizerSaysNoCanHaz_accessIsNotPermitted() {
		$donation = new Donation();

		$useCase = new ShowDonationConfirmationUseCase(
			new FailingDonationAuthorizer(),
			new FakeDonationRepository( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testWhenAuthorizerSaysSureThingBro_accessIsPermitted() {
		$donation = new Donation();

		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			new FakeDonationRepository( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertTrue( $response->accessIsPermitted() );
	}

	public function testWhenDonationDoesNotExist_accessIsNotPermitted() {
		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			new FakeDonationRepository()
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testWhenDonationExistsAndAccessIsAllowed_donationIsReturned() {
		$donation = new Donation();
		$donation->setAmount( new Euro( 4200 ) );

		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			new FakeDonationRepository( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertEquals( $donation, $response->getDonation() );
	}

}
