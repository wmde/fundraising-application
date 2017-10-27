<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\MembershipContext\Tests\Data\ValidMembershipApplication;

/**
 * @covers \WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase {

	public function testIdIsNullWhenNotAssigned(): void {
		$this->assertNull( ValidMembershipApplication::newDomainEntity()->getId() );
	}

	public function testCanAssignIdToNewDonation(): void {
		$donation = ValidMembershipApplication::newDomainEntity();

		$donation->assignId( 42 );
		$this->assertSame( 42, $donation->getId() );
	}

	public function testCannotAssignIdToDonationWithIdentity(): void {
		$donation = ValidMembershipApplication::newDomainEntity();
		$donation->assignId( 42 );

		$this->expectException( RuntimeException::class );
		$donation->assignId( 43 );
	}

	public function testNewApplicationHasExpectedDefaults(): void {
		$application = ValidMembershipApplication::newDomainEntity();

		$this->assertNull( $application->getId() );
		$this->assertFalse( $application->isCancelled() );
		$this->assertFalse( $application->needsModeration() );
	}

	public function testCancellationResultsInCancelledApplication(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->cancel();

		$this->assertTrue( $application->isCancelled() );
	}

	public function testMarkForModerationResultsInApplicationThatNeedsModeration(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$application->markForModeration();

		$this->assertTrue( $application->needsModeration() );
	}

	public function testDonationReceiptIsSetFromConstructor(): void {
		$application = ValidMembershipApplication::newDomainEntity();
		$this->assertTrue( $application->getDonationReceipt() );
	}

}