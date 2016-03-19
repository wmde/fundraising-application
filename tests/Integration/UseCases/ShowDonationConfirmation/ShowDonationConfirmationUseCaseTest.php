<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationRepositoryFake;
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
	const WRONG_ACCESS_TOKEN = 'I am a potato';
	const CORRECT_ACCESS_TOKEN = 'Kindly allow me access';

	public function testWhenAuthorizerSaysNoCanHaz_accessIsNotPermitted() {
		$donation = new Donation();

		$useCase = new ShowDonationConfirmationUseCase(
			new FailingDonationAuthorizer(),
			new DonationRepositoryFake( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID,
			self::WRONG_ACCESS_TOKEN
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testWhenAuthorizerSaysSureThingBro_accessIsPermitted() {
		$donation = new Donation();

		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			new DonationRepositoryFake( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID,
			self::CORRECT_ACCESS_TOKEN
		) );

		$this->assertTrue( $response->accessIsPermitted() );
	}

	public function testWhenDonationDoesNotExist_accessIsNotPermitted() {
		self::markTestIncomplete( 'TODO' );

		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			new DonationRepositoryFake()
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID,
			self::WRONG_ACCESS_TOKEN
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

}
