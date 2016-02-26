<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ConfirmSubscription;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationRequest;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\CancelDonation\CancelDonationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenIdOfUnknownDonation_cancellationIsNotSuccessful() {
		$useCase = TestEnvironment::newInstance()->getFactory()->newCancelDonationUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337, 'token', 'updateToken' ) );

		$this->assertFalse( $response->cancellationWasSuccessful() );
	}

	public function testResponseContainsDonationId() {
		$useCase = TestEnvironment::newInstance()->getFactory()->newCancelDonationUseCase();

		$response = $useCase->cancelDonation( new CancelDonationRequest( 1337, 'token', 'updateToken' ) );

		$this->assertEquals( 1337, $response->getDonationId() );
	}

	public function testGivenIdOfCancellableDonation_cancellationIsSuccessful() {
		$factory = TestEnvironment::newInstance()->getFactory();

		$donation = ValidDonation::newDonation();
		$donation->setStatus( Donation::STATUS_NEW );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );

		$factory->getDonationRepository()->storeDonation( $donation );

		$useCase = $factory->newCancelDonationUseCase();
		$response = $useCase->cancelDonation( new CancelDonationRequest( $donation->getId(), 'token', 'updateToken' ) );

		$this->assertTrue( $response->cancellationWasSuccessful() );
	}

}
